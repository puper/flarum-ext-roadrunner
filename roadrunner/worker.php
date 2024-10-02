<?php

require __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;

use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;

use Illuminate\Support\Arr;

use Psr\Http\Message\StreamInterface;


class Site extends \Flarum\Foundation\Site {
    public static function fromPaths(array $paths)
    {
        $paths = new \Flarum\Foundation\Paths($paths);

        date_default_timezone_set('UTC');

        if (! static::hasConfigFile($paths->base)) {
            // Instantiate site instance for new installations,
            // fallback to localhost for validation of Config for instance in CLI.
            return new \Flarum\Foundation\UninstalledSite(
                $paths,
                Arr::get($_SERVER, 'REQUEST_URI', 'http://localhost')
            );
        }

        return (
            new InstalledSite($paths, static::loadConfig($paths->base))
        )->extendWith(static::loadExtenders($paths->base));
    }
}

class InstalledSite extends \Flarum\Foundation\InstalledSite {
    public $container;
    public function bootApp(): \Flarum\Foundation\InstalledApp
    {
        if ($this->container === null) {
            $this->container = $this->bootLaravel();
        }
        $container = clone $this->container;
        \Illuminate\Container\Container::setInstance($this->container);
        return new \Flarum\Foundation\InstalledApp(
            $container,
            $this->config
        );
    }
}

// Create new RoadRunner worker from global environment
$worker = Worker::create();

// Create common PSR-17 HTTP factory
$factory = new Psr17Factory();

$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

$site = Site::fromPaths([
    'base' => __DIR__.'/../',
    'public' => __DIR__.'/../public',
    'storage' => __DIR__.'/../storage',
]);
$site->bootApp();

$resetExtSeoContainerCallback = function() {
    static::$container = null;
};

$resetExtSeoContainer = $resetExtSeoContainerCallback->bindTo(new \V17Development\FlarumSeo\Extend, \V17Development\FlarumSeo\Extend::class);


while (true) {
    if (class_exists(\V17Development\FlarumSeo\Extend::class)) {
        $resetExtSeoContainer();
    }
    try {
        $request = $psr7->waitRequest();
        if ($request === null) {
            break;
        }
    } catch (\Throwable $e) {
        // Although the PSR-17 specification clearly states that there can be
        // no exceptions when creating a request, however, some implementations
        // may violate this rule. Therefore, it is recommended to process the 
        // incoming request for errors.
        //
        // Send "Bad Request" response.
        $psr7->respond(new Response(400));
        continue;
    }

    try {
        // Here is where the call to your application code will be located. 
        // For example:
        //  $response = $app->send($request);
        //
        // Reply by the 200 OK response
        $psrResponse = $site->bootApp()->getRequestHandler()->handle($request);
        if ($psrResponse instanceof \Laminas\Diactoros\Response\HtmlResponse) {
            $body = $psrResponse->getBody();
            $content = $body->getContents();
            $body->close();
            $psr7->respond(new Response(200, $psrResponse->getHeaders(),  $content));
        } else {
            $psr7->respond($psrResponse);
        }
    } catch (\Throwable $e) {
        // In case of any exceptions in the application code, you should handle
        // them and inform the client about the presence of a server error.
        //
        // Reply by the 500 Internal Server Error response
        $psr7->respond(new Response(500, [], 'Something Went Wrong!'));
        
        // Additionally, we can inform the RoadRunner that the processing 
        // of the request failed.
        $psr7->getWorker()->error((string)$e);
    }
}