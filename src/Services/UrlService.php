<?php

namespace IO\Services;

use IO\Helper\MemoryCache;
use IO\Helper\RuntimeTracker;
use IO\Services\UrlBuilder\CategoryUrlBuilder;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\UrlBuilder\VariationUrlBuilder;

class UrlService
{
    use RuntimeTracker;
    use MemoryCache;

    /**
     * Get canonical url for a category
     * @param int           $categoryId
     * @param string|null   $lang
     * @return UrlQuery
     */
    public function getCategoryURL( $categoryId, $lang = null )
    {
        $this->start("getCategoryUrl");
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }
        $categoryUrl = $this->fromMemoryCache(
            "categoryUrl.$categoryId.$lang",
            function() use ($categoryId, $lang) {
                /** @var CategoryUrlBuilder $categoryUrlBuilder */
                $categoryUrlBuilder = pluginApp( CategoryUrlBuilder::class );
                return $categoryUrlBuilder->buildUrl( $categoryId, $lang );
            }
        );

        $this->track("getCategoryUrl");
        return $categoryUrl;
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
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        $variationUrl = $this->fromMemoryCache(
            "variationUrl.$itemId.$variationId.$lang",
            function() use ($itemId, $variationId, $lang) {
                /** @var VariationUrlBuilder $variationUrlBuilder */
                $variationUrlBuilder = pluginApp( VariationUrlBuilder::class );
                $variationUrl = $variationUrlBuilder->buildUrl( $itemId, $variationId, $lang );

                if ( $variationUrl->getPath() !== null )
                {
                    $variationUrl->append(
                        $variationUrlBuilder->getSuffix( $itemId, $variationId )
                    );
                }

                return $variationUrl;
            }
        );

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
        $this->start("getCanonicalUrl");
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        $canonicalUrl = $this->fromMemoryCache(
            "canonicalUrl.$lang",
            function() use ($lang) {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp( CategoryService::class );
                if ( TemplateService::$currentTemplate === 'tpl.item' )
                {
                    $currentItem = $categoryService->getCurrentItem();
                    if ( count($currentItem) > 0 )
                    {
                        return $this
                            ->getVariationURL( $currentItem['item']['id'], $currentItem['variation']['id'], $lang )
                            ->toAbsoluteUrl( $lang !== null );
                    }

                    return null;
                }

                if ( substr(TemplateService::$currentTemplate,0, 12) === 'tpl.category' )
                {
                    $currentCategory = $categoryService->getCurrentCategory();

                    if ( $currentCategory !== null )
                    {
                        return $this
                            ->getCategoryURL( $currentCategory->id, $lang )
                            ->toAbsoluteUrl( $lang !== null );
                    }
                    return null;
                }

                if ( TemplateService::$currentTemplate === 'tpl.home' )
                {
                    return pluginApp( UrlQuery::class, ['path' => "", 'lang' => $lang])
                        ->toAbsoluteUrl( $lang !== null );
                }

                return null;
            }
        );
        $this->track("getCanonicalUrl");

        return $canonicalUrl;

    }

    /**
     * Get equivalent canonical urls for each active language
     * @return array
     */
    public function getLanguageURLs()
    {
        $this->start("getLanguageUrls");
        $languageUrls = $this->fromMemoryCache(
            "languageUrls",
            function() {
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

                return $result;
            }
        );

        $this->track("getLanguageUrls");
        return $languageUrls;
    }
}