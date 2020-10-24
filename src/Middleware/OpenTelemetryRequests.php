<?php

namespace SeanHood\LaravelOpenTelemetry\Middleware;

use Closure;

use OpenTelemetry\Sdk\Trace\TracerProvider;

class OpenTelemetryRequests
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // I'd prefer to use Type Hinting for accessing this OpenTelemetry instance
        // How do I do that?
        $tracer = app('laravel-opentelemetry');

        $span = $tracer->startAndActivateSpan('http_request');

        $span->setAttribute('request.path', $request->path())
             ->setAttribute('request.url', $request->fullUrl())
             ->setAttribute('request.method', $request->method())
             ->setAttribute('request.secure', $request->secure())
             ->setAttribute('request.ip', $request->ip())
             ->setAttribute('request.ua', $request->userAgent());

        $response = $next($request);


        $span->setAttribute('response.status', $response->status());
        $tracer->endActiveSpan();

        return $response;
    }
}
