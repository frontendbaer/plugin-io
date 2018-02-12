<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Frontend\Contracts\Checkout;

/**
 * Class CountryService
 * @package IO\Services
 */
class CountryService
{
    use RuntimeTracker;

	/**
	 * @var CountryRepositoryContract
	 */
	private $countryRepository;

    /**
     * @var Country[][]
     */
	private static $activeCountries = [];

    /**
     * CountryService constructor.
     * @param CountryRepositoryContract $countryRepository
     */
	public function __construct(CountryRepositoryContract $countryRepository)
	{
	    $this->start("constructor");
		$this->countryRepository = $countryRepository;
	    $this->track("constructor");
	}

    /**
     * List all active countries
     * @param string $lang
     * @return Country[]
     */
    public function getActiveCountriesList($lang = 'de'):array
    {
        $this->start("getActiveCountriesList");
        if (!isset(self::$activeCountries[$lang])) {
            $list = $this->countryRepository->getActiveCountriesList();

            foreach ($list as $country) {
                $country->currLangName   = $country->names->contains('language', $lang) ?
                    $country->names->where('language', $lang)->first()->name :
                    $country->names->first()->name;
                self::$activeCountries[$lang][] = $country;
            }
        }

        $this->track("getActiveCountriesList");
        return self::$activeCountries[$lang];
    }

    /**
     * Get a list of names for the active countries
     * @param string $language
     * @return array
     */
	public function getActiveCountryNameMap(string $language):array
	{
	    $this->start("getActiveCountryNameMap");
        $nameMap = [];
        foreach ($this->getActiveCountriesList($language) as $country) {
            $nameMap[$country->id] = $country->currLangName;
        }

	    $this->track("getActiveCountryNameMap");
        return $nameMap;
	}

    /**
     * Set the ID of the current shipping country
     * @param int $shippingCountryId
     */
	public function setShippingCountryId(int $shippingCountryId)
	{
	    $this->start("setShippingCountryId");
		pluginApp(Checkout::class)->setShippingCountryId($shippingCountryId);
	    $this->track("setShippingCountryId");
	}

    /**
     * Get a specific country by ID
     * @param int $countryId
     * @return Country
     */
	public function getCountryById(int $countryId):Country
	{
	    $this->start("getCountryById");
		$result = $this->countryRepository->getCountryById($countryId);
	    $this->track("getCountryById");

		return $result;
	}

    /**
     * Get the name of specific country
     * @param int $countryId
     * @param string $lang
     * @return string
     */
	public function getCountryName(int $countryId, string $lang = "de"):string
	{
	    $this->start("getCountryName");
		$country = $this->countryRepository->getCountryById($countryId);
		if($country instanceof Country && count($country->names) != 0)
		{
			foreach($country->names as $countryName)
			{
				if($countryName->language == $lang)
				{
	                $this->track("getCountryName");
					return $countryName->name;
				}
			}

			$countryName = $country->names[0]->name;
            $this->track("getCountryName");

            return $countryName;
		}
		return "";
	}
}
