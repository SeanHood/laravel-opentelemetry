<?php

namespace SeanHood\LaravelOpenTelemetry\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenTelemetry\Trace\Span;
use OpenTelemetry\Trace\SpanStatus;
use OpenTelemetry\Trace\Tracer;

/**
 * Trace an incoming HTTP request
 */
class Trace
{
    /**
     * @var Tracer $tracer OpenTelemetry Tracer
     */
    private $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $span = $this->tracer->startAndActivateSpan('http_'.strtolower($request->method()));
        $response = $next($request);

        $this->setSpanStatus($span, $response->status());
        $this->addConfiguredTags($span, $request, $response);
        $span->setAttribute('response.status', $response->status());
        
        $this->tracer->endActiveSpan();

        return $response;
    }

    private function setSpanStatus(Span $span, int $httpStatusCode)
    {
        switch($httpStatusCode) {
            case 400:
                $span->setSpanStatus(SpanStatus::FAILED_PRECONDITION, SpanStatus::DESCRIPTION[SpanStatus::FAILED_PRECONDITION]);
                return;
            case 401:
                $span->setSpanStatus(SpanStatus::UNAUTHENTICATED, SpanStatus::DESCRIPTION[SpanStatus::UNAUTHENTICATED]);
                return;
            case 403:
                $span->setSpanStatus(SpanStatus::PERMISSION_DENIED, SpanStatus::DESCRIPTION[SpanStatus::PERMISSION_DENIED]);
                return;
            case 404:
                $span->setSpanStatus(SpanStatus::NOT_FOUND, SpanStatus::DESCRIPTION[SpanStatus::NOT_FOUND]);
                return;
        }

        if($httpStatusCode >= 500 && $httpStatusCode < 600) {
            $span->setSpanStatus(SpanStatus::INTERNAL, SpanStatus::DESCRIPTION[SpanStatus::INTERNAL]);
        }

        if($httpStatusCode >= 200 && $httpStatusCode <= 300) {
            $span->setSpanStatus(SpanStatus::OK, SpanStatus::DESCRIPTION[SpanStatus::OK]);
        }
    }

    private function addConfiguredTags(Span $span, Request $request, $response)
    {
        $configurationKey = 'laravel_opentelemetry.tags.';

        if(config($configurationKey.'path')) {
            $span->setAttribute('request.path', $request->path());
        }

        if(config($configurationKey.'url')) {
            $span->setAttribute('request.url', $request->fullUrl());
        } 
        
        if(config($configurationKey.'method')) {
            $span->setAttribute('request.method', $request->method());
        }

        if(config($configurationKey.'secure')) {
            $span->setAttribute('request.secure', $request->secure());
        }

        if(config($configurationKey.'ip')) {
            $span->setAttribute('request.ip', $request->ip());
        }

        if(config($configurationKey.'ua')) {
            $span->setAttribute('request.ua', $request->userAgent());
        }

        if(config($configurationKey.'user') && $request->user()) {
            $span->setAttribute('request.user', $request->user()->email);
        }
    }
}
