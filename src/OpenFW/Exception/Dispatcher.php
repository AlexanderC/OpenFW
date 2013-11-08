<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 4:33 PM
 */

namespace OpenFW\Exception;

use OpenFW\Constants;
use OpenFW\Routing\Exception\ControllerNotFoundException;
use OpenFW\Traits\ContainerAware;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Dispatcher
{
    use ContainerAware;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * @return void
     */
    public function register()
    {
        set_error_handler([$this, 'errorHandler']);

        if(true === $this->debug) {
            if(class_exists('Whoops\Run')) {
                $pageHandler = new PrettyPageHandler();
                $run = new Run();

                $run->pushHandler($pageHandler);
                $run->register();
            } else {
                set_exception_handler([$this, 'exceptionHandlerDebug']);
            }
        } else {
            set_exception_handler([$this, 'exceptionHandlerLive']);
        }
    }

    /**
     * @param string $code
     * @param int $message
     * @throws \RuntimeException
     */
    public function errorHandler($code, $message)
    {
        throw new \RuntimeException($message, $code);
    }

    /**
     * @param \Exception $e
     */
    public function exceptionHandlerLive(\Exception $e)
    {
        $header = "HTTP/1.0 500 Internal Server Error";

        if($e instanceof ControllerNotFoundException) {
            $header = "HTTP/1.0 404 Not Found";
        }

        @ob_end_clean();
        @ob_implicit_flush(1);
        header($header);
        exit;
    }

    /**
     * @param \Exception $e
     */
    public function exceptionHandlerDebug(\Exception $e)
    {
        $prettyException = $this->pretifyException($e);
        $isCli = $this->container[Constants::ENVIRONMENT_SERVICE]->isCli();

        @ob_end_clean();
        @ob_implicit_flush(1);
        echo
            $isCli
                ? "[In order to see prettier exceptions- require 'filp/whoops' using composer]\n\n"
                : "<strong>
                    [In order to see prettier exceptions- require 'filp/whoops' using composer]
                   </strong>
                   <br/><br/>"
        ;
        exit($isCli ? $prettyException : nl2br($prettyException));
    }

    /**
     * @param \Exception $exception
     * @return string
     */
    protected function pretifyException(\Exception $exception)
    {
        // these are our templates
        $traceline = "#%s %s(%s): %s(%s)";
        $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

        // alter your trace as you please, here
        $trace = $exception->getTrace();
        foreach ($trace as $key => $stackPoint) {
            // I'm converting arguments to their type
            // (prevents passwords from ever getting logged as anything other than 'string')
            $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
        }

        // build your tracelines
        $result = array();

        foreach ($trace as $key => $stackPoint) {
            $stackPoint['file'] = isset($stackPoint['file']) ? $stackPoint['file'] : "Unavailable";
            $stackPoint['line'] = isset($stackPoint['line']) ? $stackPoint['line'] : "NaN";
            $stackPoint['function'] = isset($stackPoint['function']) ? $stackPoint['function'] : "Unavailable";
            $stackPoint['args'] = isset($stackPoint['args']) ? $stackPoint['args'] : [];

            $result[] = sprintf(
                $traceline,
                $key,
                $stackPoint['file'],
                $stackPoint['line'],
                $stackPoint['function'],
                implode(', ', $stackPoint['args'])
            );
        }
        // trace always ends with {main}
        $result[] = '#' . ++$key . ' {main}';

        // write trace lines into main template
        $msg = sprintf(
            $msg,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            implode("\n", $result),
            $exception->getFile(),
            $exception->getLine()
        );

        return $msg;
    }
} 