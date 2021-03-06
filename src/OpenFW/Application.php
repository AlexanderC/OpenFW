<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 11:17 AM
 */

namespace OpenFW;


use OpenFW\Bundles\Exception\BundleNotFoundException;
use OpenFW\Configuration\Configurator;
use OpenFW\Environment\Environment;
use OpenFW\Events\Eventer;
use OpenFW\Exception\ConfigurationException;
use OpenFW\Exception\Dispatcher;
use OpenFW\Filesystem\RegexWalker;
use OpenFW\Routing\Exception\ControllerNotFoundException;
use OpenFW\Routing\Router;
use OpenFW\Bundles\Manager as BundlesManager;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application
{
    /**
     * @var Environment\Environment
     */
    protected $env;

    /**
     * @var Routing\Router
     */
    protected $router;

    /**
     * @var Events\Eventer
     */
    protected $eventer;

    /**
     * @var Configuration\Configurator
     */
    protected $configurator;

    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * @var Bundles\Manager
     */
    protected $bundles;

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $console;

    /**
     * @return Application
     */
    public static function create()
    {
        $env = new Environment();
        $router = new Router();
        $eventer = new Eventer();
        $configurator = new Configurator($env->getEnvironment(), (new RegexWalker(
            Constants::getResolvedPath(Constants::CONFIG_DIR), Configurator::DEFAULT_IT_REGEX
        ))->iterator(RegexWalker::IT_FILES | RegexWalker::IT_LINKS));

        return new self($env, $router, $eventer, $configurator);
    }

    /**
     * @param Environment $env
     * @param Router $router
     * @param Eventer $eventer
     * @param Configurator $configurator
     * @throws ConfigurationException
     */
    public function __construct(
        Environment $env, Router $router,
        Eventer $eventer, Configurator $configurator
    ) {
        // register internals
        $this->env = $env;
        $this->router = $router;
        $this->eventer = $eventer;
        $this->configurator = $configurator;

        // parse configuration files
        $this->configurator->parse();

        // init DIC and register some events
        $this->initContainer();
        $this->registerEvents();

        // init exception dispatcher
        $exceptionDispatcher = new Dispatcher($this->container[Constants::CONFIGURATION_CONTAINER]['debug']);
        $exceptionDispatcher->setContainer($this->container);
        $exceptionDispatcher->register();

        // init console application only when CLI env
        if($this->env->isCli()) {
            $this->console = new ConsoleApplication(Constants::APP_NAME, Constants::APP_VERSION);
        }

        // init bundles manager and available bundles
        $this->bundles = new BundlesManager($this->container[Constants::CONFIGURATION_CONTAINER]['bundles']);
        $this->initBundles();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     * @throws \Exception
     * @throws Routing\Exception\ControllerNotFoundException
     */
    public function run()
    {
        $this->eventer->trigger(Constants::BEFORE_LOAD_EVENT, $this->container);

        if(!$this->env->isCli()) {
            $this->eventer->trigger(Constants::BEFORE_CONTROLLER_CALL_EVENT, $this->container);
            try {
                $result = $this->router->dispatch();
            } catch(ControllerNotFoundException $e) {
                $this->eventer->trigger(Constants::CONTROLLER_NOT_FOUND_EVENT, $this->container);
                throw $e;
            }
            $this->eventer->trigger(Constants::AFTER_CONTROLLER_CALL_EVENT, $this->container);

            return $result;
        } else {
            $this->console->run();
        }

        $this->eventer->trigger(Constants::AFTER_LOAD_EVENT, $this->container);
    }

    /**
     * @return void
     */
    protected function initContainer()
    {
        $this->container = new \Pimple();

        $this->container[Constants::ENVIRONMENT_SERVICE] = $this->env;
        $this->container[Constants::ROUTING_SERVICE] = $this->router;
        $this->container[Constants::EVENTS_SERVICE] = $this->eventer;
        $this->container[Constants::CONFIGURATION_SERVICE] = $this->configurator;
        $this->container[Constants::BUNDLES_SERVICE] = $this->bundles;
        $this->container[Constants::CONSOLE_SERVICE] = $this->console;

        $this->container[Constants::CONFIGURATION_CONTAINER] = $this->configurator->getConfig();
    }

    /**
     * @return void
     */
    protected function registerEvents()
    {
        $this->eventer->register(Constants::BEFORE_LOAD_EVENT);
        $this->eventer->register(Constants::AFTER_LOAD_EVENT);

        $this->eventer->register(Constants::BUNDLE_NOT_FOUND_EVENT);
        $this->eventer->register(Constants::BEFORE_BUNDLE_INIT_EVENT);
        $this->eventer->register(Constants::AFTER_BUNDLE_INIT_EVENT);
        $this->eventer->register(Constants::BUNDLE_ENVIRONMENT_CHECK_FAIL_EVENT);

        $this->eventer->register(Constants::CONTROLLER_NOT_FOUND_EVENT);
        $this->eventer->register(Constants::BEFORE_CONTROLLER_CALL_EVENT);
        $this->eventer->register(Constants::AFTER_CONTROLLER_CALL_EVENT);

        $this->eventer->register(Constants::ON_RUNTIME_EXCEPTION_EVENT);
    }

    /**
     * @throws \Exception
     * @throws Bundles\Exception\BundleNotFoundException
     */
    protected function initBundles()
    {
        foreach($this->bundles->getBundles() as $bundle) {
            $this->eventer->trigger(Constants::BEFORE_BUNDLE_INIT_EVENT, [$bundle, $this->container]);

            try {
                $instance = $this->bundles->createBundleInstance($bundle, $this->container);
            } catch(BundleNotFoundException $e) {
                $this->eventer->trigger(Constants::BUNDLE_NOT_FOUND_EVENT, [$bundle, $this->container]);
                throw $e;
            } catch(\Exception $e) {
                $this->eventer->trigger(
                    Constants::BUNDLE_ENVIRONMENT_CHECK_FAIL_EVENT, [$bundle, $this->container]
                );
                throw $e;
            }

            if(true === $bundle['lazy']) {
                $instance->initLazy();

                $this->container[$bundle['name']] = $this->container->share(
                    function ($container) use ($bundle, $instance) {
                        $instance->init();
                        $container[Constants::EVENTS_SERVICE]->trigger(
                            Constants::AFTER_BUNDLE_INIT_EVENT, [$bundle, $instance, $this->container]
                        );

                        return $instance;
                    }
                );
            } else {
                $instance->init();
                $this->eventer->trigger(Constants::AFTER_BUNDLE_INIT_EVENT, [$bundle, $instance, $this->container]);

                $this->container[$bundle['name']] = $instance;
            }
        }
    }

    /**
     * @return \OpenFW\Bundles\Manager
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * @return \OpenFW\Configuration\Configurator
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }

    /**
     * @return \Symfony\Component\Console\Application
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \OpenFW\Environment\Environment\Environment
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @return \OpenFW\Events\Eventer
     */
    public function getEventer()
    {
        return $this->eventer;
    }

    /**
     * @return \OpenFW\Routing\Router
     */
    public function getRouter()
    {
        return $this->router;
    }
}
