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
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Dispatcher
{
    use ContainerAware;

    const CLI_EXCEPTION_TPL = "\n\033[0;31m%s\033[0m\n\n\033[0;33m%s\033[0m\n\n";

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
        if(true === $this->debug) {
            if(class_exists('Whoops\Run')) {
                $run = new Run();

                // Set page handler for output prettified exception in non CLI environment
                $pageHandler = new PrettyPageHandler();
                $run->pushHandler($pageHandler);

                // Set handler that would trigger runtime exception event
                $run->pushHandler(
                    new CallbackHandler(function (\Exception $exception) {
                        $this->container[Constants::EVENTS_SERVICE]
                            ->trigger(Constants::ON_RUNTIME_EXCEPTION_EVENT, [$exception, $this->container]);
                    })
                );

                // Set exception output handler when CLI environment detected.
                // Do this because page handler does not output anything
                if($this->container[Constants::ENVIRONMENT_SERVICE]->isCli()) {
                    $run->pushHandler(
                        new CallbackHandler(function (\Exception $exception) {
                            @ob_end_clean();
                            @ob_implicit_flush(1);
                            exit(sprintf(
                                self::CLI_EXCEPTION_TPL,
                                $exception->getMessage(), $this->prettifyException($exception)
                            ));
                        })
                    );
                }

                // register Whoops\Run exceptions handler
                $run->register();
            } else {
                set_exception_handler([$this, 'exceptionHandlerDebug']);
                register_shutdown_function(array($this, 'shutdownHandler'));
            }
        } else {
            set_exception_handler([$this, 'exceptionHandlerLive']);
            register_shutdown_function(array($this, 'shutdownHandler'));
        }

        // delete whoops error handler first
        restore_error_handler();
        set_error_handler([$this, 'errorHandler']);
    }

    /**
     * @param int $errSeverity
     * @param string $errMsg
     * @param string $errFile
     * @param int $errLine
     * @param array $errContext
     * @throws Error\WarningException
     * @throws Error\CompileErrorException
     * @throws Error\ErrorException
     * @throws Error\CoreErrorException
     * @throws Error\UserNoticeException
     * @throws Error\CoreWarningException
     * @throws Error\UserErrorException
     * @throws Error\UserWarningException
     * @throws Error\ParseException
     * @throws Error\NoticeException
     * @throws Error\RecoverableErrorException
     * @throws Error\UserDeprecatedException
     * @throws Error\DeprecatedException
     * @throws Error\StrictException
     */
    public function errorHandler($errSeverity, $errMsg, $errFile, $errLine, array $errContext = [])
    {
        // check if error severity exists
        // in reporting level
        if(error_reporting() & $errSeverity) {
            switch($errSeverity) {
                case E_ERROR:
                    throw new Error\ErrorException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_WARNING:
                    throw new Error\WarningException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_PARSE:
                    throw new Error\ParseException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_NOTICE:
                    throw new Error\NoticeException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_CORE_ERROR:
                    throw new Error\CoreErrorException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_CORE_WARNING:
                    throw new Error\CoreWarningException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_COMPILE_ERROR:
                    throw new Error\CompileErrorException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_COMPILE_WARNING:
                    throw new Error\CoreWarningException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_USER_ERROR:
                    throw new Error\UserErrorException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_USER_WARNING:
                    throw new Error\UserWarningException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_USER_NOTICE:
                    throw new Error\UserNoticeException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_STRICT:
                    throw new Error\StrictException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_RECOVERABLE_ERROR:
                    throw new Error\RecoverableErrorException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_DEPRECATED:
                    throw new Error\DeprecatedException($errMsg, 0, $errSeverity, $errFile, $errLine);
                case E_USER_DEPRECATED:
                    throw new Error\UserDeprecatedException($errMsg, 0, $errSeverity, $errFile, $errLine);
            }
        }
    }

    /**
     * @return void
     */
    public function shutdownHandler()
    {
        if($error = error_get_last()) {

            if(
            in_array(
                $error['type'],
                array(
                     E_ERROR,
                     E_PARSE,
                     E_CORE_ERROR,
                     E_CORE_WARNING,
                     E_COMPILE_ERROR,
                     E_COMPILE_WARNING
                )
            )
            ) {
                $this->errorHandler(
                    $error['type'],
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
            }
        }
    }

    /**
     * @param \Exception $e
     */
    public function exceptionHandlerLive(\Exception $e)
    {
        $this->container[Constants::EVENTS_SERVICE]
            ->trigger(Constants::ON_RUNTIME_EXCEPTION_EVENT, [$e, $this->container]);

        $header = "HTTP/1.0 500 Internal Server Error";

        // on 404 error
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
        $this->container[Constants::EVENTS_SERVICE]
            ->trigger(Constants::ON_RUNTIME_EXCEPTION_EVENT, [$e, $this->container]);

        $prettyException = $this->prettifyException($e);
        $isCli = $this->container[Constants::ENVIRONMENT_SERVICE]->isCli();

        @ob_end_clean();
        @ob_implicit_flush(1);
        echo
        $isCli
            ? "[In order to see prettier exceptions- require 'filp/whoops' using composer]\n\n"
            : "<strong>
                    [In order to see prettier exceptions- require 'filp/whoops' using composer]
                   </strong>
                   <br/><br/>";
        exit($isCli ? $prettyException : nl2br($prettyException));
    }

    /**
     * @param \Exception $exception
     * @return string
     */
    public function prettifyException(\Exception $exception)
    {
        // these are our templates
        $traceline = "#%s %s(%s): %s(%s)";
        $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

        // alter your trace as you please, here
        $trace = $exception->getTrace();
        foreach($trace as $key => $stackPoint) {
            // I'm converting arguments to their type
            // (prevents passwords from ever getting logged as anything other than 'string')
            $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
        }

        // build your tracelines
        $result = array();

        foreach($trace as $key => $stackPoint) {
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
