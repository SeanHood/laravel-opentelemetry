<?php
namespace SeanHood\LaravelOpenTelemetry;

use Illuminate\Support\ServiceProvider;

use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace\Tracer;

/**
 * LaravelOpenTelemetryServiceProvider injects a configured OpenTelemetry Tracer into
 * the Laravel service container, so that instrumentation is traceable.
 */
class LaravelOpenTelemetryServiceProvider extends ServiceProvider
{
    /**
     * Publishes configuration file.
     *
     * @return void
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
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Tracer::class, function () {
            return $this->initOpenTelemetry();
        });
    }


    /**
     * Initialize an OpenTelemetry Tracer with the exporter
     * specified in the application configuration.
     * 
     * @return Tracer|null A configured Tracer, or null if tracing hasn't been enabled.
     */
    private function initOpenTelemetry(): Tracer
    {
        if(!config('laravel_opentelemetry.enable')) {
            return null;
        }

        $zipkinExporter = new ZipkinExporter(
            config('laravel_opentelemetry.service_name'),
            config('laravel_opentelemetry.zipkin_endpoint')
        );

        $provider = new TracerProvider();

        return $provider
            ->addSpanProcessor(new SimpleSpanProcessor($zipkinExporter))
            ->getTracer('io.opentelemetry.contrib.php');
    }
}
