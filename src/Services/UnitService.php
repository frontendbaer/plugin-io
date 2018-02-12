<?php //strict

namespace IO\Services;

use IO\Helper\MemoryCache;
use IO\Helper\RuntimeTracker;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use Plenty\Modules\Item\Unit\Models\UnitName;

/**
 * Class UnitService
 * @package IO\Services
 */
class UnitService
{
    use MemoryCache;
    use RuntimeTracker;

	/**
	 * @var UnitNameRepositoryContract
	 */
	private $unitNameRepository;

    /**
     * UnitService constructor.
     * @param UnitNameRepositoryContract $unitRepository
     */
	public function __construct(UnitNameRepositoryContract $unitRepository)
	{
	    $this->start("constructor");
		$this->unitNameRepository = $unitRepository;
	    $this->track("constructor");
	}

    /**
     * Get the unit by ID
     * @param int $unitId
     * @param string $lang
     * @return UnitName
     */
	public function getUnitById(int $unitId, string $lang = "de"):UnitName
	{
	    $this->start("getUnitById");
		$unit = $this->unitNameRepository->findOne($unitId, $lang);
	    $this->track("getUnitById");

	    return $unit;
	}

    public function getUnitNameByKey( $unitKey, $lang = null )
    {
        $this->start("getUnitNameByKey");
        if ( $lang === null )
        {
            $lang = pluginApp(SessionStorageService::class)->getLang();
        }

        $unitName = $this->fromMemoryCache(
            "unitName.$unitKey.$lang",
            function() use ($unitKey, $lang)
            {
                /**
                 * @var UnitRepositoryContract $unitRepository
                 */
                $unitRepository = pluginApp(UnitRepositoryContract::class);

                /** @var AuthHelper $authHelper */
                $authHelper = pluginApp(AuthHelper::class);

                $unitData = $authHelper->processUnguarded( function() use ($unitRepository, $unitKey)
                {
                    $unitRepository->setFilters(['unitOfMeasurement' => $unitKey]);
                    return $unitRepository->all(['*'], 1, 1);
                });


                $unitId = $unitData->getResult()->first()->id;

                return $this->unitNameRepository->findOne($unitId, $lang)->name;
            }
        );
        $this->track("getUnitNameByKey");

        return $unitName;
    }
}
