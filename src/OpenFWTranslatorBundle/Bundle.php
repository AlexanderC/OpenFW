<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:07 PM
 */

namespace OpenFWTranslatorBundle;


use OpenFW\Configuration\RawConfigurator;
use OpenFW\Exception\ConfigurationException;
use OpenFW\Traits\Bundle as MainBundle;
use OpenFW\Traits\ConfigurableBundle;
use OpenFW\Traits\ContainerAware;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

class Bundle
{
    use MainBundle;
    use ContainerAware;
    use ConfigurableBundle;

    const TRANSLATOR_CLASS = "Symfony\\Component\\Translation\\Translator";
    const TRANSLATIONS_PATH_TPL = "%s/translations";

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

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
        if(!class_exists(self::TRANSLATOR_CLASS)) {
            throw new \RuntimeException("Unable to find translator class. Please install 'symfony/translation'.");
        }

        if(!isset($this->config['fallback'])) {
            throw new ConfigurationException("Missing 'fallback' section.");
        }

        if(!is_dir($this->getTranslationsPath())) {
            throw new ConfigurationException("Unable to find translations path.");
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

        $this->translator = new Translator(null, new MessageSelector());

        $this->translator->setFallbackLocale($this->config['fallback']);

        $this->translator->addLoader('array', new ArrayLoader());

        $configurator = RawConfigurator::create($this->getTranslationsPath());
        $configurator->parse();

        $config = $configurator->getConfig();

        // load locales from config
        if(is_array($config)) {
            foreach($config as $locale => $dictionary) {
                $this->translator->addResource('array', $dictionary, $locale);
            }
        }
    }

    /**
     * @param string $id
     * @param string|null $locale
     * @return string
     */
    public function trans($id, $locale = null)
    {
        return $this->translator->trans($id, [], 'messages', $locale ? : $this->config['fallback']);
    }

    /**
     * @param string $id
     * @param int $number
     * @param null|string $locale
     * @return string
     */
    public function transChoice($id, $number, $locale = null)
    {
        return $this->translator->transChoice($id, $number, [], 'messages', $locale ? : $this->config['fallback']);
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @throws \RuntimeException
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if(method_exists(self::TRANSLATOR_CLASS, $method)) {
            return call_user_func_array([$this->translator, $method], $arguments);
        }

        throw new \RuntimeException("Method {$method} does not exists.");
    }

    /**
     * @return string
     */
    protected function getTranslationsPath()
    {
        return sprintf(self::TRANSLATIONS_PATH_TPL, __DIR__);
    }
} 