<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:07 PM
 */

namespace OpenFWLoggerBundle;


use OpenFW\Constants;
use OpenFW\Events\Event;
use OpenFW\Events\Eventer;
use OpenFW\Events\Matchers\BinaryMatcher;
use OpenFW\Exception\ConfigurationException;
use OpenFW\Traits\Bundle as MainBundle;
use OpenFW\Traits\ConfigurableBundle;
use OpenFW\Traits\ContainerAware;
use Monolog\Logger;

class Bundle
{
    use MainBundle;
    use ContainerAware;
    use ConfigurableBundle;

    /**
     * @var array
     */
    protected $loggers = [];

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
        if(!class_exists("Monolog\\Logger")) {
            throw new \RuntimeException("Unable to find logger class. Please install 'monolog/monolog'.");
        }

        if(!isset($this->config['channels']) || !is_array($this->config['channels'])) {
            throw new ConfigurationException("Missing 'channels' section or wrong format.");
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

        foreach($this->config['channels'] as $channel => $handlers) {
            $this->loggers[$channel] = new Logger($channel);

            if(!is_array($handlers)) {
                throw new ConfigurationException("Channel handlers should be an array.");
            }

            foreach($handlers as $handler) {
                $this->loggers[$channel]->pushHandler($handler);
            }
        }

        /** @var $eventer Eventer */
        $eventer = $this->container[Constants::EVENTS_SERVICE];

        $eventer->addListener(new BinaryMatcher(Constants::ON_RUNTIME_EXCEPTION_EVENT),
            function(Event $event) {
                $exception = $event->getData()[0];

                $method = 'addError';

                if($exception instanceof \ErrorException) {
                    $class = get_class($exception);

                    if(false !== stripos($class, 'compileError') || false !== stripos($class, 'coreError')) {
                        $method = 'addCritical';
                    } elseif(false !== stripos($class, 'warning')) {
                        $method = 'addWarning';
                    } elseif(false !== stripos($class, 'notice')) {
                        $method = 'addNotice';
                    } elseif(false !== stripos($class, 'deprecated') || false !== stripos($class, 'strict')) {
                        $method = 'addAlert';
                    }
                }

                /** @var $logger Logger */
                foreach($this->loggers as $logger) {
                    call_user_func([$logger, $method], $exception->getMessage(), $event->getData());
                }
            });
    }

    /**
     * @return array
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * @param string $name
     * @return Logger
     * @throws \OutOfBoundsException
     */
    public function getLogger($name)
    {
        if(!$this->isLogger($name)) {
            throw new \OutOfBoundsException("Logger channel {$name} does not exists.");
        }

        return $this->loggers[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isLogger($name)
    {
        return isset($this->loggers[$name]);
    }
} 