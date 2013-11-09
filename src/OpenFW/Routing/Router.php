<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 12:31 PM
 */

namespace OpenFW\Routing;


use OpenFW\Constants;
use OpenFW\Routing\Exception\ControllerNotFoundException;
use OpenFW\Traits\ContainerAware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    use ContainerAware;

    const ALL_METHODS = 0;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var null|Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $routes = [];

    public function __construct()
    {
        $this->request = Request::createFromGlobals();

        $cacheFile = $this->getCacheFile();

        if(is_file($cacheFile)) {
            $this->routes = unserialize(file_get_contents($this->getCacheFile()));
        }
    }

    /**
     * @return array|mixed
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return Response
     * @throws Exception\ControllerNotFoundException
     */
    public function dispatch()
    {
        $method = $this->request->getMethod();

        $controller = false;
        $parameters = [];

        // try method defined
        if(isset($this->routes[$method])) {
            /** @var $route Route */
            foreach($this->routes[$method] as $route) {
                if($route->match($this->request->getPathInfo(), $parameters)) {
                    $controller = $route->getController();
                    break;
                }
            }
        }

        // try to find any compatible
        if(false === $controller && isset($this->routes[self::ALL_METHODS])) {
            /** @var $route Route */
            foreach($this->routes[self::ALL_METHODS] as $route) {
                if($route->match($this->request->getPathInfo(), $parameters)) {
                    $controller = $route->getController();
                    break;
                }
            }
        }

        if(false === $controller) {
            throw new ControllerNotFoundException("Controller not found.");
        }

        return $this->callController($controller, $parameters);
    }

    /**
     * @param string $name
     * @param string $expression
     * @param callable $controller
     * @param int|string $method
     * @return Route
     */
    public function addRoute($name, $expression, callable $controller, $method = self::ALL_METHODS)
    {
        // first case happens only after cached version loaded
        if(isset($this->routes[$method], $this->routes[$method][$name])
            && !is_callable($this->routes[$method][$name]->getController())) {
            $this->routes[$method][$name]->setController($controller);

            return $this->routes[$method][$name];
        } else {
            $route = new Route($expression, $controller);

            $this->routes[$method] = isset($this->routes[$method]) ? $this->routes[$method] : [];

            $this->routes[$method][$name] = $route;

            return $route;
        }
    }

    /**
     * @param string $url
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public static function createRedirectResponse($url, $status = 302, array $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public static function createJsonResponse($data, $status = 200, array $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function createResponse($content, $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * @throws \RuntimeException
     */
    public function __destruct()
    {
        if(!(is_file($this->getCacheFile())
            && false === $this->container[Constants::CONFIGURATION_CONTAINER]['debug'])) {
            if(!file_put_contents($this->getCacheFile(), serialize($this->routes), LOCK_EX | LOCK_NB)) {
                throw new \RuntimeException("Unable to persist router cache.");
            }
        }
    }

    /**
     * @param callable $controller
     * @param array $parameters
     * @return Response
     * @throws \RuntimeException
     */
    protected function callController(callable $controller, array & $parameters)
    {
        $result = call_user_func_array($controller, $parameters);

        if(!($result instanceof Response)) {
            throw new \RuntimeException("Response instance should be returned from controller.");
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getCacheFile()
    {
        return sprintf("%s/__router_routes_cache.tmp", Constants::getResolvedPath(Constants::CACHE_DIR));
    }
}