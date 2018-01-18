<?php
namespace IO\Middlewares;
use IO\Services\PerformanceTrackingService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class Performance
 * @package IO\Middlewares
 */
class Performance extends Middleware
{
    public function before(
        Request $request
    )
    {
    }
    public function after(
        Request $request,
        Response $response
    ): Response
    {
        /** @var PerformanceTrackingService $performanceTracker */
        $performanceTracker = pluginApp(PerformanceTrackingService::class);
        $performanceTracker->save();
        return $response;
    }
}