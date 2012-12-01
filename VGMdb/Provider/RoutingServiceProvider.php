<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\RedirectableUrlMatcher;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;

/**
 * Provides caching for routes loaded from YAML configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
         // replace the default url matcher with one that supports caching
        $app['url_matcher'] = $app->share(function () use ($app) {
            if (!isset($app['config.cache_dir']) || !isset($app['routing.matcher_cache_class'])) {
                return new RedirectableUrlMatcher($app['routes'], $app['request_context']);
            }

            $class = $app['routing.matcher_cache_class'];
            $cache = new ConfigCache($app['config.cache_dir'].'/'.$class.'.php', $app['debug']);
            if (!$cache->isFresh($class)) {
                $dumper = new PhpMatcherDumper($app['routes']);

                $options = array(
                    'class'      => $class,
                    'base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher'
                );

                $cache->write($dumper->dump($options), $app['routes']->getResources());
            }

            require_once $cache;

            return new $class($app['request_context']);
        });

        $app['routing.matcher_cache_class'] = 'ProjectUrlMatcher';
    }

    public function boot(Application $app)
    {
        $collection = new RouteCollection();
        $paths = array();

        if (substr($app['routing.config_dir'], -4) === '.yml') {
            $paths[] = $app['routing.config_dir'];
        } else {
            $paths = glob($app['routing.config_dir'] . '/*.yml');
        }

        foreach ($paths as $path) {
            $locator = new FileLocator($path);
            $loader = new YamlFileLoader($locator);
            $collection->addCollection($loader->load($path));
        }

        $app['routes']->addCollection($collection);
    }
}