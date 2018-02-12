<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\Item\Availability\Contracts\AvailabilityRepositoryContract;
use Plenty\Modules\Item\Availability\Models\Availability;

/**
 * Class AvailabilityService
 * @package IO\Services
 */
class AvailabilityService
{
    use RuntimeTracker;

	/**
	 * @var AvailabilityRepositoryContract
	 */
	private $availabilityRepository;

    /**
     * AvailabilityService constructor.
     * @param AvailabilityRepositoryContract $availabilityRepository
     */
	public function __construct(AvailabilityRepositoryContract $availabilityRepository)
	{
	    $this->start("constructor");
		$this->availabilityRepository = $availabilityRepository;
	    $this->track("constructor");
	}

    /**
     * Get the item availability by ID
     * @param int $availabilityId
     * @return Availability|null
     */
	public function getAvailabilityById( int $availabilityId = 0 )
    {
        $this->start("getAvailabilityById");
        $availability = $this->availabilityRepository->findAvailability( $availabilityId );
        $this->track("getAvailabilityById");
        return $availability;
    }

    /**
     *
     * @return array
     */
    public function getAvailabilities():array
    {
        $this->start("getAvailabilities");
        $availabilities = array();
        for( $i = 1; $i <= 10; $i++ )
        {
	        $availability = $this->getAvailabilityById( $i );
	        if($availability instanceof Availability)
	        {
	            array_push( $availabilities, $this->getAvailabilityById( $i ) );
	        }
        }
        $this->track("getAvailabilities");

        return $availabilities;
    }
}
