<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Frontend\Session\Storage\Models\Customer;

/**
 * Class SessionStorageService
 * @package IO\Services
 */
class SessionStorageService
{
    use RuntimeTracker;
	/**
	 * @var FrontendSessionStorageFactoryContract
	 */
	private $sessionStorage;

    /**
     * SessionStorageService constructor.
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     */
	public function __construct(FrontendSessionStorageFactoryContract $sessionStorage)
	{
	    $this->start("constructor");
		$this->sessionStorage = $sessionStorage;
	    $this->track("constructor");
	}

    /**
     * Set the value in the session
     * @param string $name
     * @param $value
     */
	public function setSessionValue(string $name, $value)
	{
	    $this->start("setSessionValue");
		$this->sessionStorage->getPlugin()->setValue($name, $value);
	    $this->track("setSessionValue");
	}

    /**
     * Get a value from the session
     * @param string $name
     * @return mixed
     */
	public function getSessionValue(string $name)
	{
	    $this->start("getSessionValue");
		$value = $this->sessionStorage->getPlugin()->getValue($name);
	    $this->start("getSessionValue");

	    return $value;
	}

    /**
     * Get the language from session
     * @return string|null
     */
	public function getLang()
	{
	    $this->start("getLang");
        $lang = $this->sessionStorage->getLocaleSettings()->language;

        if(is_null($lang) || !strlen($lang))
        {
            $lang = 'de';
        }

	    $this->track("getLang");
		return $lang;
	}

    /**
     * @return Customer
     */
	public function getCustomer()
    {
        $this->start("getCustomer");
        $customer = $this->sessionStorage->getCustomer();
        $this->track("getCustomer");

        return $customer;
    }
}
