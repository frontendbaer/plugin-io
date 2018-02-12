<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use Plenty\Modules\Accounting\Vat\Models\VatRate;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Extensions\Filters\NumberFormatFilter;
use Plenty\Modules\Frontend\Services\VatService;

/**
 * Class BasketService
 * @package IO\Services
 */
class BasketService
{
    use RuntimeTracker;

    /**
     * @var BasketItemRepositoryContract
     */
    private $basketItemRepository;

    /**
     * @var Checkout
     */
    private $checkout;

    private $template = '';
    /**
     * @var VatService
     */
    private $vatService;

    /**
     * @var SessionStorageService
     */
    private $sessionStorage;

    private $basketItems;

    /**
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     * @param Checkout $checkout
     * @param VatService $vatService
     */
    public function __construct(BasketItemRepositoryContract $basketItemRepository, Checkout $checkout, VatService $vatService, SessionStorageService $sessionStorage)
    {
        $this->start("constructor");
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout             = $checkout;
        $this->vatService           = $vatService;
        $this->sessionStorage       = $sessionStorage;
        $this->track("constructor");
    }

    public function setTemplate(string $template)
    {
        $this->start("setTemplate");
        $this->template = $template;
        $this->track("setTemplate");
    }

    public function getBasketForTemplate(): array
    {
        $this->start("getBasketForTemplate");
        $basket = $this->getBasket()->toArray();

        $basket["itemQuantity"] = $this->getBasketQuantity();
        $basket["totalVats"] = $this->getTotalVats();


        if ($this->sessionStorage->getCustomer()->showNetPrice) {
            $basket["itemSum"]        = $basket["itemSumNet"];
            $basket["basketAmount"]   = $basket["basketAmountNet"];
            $basket["shippingAmount"] = $basket["shippingAmountNet"];
        }

        $this->track("getBasketForTemplate");
        return $basket;
    }

    /**
     * Return the basket as an array
     * @return Basket
     */
    public function getBasket(): Basket
    {
        $this->start("getBasket");
        $basket = pluginApp(BasketRepositoryContract::class)->load();
        $basket->currency = pluginApp(CheckoutService::class)->getCurrency();
        $this->track("getBasket");
        return $basket;
    }

    /**
     * @return array
     */
    public function getTotalVats(): array
    {
        $this->start("getTotalVats");
        $vats = $this->vatService->getCurrentTotalVats();
        $this->track( "getTotalVats");
        return $vats;
    }

    public function getBasketQuantity()
    {
        $this->start( "getBasketQuantity");
        $itemQuantity = 0;

        foreach ($this->getBasketItems() as $item) {
            if ($item["variationId"] > 0) {
                $itemQuantity += $item["quantity"];
            }
        }

        $this->track( "getBasketQuantity");
        return $itemQuantity;
    }

    /**
     * List the basket items
     * @return array
     */
    public function getBasketItems(): array
    {
        $this->start("getBasketItems");
        $result = array();

        $basketItems        = $this->getBasketItemsRaw();
        $basketItemData     = $this->getBasketItemData($basketItems);
        $showNetPrice       = $this->sessionStorage->getCustomer()->showNetPrice;

        foreach ($basketItems as $basketItem) {
            if ($showNetPrice) {
                $basketItem->price = round($basketItem->price * 100 / (100.0 + $basketItem->vat), 2);
            }

            array_push(
                $result,
                $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId])
            );
        }

        $this->track("getBasketItems");
        return $result;
    }

    public function getBasketItemsForTemplate(string $template = ''): array
    {
        $this->start("getBasketItemsForTemplate");
        if (!strlen($template)) {
            $template = $this->template;
        }

        $result = array();

        $basketItems    = $this->getBasketItemsRaw();
        $basketItemData = $this->getBasketItemData($basketItems, $template);

        foreach ($basketItems as $basketItem) {
            array_push(
                $result,
                $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId])
            );
        }

        $this->track("getBasketItemsForTemplate");
        return $result;
    }

    /**
     * Get a basket item
     * @param int $basketItemId
     * @return array
     */
    public function getBasketItem(int $basketItemId): array
    {
        $this->start("getBasketItem");
        $basketItem = $this->basketItemRepository->findOneById($basketItemId);
        if ($basketItem === null) {
            return array();
        }
        $basketItemData = $this->getBasketItemData($basketItem->toArray());
        $result = $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId]);
        $this->track("getBasketItem");
        return $result;
    }

    /**
     * Load the variation data for the basket item
     * @param BasketItem $basketItem
     * @param $variationData
     * @return array
     */
    private function addVariationData(BasketItem $basketItem, $variationData): array
    {
        $this->start("addVariationData");
        $arr              = $basketItem->toArray();
        $arr["variation"] = $variationData;
        $this->track("addVariationData");
        return $arr;
    }

    /**
     * Add an item to the basket or update the basket
     * @param array $data
     * @return array
     */
    public function addBasketItem(array $data): array
    {
        $this->start("addBasketItem");
        if (isset($data['basketItemOrderParams']) && is_array($data['basketItemOrderParams'])) {
            list($data['basketItemOrderParams'], $data['totalOrderParamsMarkup']) = $this->parseBasketItemOrderParams($data['basketItemOrderParams']);
        }

        $data['referrerId'] = $this->getBasket()->referrerId;
        $basketItem = $this->findExistingOneByData($data);

        try {
            if ($basketItem instanceof BasketItem) {
                $data['id']       = $basketItem->id;
                $data['quantity'] = (int)$data['quantity'] + $basketItem->quantity;
                $this->basketItemRepository->updateBasketItem($basketItem->id, $data);
            } else {
                $this->basketItemRepository->addBasketItem($data);
            }
        } catch (\Exception $e) {
            return ["code" => $e->getCode()];
        }

        $result = $this->getBasketItemsForTemplate();
        $this->track("addBasketItem");

        return $result;
    }

    /**
     * Parse basket item order params
     * @param array $basketOrderParams
     * @return array
     */
    private function parseBasketItemOrderParams(array $basketOrderParams): array
    {
        $this->start("parseBasketItemOrderParams");
        $properties = [];

        $totalOrderParamsMarkup = 0;
        foreach ($basketOrderParams as $key => $basketOrderParam) {

            if (strlen($basketOrderParam['property']['value']) > 0 && isset($basketOrderParam['property']['value'])) {

                $properties[$key]['propertyId'] = $basketOrderParam['property']['names']['propertyId'];
                $properties[$key]['type']       = $basketOrderParam['property']['valueType'];
                $properties[$key]['value']      = $basketOrderParam['property']['value'];
                $properties[$key]['name']       = $basketOrderParam['property']['names']['name'];

                if ($basketOrderParam['surcharge'] > 0) {
                    $totalOrderParamsMarkup += $basketOrderParam['surcharge'];
                } elseif ($basketOrderParam['property']['surcharge'] > 0) {
                    $totalOrderParamsMarkup += $basketOrderParam['property']['surcharge'];
                }

            }
        }

        $this->track("parseBasketItemOrderParams");
        return [$properties, $totalOrderParamsMarkup];
    }

    /**
     * Update a basket item
     * @param int $basketItemId
     * @param array $data
     * @return array
     */
    public function updateBasketItem(int $basketItemId, array $data): array
    {
        $this->start("updateBasketItem");
        $data['id'] = $basketItemId;
        try {
            $this->basketItemRepository->updateBasketItem($basketItemId, $data);
        } catch (\Exception $e) {
            return ["code" => $e->getCode()];
        }
        $result = $this->getBasketItemsForTemplate();
        $this->track("updateBasketItem");

        return $result;
    }

    /**
     * Delete an item from the basket
     * @param int $basketItemId
     * @return array
     */
    public function deleteBasketItem(int $basketItemId): array
    {
        $this->start( "deleteBasketItem" );
        $this->basketItemRepository->removeBasketItem($basketItemId);
        $result = $this->getBasketItemsForTemplate();
        $this->track( "deleteBasketItem" );

        return $result;
    }

    /**
     * Check whether the item is already in the basket
     * @param array $data
     * @return null|BasketItem
     */
    public function findExistingOneByData(array $data)
    {
        $this->start("findExistingOneByData");
        $result = $this->basketItemRepository->findExistingOneByData($data);
        $this->track("findExistingOneByData");

        return $result;
    }

    /**
     * Get the data of the basket items
     * @param BasketItem[] $basketItems
     * @param string $template
     * @return array
     */
    private function getBasketItemData($basketItems = array(), string $template = ''): array
    {
        $this->start("getBasketItemData");
        if (!strlen($template)) {
            $template = $this->template;
        }

        if (count($basketItems) <= 0) {
            return array();
        }
        $numberFormatFilter = pluginApp(NumberFormatFilter::class);
        $currency           = $this->getBasket()->currency;

        $basketItemVariationIds    = [];
        $basketVariationQuantities = [];
        $orderProperties           = [];

        foreach ($basketItems as $basketItem) {
            array_push($basketItemVariationIds, $basketItem->variationId);
            $basketVariationQuantities[$basketItem->variationId] = $basketItem->quantity;
            $orderProperties[$basketItem->variationId]           = $basketItem->basketItemOrderParams;
        }

        /** @var TwigLoaderPresets $loaderPresets */
        $loaderPresets = pluginApp(TwigLoaderPresets::class);
        $presets = $loaderPresets->getGlobals();
        $items = pluginApp(ItemLoaderService::class)
            ->loadForTemplate(
                $template,
                $presets['itemLoaderPresets']['basketItems'],
                [
                    'variationIds' => $basketItemVariationIds,
                    'basketVariationQuantities' => $basketVariationQuantities,
                    'items' => count($basketItemVariationIds), 'page' => 1
                ]);

        $result = array();
        foreach ($items['documents'] as $item) {
            $variationId                                     = $item['data']['variation']['id'];
            $result[$variationId]                            = $item;
            $result[$variationId]['data']['orderProperties'] = $orderProperties[$variationId];
        }

        $this->track("getBasketItemData");
        return $result;
    }

    public function resetBasket()
    {
        $this->start("resetBasket");
        $basketItems = $this->getBasketItemsRaw();
        foreach ($basketItems as $basketItem) {
            $this->basketItemRepository->removeBasketItem($basketItem->id);
        }
        $this->track("resetBasket");
    }

    /**
     * Set the billing address id
     * @param int $billingAddressId
     */
    public function setBillingAddressId(int $billingAddressId)
    {
        $this->start("setBillingAddressId");
        $this->checkout->setCustomerInvoiceAddressId($billingAddressId);
        $this->track("setBillingAddressId");
    }

    /**
     * Return the billing address id
     * @return int
     */
    public function getBillingAddressId()
    {
        $this->start("getBillingAddressId");
        $addressId = $this->checkout->getCustomerInvoiceAddressId();
        $this->track( "getBillingAddressId");
        return $addressId;
    }

    /**
     * Set the delivery address id
     * @param int $deliveryAddressId
     */
    public function setDeliveryAddressId(int $deliveryAddressId)
    {
        $this->start("setDeliveryAddressId");
        $this->checkout->setCustomerShippingAddressId($deliveryAddressId);
        $this->track("setDeliveryAddressId");
    }

    /**
     * Return the delivery address id
     * @return int
     */
    public function getDeliveryAddressId()
    {
        $this->start("getDeliveryAddressId");
        $addressId = $this->checkout->getCustomerShippingAddressId();
        $this->track("getDeliveryAddressId");

        return $addressId;
    }

    /**
     * Get the maximum vat value in basket.
     *
     * @return float
     */
    public function getMaxVatValue()
    {
        $this->start("getMaxVatValue");
        $maxVatValue = -1;

        foreach ($this->getBasketItemsRaw() as $item) {
            $maxVatValue = max($maxVatValue, $item->vat);
        }

        if ($maxVatValue == -1) {
            if (count($vatRates = $this->vatService->getVat()->vatRates)
                && isset($vatRates[0])) {
                $vatRate = $vatRates[0];
                if ($vatRate instanceof VatRate) {
                    $maxVatValue = $vatRate->vatRate;
                }
            }
        }

        $this->track("getMaxVatValue");
        return $maxVatValue;
    }

    /**
     * @return BasketItem[]
     */
    private function getBasketItemsRaw()
    {
        $this->start("getBasketItemsRaw");
        if (!is_array($this->basketItems)) {
            $this->basketItems = $this->basketItemRepository->all();
        }
        $this->track("getBasketItemsRaw");

        return $this->basketItems;
    }
}
