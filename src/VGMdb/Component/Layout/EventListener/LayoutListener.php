<?php

namespace VGMdb\Component\Layout\EventListener;

use VGMdb\Application;
use VGMdb\Component\Layout\Layout;
use VGMdb\Component\View\ViewInterface;
use VGMdb\Component\Routing\Translation\TranslationRouteLoader;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Applies a layout wrapper to HTML responses.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LayoutListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'html') {
            return;
        }

        $result = $event->getControllerResult();
        if (!$result instanceof ViewInterface) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');
        if (false !== $pos = strpos($route, TranslationRouteLoader::ROUTING_PREFIX)) {
            $route = substr($route, $pos + strlen(TranslationRouteLoader::ROUTING_PREFIX));
        }

        $layoutName = $event->getRequest()->attributes->get('_layout');
        $layouts = $this->app['layout.config'];
        $replacements = array(
            '%locale%' => $this->app['request_context']->getLanguage(),
            '%app%' => $this->app['request_context']->getAppName(),
            '%client%' => $this->app['request_context']->getClient()
        );

        $config = $layoutName && isset($layouts[$layoutName])
            ? $this->doReplacements($layouts[$layoutName], $replacements)
            : array();

        if (!isset($config['layout'])) {
            $config['layout'] = array();
        }

        if (!isset($config['layout']['template'])) {
            $config['layout']['template'] = $layoutName;
        }

        $layoutData = $this->doReplacements($this->app['layout.default_data'], $replacements);

        $layout = new Layout($this->app, $config, $layoutData);
        $result = $layout->wrap($result);

        $event->setControllerResult($result);
    }

    protected function doReplacements($value, array $replacements)
    {
        if (!$replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v, $replacements);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $replacements);
        }

        return $value;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array(array('onKernelView', -32)),
        );
    }
}
