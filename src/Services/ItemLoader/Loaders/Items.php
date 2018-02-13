<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Helper\RuntimeTracker;
use IO\Services\SessionStorageService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Plugin\Application;

/**
 * Class Items
 * @package IO\Services\ItemLoader\Loaders
 */
class Items implements ItemLoaderContract
{
    use RuntimeTracker;

    private $options = [];
    
    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $this->start("getSearch");
        $sessionLang =  $this->options['lang'];
        if ( $sessionLang === null )
        {
            $sessionLang = pluginApp(SessionStorageService::class)->getLang();
        }
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [$sessionLang]]);
        $imageMutator = pluginApp(ImageMutator::class);
        $imageMutator->addClient(pluginApp(Application::class)->getPlentyId());
    
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentProcessor->addMutator($languageMutator);
        $documentProcessor->addMutator($imageMutator);

        $document = pluginApp(DocumentSearch::class, [$documentProcessor]);
        $this->track("getSearch");

        return $document;
    }
    
    /**
     * @return array
     */
    public function getAggregations()
    {
        return [];
    }
    
    /**
     * @param array $options
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        $this->start("getFilterStack");
        /** @var ClientFilter $clientFilter */
        $clientFilter = pluginApp(ClientFilter::class);
        $clientFilter->isVisibleForClient(pluginApp(Application::class)->getPlentyId());
        
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = pluginApp(VariationBaseFilter::class);
        $variationFilter->isActive();

        if(isset($options['itemIds']) && count($options['itemIds']))
        {
            $variationFilter->hasItemIds($options['itemIds']);
        }
        
        if(isset($options['variationIds']) && count($options['variationIds']))
        {
            $variationFilter->hasIds($options['variationIds']);
        }

        $sessionLang =  $options['lang'];
        if ( $sessionLang === null )
        {
            $sessionLang = pluginApp(SessionStorageService::class)->getLang();
        }

        $langMap = [
            'de' => TextFilter::LANG_DE,
            'fr' => TextFilter::LANG_FR,
            'en' => TextFilter::LANG_EN,
        ];

        /**
         * @var TextFilter $textFilter
         */
        $textFilter = pluginApp(TextFilter::class);

        if(isset($langMap[$sessionLang]))
        {
            $textFilterLanguage = $langMap[$sessionLang];

            /**
             * @var TemplateConfigService $templateConfigService
             */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $usedItemName = $templateConfigService->get('item.name');

            $textFilterType = TextFilter::FILTER_ANY_NAME;
            if(strlen($usedItemName))
            {
                if($usedItemName == '0')
                {
                    $textFilterType = TextFilter::FILTER_NAME_1;
                }
                elseif($usedItemName == '1')
                {
                    $textFilterType = TextFilter::FILTER_NAME_2;
                }
                elseif($usedItemName == '2')
                {
                    $textFilterType = TextFilter::FILTER_NAME_3;
                }
            }

            $textFilter->hasNameInLanguage($textFilterLanguage, $textFilterType);
        }

        $this->track("getFilterStack");

        return [
            $clientFilter,
            $variationFilter,
            $textFilter
        ];
    }
    
    public function setOptions($options = [])
    {
        $this->options = $options;
        return $options;
    }

    /**
     * @param array $defaultResultFields
     * @return array
     */
    public function getResultFields($defaultResultFields)
    {
        return $defaultResultFields;
    }
}