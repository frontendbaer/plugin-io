<?php

namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Events\Dispatcher;

class ExternalSearch
{
    const EXTERNAL_SEARCH_EVENT = 'IO.Search.Query';

    public $searchString = "";
    public $categoryId   = 0;
    public $page         = 1;
    public $itemsPerPage = 20;
    public $sorting      = "";

    public $results      = [];
    public $countTotal   = null;


    public static function getExternalResults( $container )
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp( Dispatcher::class );

        $dispatcher->fire( self::EXTERNAL_SEARCH_EVENT, [$container] );

        if ( $container->countTotal === null )
        {
            $container->countTotal = count( $container->results );
        }
        return $container;
    }

    public static function hasExternalSearch()
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp( Dispatcher::class );
        return $dispatcher->hasListeners( self::EXTERNAL_SEARCH_EVENT );
    }
}