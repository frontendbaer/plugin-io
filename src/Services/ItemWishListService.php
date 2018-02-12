<?php
/**
 * Created by IntelliJ IDEA.
 * User: ihussein
 * Date: 01.08.17
 */

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use IO\Helper\RuntimeTracker;
use IO\Services\CustomerService;
use IO\Repositories\ItemWishListRepository;
use IO\Repositories\ItemWishListGuestRepository;
use IO\Services\SessionStorageService;

/**
 * Class WishListService
 * @package IO\Services
 */
class ItemWishListService
{
    use RuntimeTracker;

    private $itemWishListRepo;
    
    public function __construct(SessionStorageService $sessionStorage)
    {
        $this->start("constructor");
        if($sessionStorage->getSessionValue(SessionStorageKeys::GUEST_WISHLIST_MIGRATION))
        {
            $this->migrateGuestItemWishList();
            $sessionStorage->setSessionValue(SessionStorageKeys::GUEST_WISHLIST_MIGRATION, false);
        }
        
        /**
         * @var CustomerService $customerService
         */
        $customerService = pluginApp(CustomerService::class);
        
        if((int)$customerService->getContactId() > 0)
        {
            $itemWishListRepo = pluginApp(ItemWishListRepository::class);
        }
        else
        {
            $itemWishListRepo = pluginApp(ItemWishListGuestRepository::class);
        }
        
        $this->itemWishListRepo = $itemWishListRepo;
        $this->track("constructor");

    }

    /**
     * @param int $variationId
     * @param int $quantity
     * @return mixed
     */
    public function addItemWishListEntry(int $variationId, int $quantity)
    {
        $this->start("addItemWishListEntry");
        $result = $this->itemWishListRepo->addItemWishListEntry($variationId, $quantity);
        $this->track("addItemWishListEntry");

        return $result;
    }

    /**
     * @param int $variationId
     * @return bool
     */
    public function isItemInWishList(int $variationId)
    {
        $this->start("isItemInWishList");
        $result = $this->itemWishListRepo->isItemInWishList($variationId);
        $this->track("isItemInWishList");

        return $result;
    }

    /**
     * @return array
     */
    public function getItemWishList()
    {
        $this->start("getItemWishList");
        $result = $this->itemWishListRepo->getItemWishList();
        $this->track("getItemWishList");
        return $result;
    }

    /**
     * @return int
     */
    public function getCountedItemWishList()
    {
        $this->start("getCountedItemWishList");
        $result = $this->itemWishListRepo->getCountedItemWishList();
        $this->track("getCountedItemWishList");

        return $result;
    }

    /**
     * @param int $variationId
     * @return bool
     */
    public function removeItemWishListEntry(int $variationId)
    {
        $this->start("removeItemWishListEntry");
        $result = $this->itemWishListRepo->removeItemWishListEntry($variationId);
        $this->track("removeItemWishListEntry");

        return $result;
    }
    
    public function migrateGuestItemWishList()
    {
        $this->start("migrateGuestItemWishList");
        /**
         * @var ItemWishListGuestRepository $guestWishListRepo
         */
        $guestWishListRepo = pluginApp(ItemWishListGuestRepository::class);
    
        $guestWishList = $guestWishListRepo->getItemWishList();
    
        if(count($guestWishList))
        {
            /**
             * @var ItemWishListRepository $contactWishListRepo
             */
            $contactWishListRepo = pluginApp(ItemWishListRepository::class);
            
            foreach($guestWishList as $variationId)
            {
                if((int)$variationId > 0)
                {
                    $contactWishListRepo->addItemWishListEntry($variationId);
                }
            }
            
            $guestWishListRepo->resetItemWishList();
        }

        $this->track("migrateGuestItemWishList");

    }
}
