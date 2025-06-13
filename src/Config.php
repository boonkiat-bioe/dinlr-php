<?php
namespace Nava\Dinlr;

use Nava\Dinlr\Exception\ConfigException;

/**
 * Configuration class for Dinlr API client
 */
class Config
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl = 'https://api.dinlr.com/v1';

    /**
     * @var string
     */
    private $authBaseUrl = 'https://backoffice.dinlr.com';

    /**
     * @var int
     */
    private $timeout = 30;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string|null
     */
    private $restaurantId;

    /**
     * Create a new configuration instance
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        if (isset($config['api_key'])) {
            $this->setApiKey($config['api_key']);
        }

        if (isset($config['api_url'])) {
            $this->setApiUrl($config['api_url']);
        }

        if (isset($config['auth_base_url'])) {
            $this->setAuthBaseUrl($config['auth_base_url']);
        }

        if (isset($config['timeout'])) {
            $this->setTimeout($config['timeout']);
        }

        if (isset($config['debug'])) {
            $this->setDebug($config['debug']);
        }

        if (isset($config['restaurant_id'])) {
            $this->setRestaurantId($config['restaurant_id']);
        }
    }

    /**
     * Set the API key
     *
     * @param string $apiKey
     * @return self
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Get the API key
     *
     * @return string
     * @throws ConfigException
     */
    public function getApiKey(): string
    {
        if (empty($this->apiKey)) {
            throw new ConfigException('API key is not set');
        }

        return $this->apiKey;
    }

    /**
     * Set the API URL
     *
     * @param string $apiUrl
     * @return self
     */
    public function setApiUrl(string $apiUrl): self
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        return $this;
    }

    /**
     * Get the API URL
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Set the Auth Base URL
     *
     * @param string $authBaseUrl
     * @return self
     */
    public function setAuthBaseUrl(string $authBaseUrl): self
    {
        $this->authBaseUrl = rtrim($authBaseUrl, '/');
        return $this;
    }

    /**
     * Get the Auth Base URL
     *
     * @return string
     */
    public function getAuthBaseUrl(): string
    {
        return $this->authBaseUrl;
    }

    /**
     * Set the request timeout
     *
     * @param int $timeout
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Get the request timeout
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set debug mode
     *
     * @param bool $debug
     * @return self
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Get debug mode
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Set the restaurant ID
     *
     * @param string $restaurantId
     * @return self
     */
    public function setRestaurantId(string $restaurantId): self
    {
        $this->restaurantId = $restaurantId;
        return $this;
    }

    /**
     * Get the restaurant ID
     *
     * @return string|null
     */
    public function getRestaurantId(): ?string
    {
        return $this->restaurantId;
    }
}
