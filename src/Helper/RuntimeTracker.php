<?php

namespace IO\Helper;

trait RuntimeTracker
{
    protected function start( $key )
    {
        if( !array_key_exists( self::class, RuntimeTrackingData::$startTimes ) )
        {
            RuntimeTrackingData::$startTimes[self::class] = [];
        }

        if ( !array_key_exists( self::class, RuntimeTrackingData::$callCounts ) )
        {
            RuntimeTrackingData::$callCounts[self::class] = [];
        }

        if ( !array_key_exists( $key, RuntimeTrackingData::$callCounts[self::class] ) )
        {
            RuntimeTrackingData::$callCounts[self::class][$key] = 0;
        }

        RuntimeTrackingData::$callCounts[self::class][$key] = RuntimeTrackingData::$callCounts[self::class][$key] + 1;
        RuntimeTrackingData::$startTimes[self::class][$key] = microtime(true) * 1000;
    }

    protected function track( $key )
    {
        if ( array_key_exists( self::class, RuntimeTrackingData::$startTimes ) && array_key_exists($key, RuntimeTrackingData::$startTimes[self::class] ) )
        {
            if ( !array_key_exists( self::class, RuntimeTrackingData::$runtimes ) )
            {
                RuntimeTrackingData::$runtimes[self::class] = [];
            }

            if ( !array_key_exists( $key, RuntimeTrackingData::$runtimes[self::class] ) )
            {
                RuntimeTrackingData::$runtimes[self::class][$key] = 0;
            }

            RuntimeTrackingData::$runtimes[self::class][$key] += ((microtime(true) * 1000) - RuntimeTrackingData::$startTimes[self::class][$key]);
        }
    }
}
