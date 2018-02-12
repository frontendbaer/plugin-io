<?php

namespace IO\Middlewares;

use IO\Helper\RuntimeTracker;
use IO\Helper\RuntimeTrackingData;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class RuntimeTrackerMiddleware extends Middleware
{
    use Loggable;
    use RuntimeTracker;
    private $startTime = 0;

    public function __construct()
    {
    }

    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        $this->startTime = microtime(true);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        $runtime = microtime(true) - $this->startTime;
        $runtime = number_format( $runtime, 3 );
        $this->getLogger('RuntimeLogger')->error("Server responds after $runtime seconds", RuntimeTrackingData::getStats() );
        return $response;
    }
}