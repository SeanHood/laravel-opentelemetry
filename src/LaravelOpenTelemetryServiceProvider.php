<?php
namespace SeanHood\LaravelOpenTelemetry;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;

class LaravelOpenTelemetryServiceProvider extends ServiceProvider
{
    /**
    * Publishes configuration file.
    *
    * @return  void
    */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/laravel_opentelemetry.php' => config_path('laravel_opentelemetry.php')
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/config/laravel_opentelemetry.php',
            'laravel_opentelemetry'
        );
    }
    /**
    * Make config publishment optional by merging the config from the package.
    *
    * @return  void
    */
    public function register()
    {
        // I think I'd prefer to use Type Hinting for accessing this OpenTelemetry instance
        // How do I do that?
        $this->app->singleton('laravel-opentelemetry', function () {
            return $this->initOpenTelemetry();
        });
    }


    private function initOpenTelemetry()
    {

        if (config('laravel_opentelemetry.enable')) {
            $zipkinExporter = new ZipkinExporter(
                config('laravel_opentelemetry.service_name'),
                config('laravel_opentelemetry.zipkin_endpoint')
            );

            $tracer = (new TracerProvider())
            ->addSpanProcessor(new SimpleSpanProcessor($zipkinExporter))
            ->getTracer('io.opentelemetry.contrib.php');

            return $tracer;
        }
    }
}
