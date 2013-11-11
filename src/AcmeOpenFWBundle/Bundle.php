<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:07 PM
 */

namespace AcmeOpenFWBundle;


use OpenFW\Constants;
use OpenFW\Routing\Router;
use OpenFW\Routing\Validator\RegexValidator;
use OpenFW\Traits\Bundle as MainBundle;
use OpenFW\Traits\ConfigurableBundle;
use OpenFW\Traits\ContainerAware;

class Bundle
{
    use MainBundle;
    use ContainerAware;
    use ConfigurableBundle;

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
     * Check if all the things were correctly
     * installed/configured
     *
     * @return void
     */
    public function checkEnvironment()
    {
        // check environment
    }

    /**
     * Called when preloading a lazy bundle
     * instead of init method- that would
     * be called only when accessing bundle
     * as a service (from container)
     *
     * @return void
     */
    public function initLazy()
    {
        /** @var Router $router */
        $router = $this->container['router'];

        $router->addRoute(
            "hello_route", "/{name}", function ($name) {
                return Router::createResponse("Hello {$name}");
            }
        )->addValidator('name', new RegexValidator('\w+'));

        $router->addRoute(
            "home", "/", function () use ($router) {
                return Router::createRedirectResponse(
                    $router->getRoute('hello_route')->generate(['name' => 'Alex'])
                );
            }
        );

        // case controller not found (404 error page)
        $router->setDefaultController(
            function () {
                return Router::createResponse(
                    "<h1>Page Not Found</h1>"
                );
            }
        );
    }

    /**
     * This is the place where heavy operations
     * should be executed
     *
     * @return void
     */
    public function init()
    {
        // do some heavy stuff
    }
} 