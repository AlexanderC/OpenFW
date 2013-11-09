<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/9/13
 * @time 12:54 AM
 */

namespace OpenFW;


use Doctrine\Common\Cache\CacheProvider;

class Cache
{
    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $provider;

    /**
     * @param CacheProvider $provider
     */
    public function __construct(CacheProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return CacheProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param array $config
     * @return Cache
     */
    public static function create(array $config)
    {
        $reflection = new \ReflectionClass($config['class']);
        $provider = $reflection->newInstanceArgs($config['arguments']);

        return new self($provider);
    }
} 