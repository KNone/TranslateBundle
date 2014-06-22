<?php

namespace KNone\TranslateBundle\Provider;

use KNone\TranslateBundle\Provider\ProviderInterface;
use GuzzleHttp\Client;
use KNone\TranslateBundle\Exception\InvalidConfigurationException;

/**
 * Class ProviderFactory
 * @package KNone\TranslateBundle\Provider
 * @author Krasnoyartsev Nikita <i@knone.ru>
 */
class ProviderFactory
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $providers = array(
        'google_web' => array(
            'class' => 'KNone\TranslateBundle\Provider\GoogleWebProvider'
        ),
        'yandex_api' => array(
            'class' => 'KNone\TranslateBundle\Provider\YandexApiProvider',
            'key_parameter' => 'yandex_api_key'
        )
    );

    /**
     * @var ProviderInterface
     */
    protected $instance = null;

    /**
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @return ProviderInterface
     */
    public function getTranslator()
    {
        if (!$this->instance) {
            $class = $this->getClass();
            $key = $this->getKey();
            if ($key) {
                $this->instance = new $class($this->client, $key);
            } else {
                $this->instance = new $class($this->client);
            }
        }

        return $this->instance;
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return $this->providers[$this->config['default_provider']]['class'];
    }

    /**
     * @return bool|string
     * @throws InvalidConfigurationException
     */
    protected function getKey()
    {
        $providerDescription = $this->providers[$this->config['default_provider']];
        if (isset($providerDescription['key_parameter'])) {
            if (!isset($this->config[$providerDescription['key_parameter']])) {
                throw new InvalidConfigurationException(
                    'The child node "' . $providerDescription['key_parameter'] . '" at path "k_none_translate" must be configured.'
                );
            }
            return $this->config[$providerDescription['key_parameter']];
        }

        return false;
    }
}