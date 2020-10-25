<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Tracing
    |--------------------------------------------------------------------------
    |
    | This value determines whether or not requests are traced using OpenTelemetry
    | instrumentation.
    |
    */

    'enable' => true,

    /*
    |--------------------------------------------------------------------------
    | Service Name
    |--------------------------------------------------------------------------
    |
    | This is the service name that is sent to your tracing infrastructure. If
    | this is a system made up of multiple components (eg: a microservices
    | architecture), then you should use a specific name that will tell you
    | where in the system a request has been sent.
    |
    */

    'service_name' => 'laravel-otel',

    /*
    |--------------------------------------------------------------------------
    | Zipkin Endpoint
    |--------------------------------------------------------------------------
    |
    | This value is the URL of your Zipkin endpoint. Currently only exporting
    | trace data in Zipkin format is supported. Make sure you include the
    | protocol and port number.
    |
    */

    'zipkin_endpoint' => 'http://localhost:9411/api/v2/spans',

    /*
    |--------------------------------------------------------------------------
    | Tagging
    |--------------------------------------------------------------------------
    |
    | The Trace middleware is able to enrich spans covering a HTTP request with
    | metadata about the request. Using this array you can decide which metadata
    | is included in your spans' tags.
    |
    */

    'tags' => [
        'ip'     => true, // Requester's IP address
        'path'   => true, // Path requested
        'url'    => true, // Full URL requested
        'method' => true, // HTTP method of the request
        'secure' => true, // Whether the request has been secured with SSL/TLS
        'ua'     => true, // Requester's user agent
        'user'   => true, // Authenticated username
    ]
];
