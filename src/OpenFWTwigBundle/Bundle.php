<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:07 PM
 */

namespace OpenFWTwigBundle;


use OpenFW\Constants;
use OpenFW\Exception\ConfigurationException;
use OpenFW\Traits\Bundle as MainBundle;
use OpenFW\Traits\ContainerAware;

class Bundle
{
    use MainBundle;
    use ContainerAware;

    const TWIG_CLASS = "Twig_Environment";
    const TEMPLATES_PATH_TPL = "%s/templates";

    /**
     * @var \Twig_Environment
     */
    protected $twig;

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
     * @throws ConfigurationException
     */
    public function checkEnvironment()
    {
        if(!class_exists(self::TWIG_CLASS)) {
            throw new \RuntimeException("Unable to find Twig class. Please install 'twig/twig'.");
        }

        if(!is_dir($this->getTemplatesPath())) {
            throw new ConfigurationException("Missing templates path.");
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
     * @return void
     */
    public function init()
    {
        static $once = false;

        if(true === $once) {
            return;
        } else {
            $once = true;
        }

        $loader = new \Twig_Loader_Filesystem($this->getTemplatesPath());
        $options = [
            'cache' => Constants::getResolvedPath(Constants::CACHE_DIR)
        ];

        $this->twig = new \Twig_Environment($loader, $options);
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($method, array $arguments)
    {
        if(method_exists(self::TWIG_CLASS, $method)) {
            return call_user_func_array([$this->twig, $method], $arguments);
        }

        throw new \RuntimeException("Method {$method} does not exists.");
    }

    /**
     * @return string
     */
    protected function getTemplatesPath()
    {
        return sprintf(self::TEMPLATES_PATH_TPL, __DIR__);
    }
} 