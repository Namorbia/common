<?php

namespace VGMdb\Component\View;

use VGMdb\Component\View\Logger\ViewLoggerInterface;
use VGMdb\Component\View\Engine\MustacheView;
use VGMdb\Component\View\Engine\SmartyView;

/**
 * View factory for creating views based on renderer type.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ViewFactory
{
    protected $logger;

    /**
     * Sets the default logger.
     *
     * @param ViewLoggerInterface $logger
     */
    public function setLogger(ViewLoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * View factory for creating new view instances based on renderer type.
     *
     * @param mixed $template
     * @param array $data
     * @param mixed $engine
     * @return ViewInterface
     */
    public function create($template, array $data = array(), $engine = null)
    {
        if ($template instanceof ViewInterface) {
            return $template;
        }

        if ($engine instanceof \Mustache_Engine) {
            return new MustacheView($template, $data, $engine, $this->logger);
        }

        if ($engine instanceof \Smarty) {
            return new SmartyView($template, $data, $engine, $this->logger);
        }

        return new View($template, $data, $engine, $this->logger);
    }
}
