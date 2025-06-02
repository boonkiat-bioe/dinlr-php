<?php
namespace Nava\Dinlr\Auth;

use GuzzleHttp\Client as HttpClient;
use Nava\Dinlr\Exception\ApiException;

/**
 * OAuth 2.0 authorization handler for Dinlr API
 */
class OAuth
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $apiUrl = 'https://api.dinlr.com/v1';

    /**
     * @var string
     */
    private $authorizationUrl = 'https://backoffice.dinlr.com/oauth/authorize';

    /**
     * Create a new OAuth handler instance
     *
     * @param string $clientId Client ID
     * @param string $clientSecret Client Secret
     * @param string $redirectUri Redirect URI
     * @param string|null $apiUrl API URL (optional)
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri, ?string $apiUrl = null)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri  = $redirectUri;

        if ($apiUrl) {
            $this->apiUrl = rtrim($apiUrl, '/');
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
        $params = [
            'client_id'    => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state'        => $state,
        ];

        return $this->authorizationUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange the authorization code for an access token and refresh token
     *
     * @param string $code Authorization code
     * @param string $restaurantId Restaurant ID
     * @return array Token data including access_token, refresh_token, expires_in
     * @throws ApiException
     */
    public function getAccessToken(string $code, string $restaurantId): array
    {
        $client = new HttpClient();

        $response = $client->post("{$this->apiUrl}/{$restaurantId}/oauth/token", [
            'form_params' => [
                'code'          => $code,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'authorization_code',
            ],
            'http_errors' => false,
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if ($response->getStatusCode() >= 400 || ! isset($data['access_token'])) {
            throw new ApiException(
                $data['error_description'] ?? $data['message'] ?? 'Failed to obtain access token',
                $response->getStatusCode()
            );
        }

        return $data;
    }

    /**
     * Refresh the access token using a refresh token
     *
     * @param string $refreshToken Refresh token
     * @param string $restaurantId Restaurant ID
     * @return array New token data
     * @throws ApiException
     */
    public function refreshAccessToken(string $refreshToken, string $restaurantId): array
    {
        $client = new HttpClient();

        $response = $client->post("{$this->apiUrl}/{$restaurantId}/oauth/token", [
            'form_params' => [
                'refresh_token' => $refreshToken,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'refresh_token',
            ],
            'http_errors' => false,
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if ($response->getStatusCode() >= 400 || ! isset($data['access_token'])) {
            throw new ApiException(
                $data['error_description'] ?? $data['message'] ?? 'Failed to refresh access token',
                $response->getStatusCode()
            );
        }

        return $data;
    }

    /**
     * Validate the callback response
     *
     * @param array $params Query parameters from the callback
     * @param string $expectedState The state parameter that was sent in the authorization request
     * @return array Validated data with 'code' and 'restaurant_id'
     * @throws ApiException If validation fails
     */
    public function validateCallback(array $params, string $expectedState): array
    {
        // Check for error response
        if (isset($params['error'])) {
            throw new ApiException('Authorization was denied: ' . $params['error'], 401);
        }

        // Validate required parameters
        if (! isset($params['code']) || ! isset($params['restaurant_id']) || ! isset($params['state'])) {
            throw new ApiException('Invalid callback: missing required parameters', 400);
        }

        // Validate state parameter to prevent CSRF attacks
        if ($params['state'] !== $expectedState) {
            throw new ApiException('Invalid state parameter', 400);
        }

        if (! isset($params['state']) || $params['state'] !== $expectedState) {
            throw new ApiException('Invalid state parameter');
        }

        return [
            'code'          => $params['code'],
            'restaurant_id' => $params['restaurant_id'],
        ];
    }
}
