<?php

namespace IO\Helper;

class RuntimeTrackingData
{
    public static $runtimes = [];
    public static $callCounts = [];
    public static $startTimes = [];

    public static function getStats()
    {
        $stats = [];

        foreach( self::$runtimes as $className => $trackedMethods )
        {
            $methods = [];
            $totalRuntime = 0;
            $totalCount = 0;
            foreach( $trackedMethods as $methodName => $runtime )
            {
                $totalCount += self::$callCounts[$className][$methodName];
                $totalRuntime += $runtime;

                $methods[] = [
                    'name' => $methodName,
                    'runtime' => round($runtime, 3),
                    'count' => self::$callCounts[$className][$methodName]
                ];
            }

            usort( $methods, function( $methodA, $methodB ) {
                return ($methodB['runtime']*1000) - ($methodA['runtime']*1000);
            });

            $stats[] = [
                'class'         => $className,
                'totalRuntime'  => round($totalRuntime, 3),
                'totalCount'    => $totalCount,
                'methods'       => $methods
            ];
        }

        usort( $stats, function( $statA, $statB ) {
            return ($statB['totalRuntime']*1000) - ($statA['totalRuntime']*1000);
        });

        return $stats;
    }
}