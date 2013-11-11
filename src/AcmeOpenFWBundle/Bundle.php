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
use OpenFW\Traits\ContainerAware;

class Bundle
{
    use MainBundle;
    use ContainerAware;

    public function checkEnvironment()
    {
        // check environment
    }

    public function initLazy()
    {
        /** @var Router $router */
        $router = $this->container['router'];

        $router->addRoute("hello_route", "/{name}", function($name) {
            return Router::createResponse("Hello {$name}");
        })->addValidator('name', new RegexValidator('\w+'));

        $router->addRoute("home", "/", function() use ($router) {
            return Router::createRedirectResponse(
                $router->getRoute('hello_route')->generate(['name' => 'Alex'])
            );
        });
    }

    public function init()
    {
        // do some heavy stuff
    }
} 