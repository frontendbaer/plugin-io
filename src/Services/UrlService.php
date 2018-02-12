<?php

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use IO\Helper\ShopUrl;
use IO\Services\UrlBuilder\CategoryUrlBuilder;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\UrlBuilder\VariationUrlBuilder;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;
use Plenty\Plugin\Application;

class UrlService
{
    use RuntimeTracker;

    /**
     * Get canonical url for a category
     * @param int           $categoryId
     * @param string|null   $lang
     * @return UrlQuery
     */
    public function getCategoryURL( $categoryId, $lang = null )
    {
        $this->start("getCategoryURL");
        /** @var CategoryUrlBuilder $categoryUrlBuilder */
        $categoryUrlBuilder = pluginApp( CategoryUrlBuilder::class );
        $url = $categoryUrlBuilder->buildUrl( $categoryId, $lang );
        $this->track("getCategoryURL");
        return $url;
    }

    /**
     * Get canonical url for a variation
     * @param int           $itemId
     * @param int           $variationId
     * @param string|null   $lang
     * @return UrlQuery
     */
    public function getVariationURL( $itemId, $variationId, $lang = null )
    {
        $this->start("getVariationURL");
        /** @var VariationUrlBuilder $variationUrlBuilder */
        $variationUrlBuilder = pluginApp( VariationUrlBuilder::class );
        $variationUrl = $variationUrlBuilder->buildUrl( $itemId, $variationId, $lang );

        if ( $variationUrl->getPath() !== null )
        {
            $variationUrl->append(
                $variationUrlBuilder->getSuffix( $itemId, $variationId )
            );
        }

        $this->track("getVariationURL");
        return $variationUrl;
    }

    /**
     * Get canonical url for current page
     * @param string|null   $lang
     * @return string|null
     */
    public function getCanonicalURL( $lang = null )
    {
        $this->start("getCanonicalURL");
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp( CategoryService::class );
        if ( TemplateService::$currentTemplate === 'tpl.item' )
        {
            $currentItem = $categoryService->getCurrentItem();
            $itemURL = null;
            if ( count($currentItem) > 0 )
            {
                $itemURL = $this
                    ->getVariationURL( $currentItem['item']['id'], $currentItem['variation']['id'], $lang )
                    ->toAbsoluteUrl( $lang !== null );
            }

            $this->track("getCanonicalURL");
            return $itemURL;
        }

        if ( substr(TemplateService::$currentTemplate,0, 12) === 'tpl.category' )
        {
            $categoryURL = null;
            $currentCategory = $categoryService->getCurrentCategory();

            if ( $currentCategory !== null )
            {
                $categoryURL = $this
                    ->getCategoryURL( $currentCategory->id, $lang )
                    ->toAbsoluteUrl( $lang !== null );
            }
            $this->track("getCanonicalURL");
            return $categoryURL;
        }

        $url = null;
        if ( TemplateService::$currentTemplate === 'tpl.home' )
        {
            $url = pluginApp( UrlQuery::class, ['path' => "", 'lang' => $lang])
                ->toAbsoluteUrl( $lang !== null );
        }

        $this->track("getCanonicalURL");
        return null;
    }

    /**
     * Get equivalent canonical urls for each active language
     * @return array
     */
    public function getLanguageURLs()
    {
        $this->start("getLanguageURLs");
        $result = [];
        $defaultUrl = $this->getCanonicalURL();

        if ( $defaultUrl !== null )
        {
            $result["x-default"] = $defaultUrl;
        }

        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp( WebstoreConfigurationService::class );
        foreach( $webstoreConfigService->getActiveLanguageList() as $language )
        {
            $url = $this->getCanonicalURL( $language );
            if ( $url !== null )
            {
                $result[$language] = $url;
            }
        }

        $this->track("getLanguageURLs");

        return $result;
    }
}