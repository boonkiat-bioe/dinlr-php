<?php
namespace Nava\Dinlr;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Nava\Dinlr\Auth\OAuth;
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ConfigException;

/**
 * OAuth-based client for Dinlr API
 */
class OAuthClient extends Client
{
    /**
     * @var OAuth
     */
    private $oauth;

    /**
     * @var OAuthConfig
     */
    protected $config;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Create a new Dinlr API OAuth client
     *
     * @param OAuthConfig|array $config Configuration options
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new OAuthConfig($config);
        }

        if (! $config instanceof OAuthConfig) {
            throw new ConfigException('Config must be an array or OAuthConfig instance');
        }

        $this->config = $config;

        // Initialize OAuth handler
        $this->oauth = new OAuth(
            $config->getClientId(),
            $config->getClientSecret(),
            $config->getRedirectUri(),
            $config->getApiUrl()
        );

        // Initialize HTTP client (without access token at first)
        $this->initHttpClient();
    }

    /**
     * Initialize HTTP client with current configuration
     *
     * @return void
     */
    private function initHttpClient(): void
    {
        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];

        // Add authorization header if access token is set
        if ($this->config instanceof OAuthConfig && $this->config->hasAccessToken()) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $this->httpClient = new HttpClient([
            'base_uri'    => $this->config->getApiUrl(),
            'headers'     => $headers,
            'http_errors' => false,
        ]);
    }

    /**
     * Make a request to the Dinlr API with OAuth authentication
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @return array Response data
     * @throws ApiException
     */
    public function request(string $method, string $endpoint, array $params = []): array
    {
        // Check if we need to refresh the token
        if ($this->config instanceof OAuthConfig &&
            $this->config->hasAccessToken() &&
            $this->config->isAccessTokenExpired() &&
            $this->config->hasRefreshToken()) {
            $this->refreshAccessToken();
        }

        try {
            // Make the request
            $options = [];

            if (! empty($params)) {
                if ('GET' === $method) {
                    $options['query'] = $params;
                } else {
                    $options['json'] = $params;
                }
            }

            $response = $this->httpClient->request($method, $endpoint, $options);
            $body     = $response->getBody()->getContents();
            $data     = json_decode($body, true);

            // Handle API errors
            if ($response->getStatusCode() >= 400) {
                throw new ApiException(
                    $data['message'] ?? 'API error',
                    $response->getStatusCode(),
                    null,
                    [
                        'endpoint'      => $endpoint,
                        'method'        => $method,
                        'response_data' => $data,
                    ]
                );
            }

            return $data;
        } catch (RequestException $e) {
            throw new ApiException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get the authorization URL
     *
     * @param string $state Random state parameter to prevent CSRF attacks
     * @return string The authorization URL
     */
    public function getAuthorizationUrl(string $state): string
    {
        return $this->oauth->getAuthorizationUrl($state);
    }

    /**
     * Handle the authorization callback
     *
     * @param array $params Query parameters from the callback
     * @param string $expectedState The state parameter that was sent in the authorization request
     * @return array Validated data with 'code' and 'restaurant_id'
     * @throws ApiException If validation fails
     */
    public function handleCallback(array $params, string $expectedState): array
    {
        return $this->oauth->validateCallback($params, $expectedState);
    }

    /**
     * Exchange the authorization code for an access token
     *
     * @param string $code Authorization code
     * @param string $restaurantId Restaurant ID
     * @return array Token data
     * @throws ApiException
     */
    public function getAccessToken(string $code, string $restaurantId): array
    {
        // Get the access token
        $tokenData = $this->oauth->getAccessToken($code, $restaurantId);

        // Update the configuration with the token data
        if ($this->config instanceof OAuthConfig) {
            $this->config->updateFromTokenResponse($tokenData);
            $this->initHttpClient(); // Reinitialize HTTP client with the new token
        }

        return $tokenData;
    }

    /**
     * Refresh the access token
     *
     * @return array New token data
     * @throws ApiException
     * @throws ConfigException
     */
    public function refreshAccessToken(): array
    {
        if (! $this->config instanceof OAuthConfig) {
            throw new ConfigException('Config must be an OAuthConfig instance to refresh access token');
        }

        if (! $this->config->hasRefreshToken()) {
            throw new ConfigException('Refresh token is not set');
        }

        if (! $this->config->getRestaurantId()) {
            throw new ConfigException('Restaurant ID is not set');
        }

        // Refresh the token
        $tokenData = $this->oauth->refreshAccessToken(
            $this->config->getRefreshToken(),
            $this->config->getRestaurantId()
        );

        // Update the configuration with the new token data
        $this->config->updateFromTokenResponse($tokenData);
        $this->initHttpClient(); // Reinitialize HTTP client with the new token

        return $tokenData;
    }

    /**
     * Set the access token and update the HTTP client
     *
     * @param string $accessToken Access token
     * @return self
     */
    public function setAccessToken(string $accessToken): self
    {
        if ($this->config instanceof OAuthConfig) {
            $this->config->setAccessToken($accessToken);
            $this->initHttpClient();
        }

        return $this;
    }

    /**
     * Get the OAuth handler
     *
     * @return OAuth
     */
    public function getOAuth(): OAuth
    {
        return $this->oauth;
    }
}
