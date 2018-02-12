<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use IO\Services\SessionStorageService;
use IO\Services\CountryService;
use IO\Services\WebstoreConfigurationService;
use IO\Services\CheckoutService;
use Plenty\Modules\Frontend\Services\LocaleService;
use Plenty\Plugin\Data\Contracts\Resources;

class LocalizationService
{
    use RuntimeTracker;

    public function __construct()
    {
        
    }

    public function getLocalizationData()
    {
        $this->start("getLocalizationData");
        $sessionStorage = pluginApp(SessionStorageService::class);
        $country        = pluginApp(CountryService::class);
        $webstoreConfig = pluginApp(WebstoreConfigurationService::class);
        $checkout       = pluginApp(CheckoutService::class);

        $lang = $sessionStorage->getLang();
        if(is_null($lang) || !strlen($lang))
        {
            $lang = 'de';
        }

        $currentShippingCountryId = $checkout->getShippingCountryId();
        if($currentShippingCountryId <= 0)
        {
            $currentShippingCountryId = $webstoreConfig->getDefaultShippingCountryId();
        }

        $result = [
            'activeShippingCountries'  => $country->getActiveCountriesList($lang),
            'activeShopLanguageList'   => $webstoreConfig->getActiveLanguageList(),
            'currentShippingCountryId' => $currentShippingCountryId,
            'shopLanguage'             => $lang
        ];
        $this->track("getLocalizationData");

        return $result;

    }

    public function setLanguage($newLanguage, $fireEvent = true)
    {
        $this->start("setLanguage");
        $localeService = pluginApp(LocaleService::class);
        $localeService->setLanguage($newLanguage, $fireEvent);
        $this->track("setLanguage");
    }

    public function getTranslations( string $plugin, string $group, $lang = null )
    {
        $this->start("getTranslations");
        if ( $lang === null )
        {
            $lang = pluginApp(SessionStorageService::class)->getLang();
        }

        /** @var Resources $resource */
        $resource = pluginApp( Resources::class );

        $translations = $resource->load( "$plugin::lang/$lang/$group" )->getData();
        $this->track("getTranslations");

        return $translations;
    }
}