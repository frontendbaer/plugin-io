<?php

namespace IO\Helper;
use IO\Services\PerformanceTrackingService;

/**
 * Class Performance
 * @package IO\Helper
 */
trait Performance
{
    private $trackedKeys = [];
    /**
     * @param string $key
     */
    private function trackRuntime($key)
    {
        /** @var PerformanceTrackingService $tracker */
        $tracker = pluginApp(PerformanceTrackingService::class);
        $tracker->trackRuntime(__CLASS__ . ' - '.$key);
    }
    private function start($key)
    {
        $this->trackedKeys[$key] = microtime(true);
    }
    /**
     * @param $key
     */
    private function track($key)
    {
        /** @var PerformanceTrackingService $tracker */
        $tracker = pluginApp(PerformanceTrackingService::class);
        $tracker->trackDuration(__CLASS__ . ' - '.$key, microtime(true)-$this->trackedKeys[$key]);
    }
}