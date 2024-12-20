<?php

namespace TelexApm\TelexAPM\Middleware;

use Closure;
use TelexApm\TelexAPM\Services\MetricsService;
use Throwable;

class TelexAPMMiddleware
{
    protected $metricsService;

    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, Throwable $exception)
    {
        $start = microtime(true);

        // Proceed with the request
        $response = $next($request);

        $duration = microtime(true) - $start;

        // Collect metrics
        $this->metricsService->collectMetrics($request, $response, $duration, $exception);

        return $response;
    }
}