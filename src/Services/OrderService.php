<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\ContactWish\Contracts\ContactWishRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Property\Contracts\OrderPropertyRepositoryContract;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\ConfigRepository;
use IO\Constants\OrderPaymentStatus;
use IO\Models\LocalizedOrder;
use IO\Builder\Order\OrderBuilder;
use IO\Builder\Order\OrderType;
use IO\Builder\Order\OrderOptionSubType;
use IO\Builder\Order\AddressType;
use IO\Constants\SessionStorageKeys;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Authorization\Services\AuthHelper;


/**
 * Class OrderService
 * @package IO\Services
 */
class OrderService
{
    use RuntimeTracker;

	/**
	 * @var OrderRepositoryContract
	 */
	private $orderRepository;
	/**
	 * @var BasketService
	 */
	private $basketService;
    /**
     * @var SessionStorageService
     */
    private $sessionStorage;
    
    /**
     * @var FrontendPaymentMethodRepositoryContract
     */
    private $frontendPaymentMethodRepository;
    
    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     * @param BasketService $basketService
     * @param \IO\Services\SessionStorageService $sessionStorage
     */
	public function __construct(
		OrderRepositoryContract $orderRepository,
		BasketService $basketService,
        SessionStorageService $sessionStorage,
        FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
	)
	{
	    $this->start("constructor");
		$this->orderRepository = $orderRepository;
		$this->basketService   = $basketService;
        $this->sessionStorage  = $sessionStorage;
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
	    $this->track("constructor");
	}

    /**
     * Place an order
     * @return LocalizedOrder
     */
	public function placeOrder():LocalizedOrder
	{
	    $this->start("placeOrder");
	    /** @var CheckoutService $checkoutService */
        $checkoutService = pluginApp(CheckoutService::class);
        
        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);
        
        $basket = $this->basketService->getBasket();
        
        $couponCode = null;
        if(strlen($basket->couponCode))
        {
            $couponCode = $basket->couponCode;
        }
        
		$order = pluginApp(OrderBuilder::class)->prepare(OrderType::ORDER)
		                            ->fromBasket()
		                            ->withContactId($customerService->getContactId())
		                            ->withAddressId($checkoutService->getBillingAddressId(), AddressType::BILLING)
		                            ->withAddressId($checkoutService->getDeliveryAddressId(), AddressType::DELIVERY)
		                            ->withOrderProperty(OrderPropertyType::PAYMENT_METHOD, OrderOptionSubType::MAIN_VALUE, $checkoutService->getMethodOfPaymentId())
                                    ->withOrderProperty(OrderPropertyType::SHIPPING_PROFILE, OrderOptionSubType::MAIN_VALUE, $checkoutService->getShippingProfileId())
		                            ->done();
        
		$order = $this->orderRepository->createOrder($order, $couponCode);
		$this->saveOrderContactWish($order->id, $this->sessionStorage->getSessionValue(SessionStorageKeys::ORDER_CONTACT_WISH));
        
        if($customerService->getContactId() <= 0)
        {
            $this->sessionStorage->setSessionValue(SessionStorageKeys::LATEST_ORDER_ID, $order->id);
        }

        // reset basket after order was created
        $this->basketService->resetBasket();
        $customerService->resetGuestAddresses();


        $localizedOrder = LocalizedOrder::wrap( $order, "de" );
        $this->track("placeOrder");

        return $localizedOrder;
	}
	
	private function saveOrderContactWish($orderId, $text = '')
    {
        $this->start("saveOrderContactWish");
        if(!is_null($text) && strlen($text))
        {
            /**
             * @var ContactWishRepositoryContract $contactWishRepo
             */
            $contactWishRepo = pluginApp(ContactWishRepositoryContract::class);
            $contactWishRepo->createContactWish($orderId, nl2br($text));
            $this->sessionStorage->setSessionValue(SessionStorageKeys::ORDER_CONTACT_WISH, null);
        }
        $this->track("saveOrderContactWish");
    }

    /**
     * Execute the payment for a given order.
     * @param int $orderId      The order id to execute payment for
     * @param int $paymentId    The MoP-ID to execute
     * @return array            An array containing a type ("succes"|"error") and a value.
     */
	public function executePayment( int $orderId, int $paymentId ):array
    {
        $this->start("executePayment");
        $paymentRepository = pluginApp( PaymentMethodRepositoryContract::class );
        $result = $paymentRepository->executePayment( $paymentId, $orderId );
        $this->track("executePayment");

        return $result;
    }
    
    /**
     * Find an order by ID
     * @param int $orderId
     * @param bool $removeReturnItems
     * @param bool $wrap
     * @return LocalizedOrder|mixed|Order
     */
	public function findOrderById(int $orderId, $removeReturnItems = false, $wrap = true)
	{
	    $this->start("findOrderById");
        if($removeReturnItems)
        {
            $order = $this->removeReturnItemsFromOrder($this->orderRepository->findOrderById($orderId));
        }
        else
        {
            $order = $this->orderRepository->findOrderById($orderId);
        }
        
        if($wrap)
        {
            $localizedOrder = LocalizedOrder::wrap($order, 'de');
            $this->track("findOrderById");
            return $localizedOrder;
        }

        $this->track("findOrderById");

        return $order;
	}
	
	public function findOrderByAccessKey($orderId, $orderAccessKey)
    {
        $this->start("finderOrderbyAccessKey");
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $redirectToLogin = $templateConfigService->get('my_account.confirmation_link_login_redirect');
    
        $order = $this->orderRepository->findOrderByAccessKey($orderId, $orderAccessKey);
        
        if($redirectToLogin == 'true')
        {
            /**
             * @var CustomerService $customerService
             */
            $customerService = pluginApp(CustomerService::class);
    
            $orderContactId = 0;
            foreach ($order->relations as $relation)
            {
                if ($relation['referenceType'] == 'contact' && (int)$relation['referenceId'] > 0)
                {
                    $orderContactId = $relation['referenceId'];
                }
            }
    
            if ((int)$orderContactId > 0)
            {
                if ((int)$customerService->getContactId() <= 0)
                {
                    $this->track("finderOrderbyAccessKey");

                    return pluginApp(Response::class)->redirectTo('login?backlink=confirmation/' . $orderId . '/' . $orderAccessKey);
                }
                elseif ((int)$orderContactId !== (int)$customerService->getContactId())
                {
                    $this->track("finderOrderbyAccessKey");
                    return null;
                }
            }
        }

        $localizedOrder = LocalizedOrder::wrap($order, 'de');
        $this->track("finderOrderbyAccessKey");

        return $localizedOrder;
    }
    
    /**
     * Get a list of orders for a contact
     * @param int $contactId
     * @param int $page
     * @param int $items
     * @param array $filters
     * @param bool $wrapped
     * @return PaginatedResult
     */
    public function getOrdersForContact(int $contactId, int $page = 1, int $items = 50, array $filters = [], $wrapped = true)
    {
        $this->start("getOrdersForContact");
        if(!isset($filters['orderType']))
        {
            $filters['orderType'] = OrderType::ORDER;
        }
        
        $this->orderRepository->setFilters($filters);

        $orders = $this->orderRepository->allOrdersByContact(
            $contactId,
            $page,
            $items
        );

        if($wrapped)
        {
            $orders = LocalizedOrder::wrapPaginated( $orders, "de" );
    
            $o = $orders->getResult();
            foreach($orders->getResult() as $key => $order)
            {
                $order = $order->order;
                if($order->typeId == OrderType::ORDER)
                {
                    $o[$key]->isReturnable = $this->isOrderReturnable($order);
                }
            }
            $orders->setResult($o);
        }

        $this->track("getOrdersForContact");

        return $orders;
    }
    
    /**
     * Get the last order created by the current contact
     * @param int $contactId
     * @return LocalizedOrder|null
     */
    public function getLatestOrderForContact( int $contactId )
    {
        $this->start("getLatestOrderForContact");
        if($contactId > 0)
        {
            $order = $this->orderRepository->getLatestOrderByContactId( $contactId );
        }
        else
        {
            $order = $this->orderRepository->findOrderById($this->sessionStorage->getSessionValue(SessionStorageKeys::LATEST_ORDER_ID));
        }

        $result = null;
        if(!is_null($order))
        {
            $result = LocalizedOrder::wrap( $order, "de" );
        }

        $this->track("getLatestOrderForContact");

        return $result;
    }
    
    /**
     * Return order status text by status id
     * @param $statusId
     * @return string
     */
	public function getOrderStatusText($statusId)
    {
	    //OrderStatusTexts::$orderStatusTexts[(string)$statusId];
        return '';
    }
    
    public function getOrderPropertyByOrderId($orderId, $typeId)
    {
        $this->start("getOrderPropertyByOrderId");
        /**
         * @var OrderPropertyRepositoryContract $orderPropertyRepo
         */
        $orderPropertyRepo = pluginApp(OrderPropertyRepositoryContract::class);
        $property = $orderPropertyRepo->findByOrderId($orderId, $typeId);
        $this->track("getOrderPropertyByOrderId");

        return $property;
    }
    
    public function isReturnActive()
    {
        $this->start("isReturnActive");
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $returnsActive = $templateConfigService->get('my_account.order_return_active', 'true');

        $this->track("isReturnActive");

        return $returnsActive == 'true';
    }
    
    public function isOrderReturnable(Order $order)
    {
        $this->start("isOrderReturnable");
        $returnActive = $this->isReturnActive();
        
        if($returnActive)
        {
            /**
             * @var ConfigRepository $config
             */
            $config = pluginApp(ConfigRepository::class);
            $enabledRoutes = explode(', ',  $config->get('IO.routing.enabled_routes') );
            if ( !in_array('order-return', $enabledRoutes) && !in_array('all', $enabledRoutes) )
            {
                $this->track("isOrderReturnable");
                return false;
            }
            
            $orderWithoutReturnItems = $this->removeReturnItemsFromOrder($order);
            if(!count($orderWithoutReturnItems->orderItems))
            {
                $this->track("isOrderReturnable");
                return false;
            }
            
            $shippingDateSet = false;
            $createdDateUnix = 0;
    
            foreach($order->dates as $date)
            {
                if($date->typeId == 5 && strlen($date->date))
                {
                    $shippingDateSet = true;
                }
                elseif($date->typeId == 2 && strlen($date->date))
                {
                    $createdDateUnix = $date->date->timestamp;
                }
            }
    
            /**
             * @var TemplateConfigService $templateConfigService
             */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $returnTime = (int)$templateConfigService->get('my_account.order_return_days', 14);
    
            if( $shippingDateSet && ($createdDateUnix > 0 && $returnTime > 0) && (time() < ($createdDateUnix + ($returnTime * 24 * 60 * 60))) )
            {
                $this->track("isOrderReturnable");
                return true;
            }
        }

        $this->track("isOrderReturnable");
        return false;
    }
    
    public function createOrderReturn($orderId, $items = [], $returnNote = '')
    {
        $this->start("createOrderReturn");
        $order = $this->orderRepository->findOrderById($orderId);
        $order = $this->removeReturnItemsFromOrder($order);
        $order = $order->toArray();
        
        if($this->isReturnActive())
        {
            foreach($order['orderItems'] as $key => $orderItem)
            {
                if(array_key_exists($orderItem['itemVariationId'], $items) && (int)$items[$orderItem['itemVariationId']] > 0)
                {
                    $returnQuantity = (int)$items[$orderItem['itemVariationId']];
                    
                    if($returnQuantity > $order['orderItems'][$key]['quantity'])
                    {
                        $returnQuantity = $order['orderItems'][$key]['quantity'];
                    }
                    
                    $order['orderItems'][$key]['quantity'] = $returnQuantity;

                    $order['orderItems'][$key]['references'][] = [
                        'referenceOrderItemId' =>   $order['orderItems'][$key]['id'],
                        'referenceType' => 'parent'
                    ];
                    
                    unset($order['orderItems'][$key]['id']);
                    unset($order['orderItems'][$key]['orderId']);
                }
                else
                {
                    unset($order['orderItems'][$key]);
                }
            }
    
            /**
             * @var TemplateConfigService $templateConfigService
             */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $returnStatus = $templateConfigService->get('my_account.order_return_initial_status', '');
            if(!strlen($returnStatus) || (float)$returnStatus <= 0)
            {
                $returnStatus = 9.0;
            }
    
            $order['statusId'] = (float)$returnStatus;
            $order['typeId'] = OrderType::RETURNS;
    
            $order['orderReferences'][] = [
                'referenceOrderId' => $order['id'],
                'referenceType' => 'parent'
            ];
    
            unset($order['id']);
            unset($order['dates']);
    
            $createdReturn = $this->orderRepository->createOrder($order);

            if(!is_null($returnNote) && strlen($returnNote))
            {
                $this->saveOrderContactWish($createdReturn->id, $returnNote);
            }

            $this->track("createOrderReturn");

            return $createdReturn;
        }

        $this->track("createOrderReturn");
        return $order;
    }
    
    private function removeReturnItemsFromOrder($order)
    {
        $orderId = $order->id;

        $returnFilters = [
            'orderType' => OrderType::RETURNS,
            'referenceOrderId' => $orderId
        ];
        
        $allReturns = $this->getOrdersForContact(pluginApp(CustomerService::class)->getContactId(), 1, 50, $returnFilters, false)->getResult();
        
        $returnItems = [];
        $newOrderItems = [];
        
        if(count($allReturns))
        {
            foreach($allReturns as $returnKey => $return)
            {
                foreach($return['orderReferences'] as $reference)
                {
                    if($reference['referenceType'] == 'parent' && $reference['referenceOrderId'] == $orderId)
                    {
                        foreach($return['orderItems'] as $returnItem)
                        {
                            if(array_key_exists($returnItem['itemVariationId'], $returnItems))
                            {
                                $returnItems[$returnItem['itemVariationId']] += $returnItem['quantity'];
                            }
                            else
                            {
                                $returnItems[$returnItem['itemVariationId']] = $returnItem['quantity'];
                            }
                        }
                    }
                }
            }
            
            if(count($returnItems))
            {
                foreach($order->orderItems as $key => $orderItem)
                {
                    if(array_key_exists($orderItem['itemVariationId'], $returnItems))
                    {
                        $newQuantity = $orderItem['quantity'] - $returnItems[$orderItem['itemVariationId']];
                    }
                    else
                    {
                        $newQuantity = $orderItem['quantity'];
                    }
    
                    if($newQuantity > 0 && ($orderItem->typeId == 1 || $orderItem->typeId == 3 || $orderItem->typeId == 9))
                    {
                        $orderItem['quantity'] = $newQuantity;
                        $newOrderItems[] = $orderItem;
                    }
                    else
                    {
                        $orderItem->quantity = 0;
                    }
                }
                
                $order->orderItems = $newOrderItems;
            }
            else
            {
                foreach($order->orderItems as $key => $orderItem)
                {
                    if($orderItem->typeId == 1 || $orderItem->typeId == 3 || $orderItem->typeId == 9)
                    {
                        $newOrderItems[] = $orderItem;
                    }
                }
    
                $order->orderItems = $newOrderItems;
            }
        }
        
        return $order;
    }
    
    /**
     * List all payment methods available for switch in MyAccount
     *
     * @param int $currentPaymentMethodId
     * @param null $orderId
     */
    public function getPaymentMethodListForSwitch($currentPaymentMethodId = 0, $orderId = null)
    {
        $this->start("getPaymentMethodListForSwitch");
        $result = $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsListForSwitch($currentPaymentMethodId, $orderId, $this->sessionStorage->getLang());
        $this->track("getPaymentMethodListForSwitch");

        return $result;
    }
    
    /**
     * @param $paymentMethodId
     * @param int $orderId
     * @return bool
     */
	public function allowPaymentMethodSwitchFrom($paymentMethodId, $orderId = null)
	{
	    $this->start("allowPaymentMethodSwitchFrom");
		/** @var TemplateConfigService $config */
		$config = pluginApp(TemplateConfigService::class);
		if ($config->get('my_account.change_payment') == "false")
		{
            $this->track("allowPaymentMethodSwitchFrom");
            return false;
		}
		if($orderId != null)
		{
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $orderRepo = $this->orderRepository;
            
            $order = $authHelper->processUnguarded( function() use ($orderId, $orderRepo)
            {
                $this->track("allowPaymentMethodSwitchFrom");
                return $orderRepo->findOrderById($orderId);
            });
			
			if ($order->paymentStatus !== OrderPaymentStatus::UNPAID)
			{
				// order was paid
                $this->track("allowPaymentMethodSwitchFrom");
                return false;
			}
			
			$statusId = $order->statusId;
			$orderCreatedDate = $order->createdAt;
			
			if(!($statusId <= 3.4 || ($statusId == 5 && $orderCreatedDate->toDateString() == date('Y-m-d'))))
			{
                $this->track("allowPaymentMethodSwitchFrom");
                return false;
			}
		}
        $result = $this->frontendPaymentMethodRepository->getPaymentMethodSwitchFromById($paymentMethodId, $orderId);

        $this->track("allowPaymentMethodSwitchFrom");
        return $result;
	}
    
    
    /**
     * @param $orderId
     * @param $paymentMethodId
     * @return LocalizedOrder|null
     */
    public function switchPaymentMethodForOrder($orderId, $paymentMethodId)
    {
        if((int)$orderId > 0)
        {
            $currentPaymentMethodId = 0;
    
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $orderRepo = $this->orderRepository;
    
            $order = $authHelper->processUnguarded( function() use ($orderId, $orderRepo)
            {
                return $orderRepo->findOrderById($orderId);
            });
        
            $newOrderProperties = [];
            $orderProperties = $order->properties;
        
            if(count($orderProperties))
            {
                foreach($orderProperties as $key => $orderProperty)
                {
                    $newOrderProperties[$key] = $orderProperty;
                    if($orderProperty->typeId == OrderPropertyType::PAYMENT_METHOD)
                    {
                        $currentPaymentMethodId = (int)$orderProperty->value;
                        $newOrderProperties[$key]['value'] = (int)$paymentMethodId;
                    }
                }
            }
        
            if($paymentMethodId !== $currentPaymentMethodId)
            {
                if($this->frontendPaymentMethodRepository->getPaymentMethodSwitchableFromById($currentPaymentMethodId, $orderId) && $this->frontendPaymentMethodRepository->getPaymentMethodSwitchableToById($paymentMethodId))
                {
                    $order = $authHelper->processUnguarded( function() use ($orderId, $newOrderProperties, $orderRepo)
                    {
                        return $orderRepo->updateOrder(['properties' => $newOrderProperties], $orderId);
                    });
                    
                    if(!is_null($order))
                    {
                        return LocalizedOrder::wrap( $order, "de" );
                    }
                }
            }
        }
    
        return null;
    }
}
