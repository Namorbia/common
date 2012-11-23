<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Mustache templating integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['mustache'] = $app->share(function () use ($app) {
            $loader = new \Mustache_Loader_FilesystemLoader(
                $app['mustache.template_dir'],
                array('extension' => $app['mustache.extension'])
            );
            $mustache = new \Mustache_Engine(array(
                'cache' => $app['mustache.cache_dir'],
                'loader' => $loader,
                'partials_loader' => $loader
            ));
            if (isset($app['translate'])) {
                $mustache->addHelper('t', function ($string) use ($app) {
                    return $app['translate']($string);
                });
            } else {
                $mustache->addHelper('t', function ($string) {
                    return $string;
                });
            }

            return $mustache;
        });
    }

    public function boot(Application $app)
    {
    }
}
