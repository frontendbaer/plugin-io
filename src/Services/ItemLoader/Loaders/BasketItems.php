<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Helper\RuntimeTracker;
use IO\Services\SessionStorageService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

class BasketItems implements ItemLoaderContract, ItemLoaderPaginationContract
{
    use RuntimeTracker;

    private $options = [];
    
    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $this->start("getSearch");
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [pluginApp(SessionStorageService::class)->getLang()]]);
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
     *
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

        if(array_key_exists('variationIds', $options) && count($options['variationIds']))
        {
            $variationFilter->hasIds($options['variationIds']);
        }

        $this->start("getFilterStack");

        return [
            $clientFilter,
            $variationFilter
        ];
    }
    
    /**
     * @param array $options
     * @return int
     */
    public function getCurrentPage($options = [])
    {
        return (INT)$options['page'];
    }
    
    /**
     * @param array $options
     * @return int
     */
    public function getItemsPerPage($options = [])
    {
        return (INT)$options['items'];
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