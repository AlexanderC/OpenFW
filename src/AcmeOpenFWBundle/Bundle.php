<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:07 PM
 */

namespace AcmeOpenFWBundle;


use OpenFW\Constants;
use OpenFW\Routing\Route;
use OpenFW\Routing\Router;
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

    public function init()
    {
        /** @var Router $router */
        $router = $this->container[Constants::ROUTING_SERVICE];

        $router->addRoute("hello_route", "/{name}", function($name) {
            return Router::createResponse("Hello {$name}");
        })->setValidator('name', '\w+');

        $router->addRoute("home", "/", function() {
            return Router::createRedirectResponse(
                $this->container['router']->getRoute('hello_route')->generate(['name' => 'Alex'])
            );
        });
    }
} 