<?php

namespace IO\Services;

use IO\Constants\CrossSellingType;
use IO\Constants\SessionStorageKeys;
use IO\Helper\RuntimeTracker;
use IO\Services\SessionStorageService;

/**
 * Class ItemCrossSellingService
 * @package IO\Services
 */
class ItemCrossSellingService
{
    use RuntimeTracker;

    private $sessionStorage;
    
    /**
     * ItemLastSeenService constructor.
     * @param \IO\Services\SessionStorageService $sessionStorage
     */
    public function __construct(SessionStorageService $sessionStorage)
    {
        $this->start("constructor");
        $this->sessionStorage = $sessionStorage;
        $this->track("constructor");
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->start("setType");
        if(strlen($type))
        {
            $this->sessionStorage->setSessionValue(SessionStorageKeys::CROSS_SELLING_TYPE, $type);
        }
        $this->track("setType");
    }
    
    public function getType()
    {
        $this->start("getType");
        $type = $this->sessionStorage->getSessionValue(SessionStorageKeys::CROSS_SELLING_TYPE);

        if(!is_null($type) && strlen($type))
        {
            $this->track("getType");
            return $type;
        }
        $this->track("getType");

        return CrossSellingType::SIMILAR;
    }
}