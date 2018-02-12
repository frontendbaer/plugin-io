<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\Frontend\Contracts\Checkout;

/**
 * Class ShippingService
 * @package IO\Services
 */
class ShippingService
{
    use RuntimeTracker;

	/**
	 * @var Checkout
	 */
	private $checkout;

    /**
     * ShippingService constructor.
     * @param Checkout $checkout
     */
	public function __construct(Checkout $checkout)
	{
	    $this->start("constructor");
		$this->checkout = $checkout;
	    $this->track("constructor");
	}

    /**
     * Set the ID of the current shipping profile
     * @param int $shippingProfileId
     */
	public function setShippingProfileId(int $shippingProfileId)
	{
	    $this->start("setShippingProfileId");
		$this->checkout->setShippingProfileId($shippingProfileId);
	    $this->track("setShippingProfileId");
	}
}
