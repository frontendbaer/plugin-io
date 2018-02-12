<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Application;

/**
 * Class WebstoreConfigurationService
 * @package IO\Services
 */
class WebstoreConfigurationService
{
    use RuntimeTracker;

    /**
     * @var WebstoreConfiguration
     */
    private $webstoreConfig;


    /**
     * Get the plenty-id
     */
    public function getPlentyId()
    {
        $this->start("getPlentyId");
        $plentyId = pluginApp(Application::class)->getPlentyId();
        $this->track("getPlentyId");
        return $plentyId;
    }

    /**
     * Get the webstore configuraion
     */
	public function getWebstoreConfig():WebstoreConfiguration
    {
        $this->start("getWebstoreConfig");
        if( $this->webstoreConfig === null )
        {
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfig */
            $webstoreConfig = pluginApp(WebstoreConfigurationRepositoryContract::class);

            /** @var Application $app */
            $app = pluginApp(Application::class);

            $this->webstoreConfig = $webstoreConfig->findByPlentyId($app->getPlentyId());
        }

        $this->track("getWebstoreConfig");

        return $this->webstoreConfig;
    }

	/**
	 * Get the activate languages of the webstore
	 */
    public function getActiveLanguageList()
	{
	    $this->start("getActiveLanguageList");
        $activeLanguages = [];
        
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $languages = $templateConfigService->get('language.active_languages');
        
        if(!is_null($languages) && strlen($languages))
        {
            $activeLanguages = explode(', ', $languages);
        }

        $this->track("getActiveLanguageList");

        return $activeLanguages;
	}

	/**
	 * Get the default language of the webstore
	 */
    public function getDefaultLanguage()
    {
        $this->start("getDefaultLanguage");
        $language = $this->getWebstoreConfig()->defaultLanguage;
        $this->track("getDefaultLanguage");

        return $language;
    }

    /**
	 * Get the default parcel-service-Id of the webstore
	 */
    public function getDefaultParcelServiceId()
    {
        $this->start("getDefaultParcelServiceId");
        $defaultId = $this->getWebstoreConfig()->defaultParcelServiceId;
        $this->track("getDefaultParcelServiceId");

        return $defaultId;
    }

    /**
     * Get the default parcel-service-preset-Id of the webstore
     */
    public function getDefaultParcelServicePresetId()
    {
        $this->start("getDefaultParselServicePresetId");
        $defaultId = $this->getWebstoreConfig()->defaultParcelServicePresetId;
        $this->start("getDefaultParselServicePresetId");

        return $defaultId;
    }

    /**
     * Get the default shipping-country-Id of the webstore
     */
    public function getDefaultShippingCountryId()
    {
        $this->start("getDefaultShippingCountryId");
        $sessionService = pluginApp(SessionStorageService::class);
        $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryList[$sessionService->getLang()];

        if($defaultShippingCountryId <= 0)
        {
            $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryId;
        }

        $this->track("getDefaultShippingCountryId");
        return $defaultShippingCountryId;
    }
}
