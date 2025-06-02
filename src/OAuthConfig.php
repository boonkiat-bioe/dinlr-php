<?php
namespace Nava\Dinlr;

use Nava\Dinlr\Exception\ConfigException;

/**
 * OAuth Configuration class for Dinlr API client
 */
class OAuthConfig extends Config
{
    /**
     * @var string|null
     */
    private $clientId;

    /**
     * @var string|null
     */
    private $clientSecret;

    /**
     * @var string|null
     */
    private $redirectUri;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * @var int|null
     */
    private $expiresAt;

    /**
     * Create a new OAuth configuration instance
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (isset($config['client_id'])) {
            $this->setClientId($config['client_id']);
        }

        if (isset($config['client_secret'])) {
            $this->setClientSecret($config['client_secret']);
        }

        if (isset($config['redirect_uri'])) {
            $this->setRedirectUri($config['redirect_uri']);
        }

        if (isset($config['access_token'])) {
            $this->setAccessToken($config['access_token']);
        }

        if (isset($config['refresh_token'])) {
            $this->setRefreshToken($config['refresh_token']);
        }

        if (isset($config['expires_at'])) {
            $this->setExpiresAt($config['expires_at']);
        } elseif (isset($config['expires_in']) && isset($config['access_token'])) {
            $this->setExpiresAt(time() + $config['expires_in']);
        }
    }

    /**
     * Set the client ID
     *
     * @param string $clientId
     * @return self
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Get the client ID
     *
     * @return string
     * @throws ConfigException
     */
    public function getClientId(): string
    {
        if (empty($this->clientId)) {
            throw new ConfigException('Client ID is not set');
        }

        return $this->clientId;
    }

    /**
     * Set the client secret
     *
     * @param string $clientSecret
     * @return self
     */
    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Get the client secret
     *
     * @return string
     * @throws ConfigException
     */
    public function getClientSecret(): string
    {
        if (empty($this->clientSecret)) {
            throw new ConfigException('Client secret is not set');
        }

        return $this->clientSecret;
    }

    /**
     * Set the redirect URI
     *
     * @param string $redirectUri
     * @return self
     */
    public function setRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    /**
     * Get the redirect URI
     *
     * @return string
     * @throws ConfigException
     */
    public function getRedirectUri(): string
    {
        if (empty($this->redirectUri)) {
            throw new ConfigException('Redirect URI is not set');
        }

        return $this->redirectUri;
    }

    /**
     * Set the access token
     *
     * @param string $accessToken
     * @return self
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Get the access token
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Set the refresh token
     *
     * @param string $refreshToken
     * @return self
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Get the refresh token
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Set the token expiration timestamp
     *
     * @param int $expiresAt
     * @return self
     */
    public function setExpiresAt(int $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * Get the token expiration timestamp
     *
     * @return int|null
     */
    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    /**
     * Check if the access token is set
     *
     * @return bool
     */
    public function hasAccessToken(): bool
    {
        return ! empty($this->accessToken);
    }

    /**
     * Check if the refresh token is set
     *
     * @return bool
     */
    public function hasRefreshToken(): bool
    {
        return ! empty($this->refreshToken);
    }

    /**
     * Check if the access token has expired
     *
     * @param int $bufferSeconds Seconds to subtract from expiry time
     * @return bool
     */
    public function isAccessTokenExpired(int $bufferSeconds = 300): bool
    {
        if (! $this->expiresAt) {
            return true;
        }

        return (time() + $bufferSeconds) >= $this->expiresAt;
    }

    /**
     * Update token information from authorization response
     *
     * @param array $tokenData Token data from authorization response
     * @return self
     */
    public function updateFromTokenResponse(array $tokenData): self
    {
        if (isset($tokenData['access_token'])) {
            $this->setAccessToken($tokenData['access_token']);
        }

        if (isset($tokenData['refresh_token'])) {
            $this->setRefreshToken($tokenData['refresh_token']);
        }

        if (isset($tokenData['expires_in'])) {
            $this->setExpiresAt(time() + $tokenData['expires_in']);
        }

        return $this;
    }
}
