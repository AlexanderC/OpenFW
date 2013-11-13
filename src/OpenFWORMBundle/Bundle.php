<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/13/13
 * @time 10:13 AM
 */

namespace OpenFWORMBundle;


use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use OpenFW\Constants;
use OpenFW\Exception\ConfigurationException;
use OpenFW\Traits\Bundle as MainBundle;
use OpenFW\Traits\ConfigurableBundle;
use OpenFW\Traits\ContainerAware;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Bundle
{
    use MainBundle;
    use ContainerAware;
    use ConfigurableBundle;

    const ORM_CLASS = "Doctrine\\ORM\\EntityManager";
    const ENTITY_PATH_TPL = "%s/Entity";
    const PROXY_PATH_TPL = "%s/OpenFWORMBundle/Proxies";

    /**
     * @var array
     */
    protected $managers = [];

    /**
     * This would be used basically to
     * skip the step with creating class
     * reflection, due to performance reason
     *
     * @return string
     */
    public function getDirectory()
    {
        return __DIR__;
    }

    /**
     * @throws \RuntimeException
     */
    public function checkEnvironment()
    {
        if(!class_exists(self::ORM_CLASS)) {
            throw new \RuntimeException("Unable to find EntityManager class. Please install 'doctrine/orm'.");
        }

        if(!is_dir($this->getEntitiesPath())) {
            throw new ConfigurationException("Missing entities path.");
        }

        if(!isset($this->config['connections'])
            || !is_array($this->config['connections'])
            || empty($this->config['connections'])) {
            throw new ConfigurationException("Unable to find 'connections' section or wrong format provided.");
        }
    }

    /**
     * @return void
     */
    public function initLazy()
    {
        $this->init();
    }

    /**
     * @throws \OpenFW\Exception\ConfigurationException
     */
    public function init()
    {
        static $once = false;

        if(true === $once) {
            return;
        } else {
            $once = true;
        }

        // get config items
        $isDev = $this->container[Constants::CONFIGURATION_CONTAINER]['debug'];
        $paths = (isset($this->config['entityPaths']) && is_array($this->config['entityPaths']))
                    ? array_merge($this->config['entityPaths'], [$this->getEntitiesPath()])
                    : [$this->getEntitiesPath()];


        // create entity managers
        foreach($this->config['connections'] as $name => $config) {
            if(!isset($config['parameters'])) {
                throw new ConfigurationException("Unable to find 'parameters' section in {$name}.");
            }

            $cacheDir = Constants::getResolvedPath(Constants::CACHE_DIR);

            // create configuration
            $setup = Setup::createAnnotationMetadataConfiguration(
                $paths, // entities paths
                $isDev, // dev mode flag
                sprintf(self::PROXY_PATH_TPL, $cacheDir), // proxy cache path
                (isset($config['cache']) && $config['cache'] instanceof Cache) // cache instance
                    ? $config['cache'] // set setup or
                    : (function_exists('apc_add') ? new ApcCache() : new PhpFileCache($cacheDir)) // set apc or file
            );

            // add manager instance
            $this->managers[$name] = EntityManager::create($config['parameters'], $setup);
        }
    }

    /**
     * @param string $name
     * @return \Doctrine\ORM\EntityManager
     * @throws \RuntimeException
     */
    public function getManager($name)
    {
        if(!$this->isManager($name)) {
            throw new \RuntimeException("Entity Manager {$name} des not exists.");
        }

        return $this->managers[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isManager($name)
    {
        return isset($this->managers[$name]);
    }

    /**
     * @return array
     */
    public function getManagers()
    {
        return $this->managers;
    }

    /**
     * @return string
     */
    protected function getEntitiesPath()
    {
        return sprintf(self::ENTITY_PATH_TPL, __DIR__);
    }
} 