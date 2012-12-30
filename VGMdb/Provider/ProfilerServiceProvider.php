<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides Symfony's profiling tools.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ProfilerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // defaults
        $app['profiler.class'] = 'Symfony\\Component\\HttpKernel\\Profiler\\Profiler';
        $app['profiler.enabled'] = $app['debug'];
        $app['profiler_listener.class'] = 'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener';
        $app['profiler_listener.only_exceptions'] = false;
        $app['profiler_listener.only_master_requests'] = false;
        $app['profiler.request_matcher.class'] = 'Symfony\\Component\\HttpFoundation\\RequestMatcher';
        $app['profiler.request_matcher.path'] = null;
        $app['profiler.request_matcher.host'] = null;
        $app['profiler.request_matcher.methods'] = null;
        $app['profiler.request_matcher.ip'] = null;
        $app['profiler.controller.profiler'] = 'VGMdb\\Component\\WebProfiler\\Controllers\\ProfilerController';

        // data collector classes
        $app['data_collector.config.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector';
        $app['data_collector.request.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector';
        $app['data_collector.exception.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\ExceptionDataCollector';
        $app['data_collector.events.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\EventDataCollector';
        $app['data_collector.logger.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\LoggerDataCollector';
        $app['data_collector.time.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\TimeDataCollector';
        $app['data_collector.memory.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\MemoryDataCollector';
        $app['data_collector.router.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\RouterDataCollector';
        $app['data_collector.container.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\ContainerDataCollector';
        $app['data_collector.view.class'] = 'VGMdb\\Component\\View\\DataCollector\\ViewDataCollector';
        $app['data_collector.db.class'] = 'VGMdb\\Component\\Doctrine\\DataCollector\\DoctrineDataCollector';
        $app['data_collector.propel.class'] = 'VGMdb\\Component\\Propel\\DataCollector\\PropelDataCollector';
        $app['data_collector.guzzle.class'] = 'VGMdb\\Component\\Guzzle\\DataCollector\\GuzzleDataCollector';

        // data collectors. remember to guard against nonexistent providers by returning -1
        $app['data_collector.config'] = $app->share(function () use ($app) {
            return new $app['data_collector.config.class']($app);
        });
        $app['data_collector.request'] = $app->share(function () use ($app) {
            return new $app['data_collector.request.class']();
        });
        $app['data_collector.exception'] = $app->share(function () use ($app) {
            return new $app['data_collector.exception.class']();
        });
        $app['data_collector.events'] = $app->share(function () use ($app) {
            return new $app['data_collector.events.class']($app['dispatcher']);
        });
        $app['data_collector.logger'] = $app->share(function () use ($app) {
            if (!isset($app['logger'])) {
                return -1;
            }
            return new $app['data_collector.logger.class']($app['logger']);
        });
        $app['data_collector.time'] = $app->share(function () use ($app) {
            return new $app['data_collector.time.class']($app);
        });
        $app['data_collector.memory'] = $app->share(function () use ($app) {
            return new $app['data_collector.memory.class']();
        });
        $app['data_collector.router'] = $app->share(function () use ($app) {
            return new $app['data_collector.router.class']();
        });
        $app['data_collector.container'] = $app->share(function () use ($app) {
            return new $app['data_collector.container.class']($app);
        });
        $app['data_collector.view'] = $app->share(function () use ($app) {
            return new $app['data_collector.view.class']($app['view.logger']);
        });
        $app['data_collector.db'] = $app->share(function () use ($app) {
            if (!isset($app['db'])) {
                return -1;
            }
            $collector = new $app['data_collector.db.class']();
            $collector->addLogger('default', $app['db.debug_logger']);

            return $collector;
        });
        $app['data_collector.propel'] = $app->share(function () use ($app) {
            if (!isset($app['propel.configuration'])) {
                return -1;
            }
            return new $app['data_collector.propel.class']($app['propel.logger'], $app['propel.configuration']);
        });
        $app['data_collector.guzzle'] = $app->share(function () use ($app) {
            if (!isset($app['guzzle'])) {
                return -1;
            }
            return new $app['data_collector.guzzle.class']($app['guzzle.debug_logger.adapter']);
        });

        $app['data_collector.registry'] = array(
            'config'    => array(255, 'collector/config'),
            'request'   => array(255, 'collector/request'),
            'exception' => array(255, 'collector/exception'),
            'events'    => array(255, 'collector/events'),
            'logger'    => array(255, 'collector/logger'),
            'time'      => array(255, 'collector/time'),
            'memory'    => array(255, null),
            'router'    => array(255, 'collector/router'),
            'container' => array(255, 'collector/container'),
            'view'      => array(255, 'collector/view'),
            'db'        => array(255, 'collector/db'),
            'propel'    => array(255, 'collector/propel'),
            'guzzle'    => array(255, 'collector/guzzle'),
        );

        // stopwatch
        $app['debug.stopwatch.class'] = 'Symfony\\Component\\Stopwatch\\Stopwatch';

        $app['debug.stopwatch'] = $app->share(function () use ($app) {
            return new $app['debug.stopwatch.class']();
        });

        // deprecation listener
        $app['debug.deprecation_logger_listener.class'] = 'Symfony\\Component\\HttpKernel\\EventListener\\DeprecationLoggerListener';

        $app['debug.deprecation_logger_listener'] = $app->share(function () use ($app) {
            return new $app['debug.deprecation_logger_listener.class']($app['logger']);
        });

        // replace dispatcher class with traceable implementation
        $app['debug.dispatcher.class'] = 'VGMdb\\Component\\HttpKernel\\Debug\\TraceableEventDispatcher';

        $app['dispatcher'] = $app->share($app->extend('dispatcher', function ($dispatcher) use ($app) {
            $debugDispatcher = new $app['debug.dispatcher.class']($dispatcher, $app['debug.stopwatch'], $app['logger']);
            $debugDispatcher->setProfiler($app['profiler']);

            return $debugDispatcher;
        }));

        // replace controller resolver with traceable implementation
        $app['debug.resolver.class'] = 'Symfony\\Component\\HttpKernel\\Controller\\TraceableControllerResolver';

        $app['resolver'] = $app->share($app->extend('resolver', function ($resolver) use ($app) {
            return new $app['debug.resolver.class']($resolver, $app['debug.stopwatch']);
        }));

        // replace view logger with traceable implementation
        $app['debug.view_logger.class'] = 'VGMdb\\Component\\View\\Logging\\ViewLogger';

        $app['view.logger'] = $app->share(function () use ($app) {
            return new $app['debug.view_logger.class']($app['logger'], $app['debug.stopwatch']);
        });

        // replace Doctrine logger with traceable implementation
        $app['debug.db_logger.class'] = 'VGMdb\\Component\\Doctrine\\DBAL\\Logging\\SQLDebugLogger';

        $app['db.debug_logger'] = $app->share(function () use ($app) {
            return new $app['debug.db_logger.class']($app['logger'], $app['debug.stopwatch']);
        });

        // replace Propel logger with traceable implementation
        $app['debug.propel_logger.class'] = 'VGMdb\\Component\\Propel\\Logger\\PropelLogger';

        $app['propel.logger'] = $app->share(function () use ($app) {
            return new $app['debug.propel_logger.class']($app['logger'], $app['debug.stopwatch']);
        });

        // add debug logger and request subscriber to Guzzle
        $app['guzzle.debug_logger'] = $app->share(function() use ($app) {
            return new \Guzzle\Plugin\Log\LogPlugin($app['guzzle.debug_logger.adapter']);
        });

        $app['guzzle.debug_logger.adapter'] = $app->share(function() use ($app) {
            return new \Guzzle\Log\ArrayLogAdapter();
        });

        $app['guzzle.request_listener'] = $app->share(function() use ($app) {
            return new \VGMdb\Component\Guzzle\EventListener\GuzzleRequestListener($app['debug.stopwatch']);
        });

        $app['guzzle.client'] = $app->share($app->extend('guzzle.client', function ($guzzle) use ($app) {
            $guzzle->addSubscriber($app['guzzle.debug_logger']);
            $guzzle->addSubscriber($app['guzzle.request_listener']);

            return $guzzle;
        }));

        // replace Serializer with traceable implementation
        $app['debug.serializer.class'] = 'VGMdb\\Component\\Serializer\\Debug\\TraceableSerializer';

        $app['serializer'] = $app->share($app->extend('serializer', function ($serializer) use ($app) {
            return new $app['debug.serializer.class']($serializer, $app['debug.stopwatch'], $app['logger']);
        }));

        $app['profiler'] = $app->share(function () use ($app) {
            $profiler = new $app['profiler.class']($app['profiler.storage'], $app['logger']);

            $collectors = new \SplPriorityQueue();
            $order = PHP_INT_MAX;
            foreach ($app['data_collector.registry'] as $id => $attributes) {
                $priority = isset($attributes[0]) ? $attributes[0] : 0;
                $template = null;

                if (isset($attributes[1])) {
                    $template = array($id, $attributes[1]);
                }

                $collectors->insert(array($id, $template), array($priority, --$order));
            }

            $templates = array();
            foreach ($collectors as $collector) {
                $data_collector = $app['data_collector.' . $collector[0]];
                if (-1 !== $data_collector) {
                    $profiler->add($data_collector);
                    $templates[$collector[0]] = $collector[1][1];
                }
            }

            $app['data_collector.templates'] = $templates;

            if (!$app['profiler.enabled']) {
                $profiler->disable();
            }

            return $profiler;
        });

        $app['profiler.storage'] = $app->share(function () use ($app) {
            $supported = array(
                'sqlite'    => 'Symfony\\Component\\HttpKernel\\Profiler\\SqliteProfilerStorage',
                'mysql'     => 'Symfony\\Component\\HttpKernel\\Profiler\\MysqlProfilerStorage',
                'file'      => 'Symfony\\Component\\HttpKernel\\Profiler\\FileProfilerStorage',
                'mongodb'   => 'Symfony\\Component\\HttpKernel\\Profiler\\MongoDbProfilerStorage',
                'memcache'  => 'Symfony\\Component\\HttpKernel\\Profiler\\MemcacheProfilerStorage',
                'memcached' => 'Symfony\\Component\\HttpKernel\\Profiler\\MemcachedProfilerStorage',
                'redis'     => 'Symfony\\Component\\HttpKernel\\Profiler\\RedisProfilerStorage'
            );
            list($class, $dummy) = explode(':', $app['profiler.storage.dsn'], 2);
            if (!isset($supported[$class])) {
                throw new \LogicException(sprintf('Driver "%s" is not supported for the profiler.', $class));
            }

            return new $supported[$class](
                $app['profiler.storage.dsn'],
                $app['profiler.storage.username'],
                $app['profiler.storage.password'],
                $app['profiler.storage.lifetime']
            );
        });

        $app['profiler_listener'] = $app->share(function () use ($app) {
            return new $app['profiler_listener.class'](
                $app['profiler'],
                $app['profiler.request_matcher'],
                $app['profiler_listener.only_exceptions'],
                $app['profiler_listener.only_master_requests']
            );
        });

        $app['profiler.request_matcher'] = $app->share(function () use ($app) {
            if (!$app['profiler.request_matcher.path'] &&
                !$app['profiler.request_matcher.host'] &&
                !$app['profiler.request_matcher.methods'] &&
                !$app['profiler.request_matcher.ip']) {
                return null;
            }

            return new $app['profiler.request_matcher.class'](
                $app['profiler.request_matcher.path'],
                $app['profiler.request_matcher.host'],
                $app['profiler.request_matcher.methods'],
                $app['profiler.request_matcher.ip']
            );
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['profiler_listener']);
        $app['dispatcher']->addSubscriber($app['debug.deprecation_logger_listener']);
        if (isset($app['data_collector.request']) && isset($app['data_collector.registry']['request'])) {
            $app['dispatcher']->addSubscriber($app['data_collector.request']);
        }
        if (isset($app['data_collector.router']) && isset($app['data_collector.registry']['router'])) {
            $app['dispatcher']->addSubscriber($app['data_collector.router']);
        }
    }
}
