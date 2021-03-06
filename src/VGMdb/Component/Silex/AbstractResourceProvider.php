<?php

namespace VGMdb\Component\Silex;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

/**
 * Base implementation for resource providers, based on the Symfony2 Bundle system.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractResourceProvider implements ResourceProviderInterface
{
    protected $name;
    protected $reflected;

    /**
     * Builds the resource.
     *
     * It is only ever called once when the cache is empty.
     */
    public function build()
    {
    }

    /**
     * Checks if the provider is enabled.
     *
     * @return Boolean
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Returns the parent provider name.
     *
     * @return string The parent name it overrides or null if no parent
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Returns the resource name (the namespace segment).
     *
     * @return string The resource name
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = $this->getNamespace();
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name :  substr($name, $pos + 1);
    }

    /**
     * Gets the resource namespace.
     *
     * @return string The resource namespace
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return $this->reflected->getNamespaceName();
    }

    /**
     * Gets the resource directory path.
     *
     * @return string The resource absolute path
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }

    /**
     * Finds and registers Commands.
     *
     * Override this method if your provider commands do not follow the conventions:
     *
     * * Commands are in the 'Command' sub-directory
     * * Commands extend Symfony\Component\Console\Command\Command
     *
     * @param Application $application An Application instance
     */
    public function registerCommands(Application $application)
    {
        if (!is_dir($dir = $this->getPath().'/Command')) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = $this->getNamespace().'\\Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\'.strtr($relativePath, '/', '\\');
            }
            $r = new \ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
                $application->add($r->newInstance());
            }
        }
    }
}
