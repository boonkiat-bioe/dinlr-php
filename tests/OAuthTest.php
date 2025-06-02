<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\OAuthClient;
use Nava\Dinlr\OAuthConfig;
use PHPUnit\Framework\TestCase;

class OAuthTest extends TestCase
{
    /**
     * @var array
     */
    protected $testConfig;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: OAuth Authentication Flow";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        // Load base config first
        $this->testConfig = require __DIR__ . '/config.php';

        // Then load OAuth-specific config
        $oauthConfig      = require __DIR__ . '/config_oauth.php';
        $this->testConfig = array_merge($this->testConfig, $oauthConfig);

        echo "\n• Client ID: " . $this->testConfig['client_id'];
        echo "\n• Redirect URI: " . $this->testConfig['redirect_uri'];
        echo "\n• Test state: " . $this->testConfig['test_oauth_data']['state'];
        echo "\n--------------------------------------------------------------";
    }

    /**
     * Test OAuth client creation
     */
    public function testOAuthClientCreation()
    {
        echo "\n\nSTEP 1: Testing OAuth client creation";
        echo "\n--------------------------------------------------------------";

        $config = new OAuthConfig($this->testConfig);
        $client = new OAuthClient($config);

        echo "\n✓ OAuth client created successfully";

        $this->assertInstanceOf(OAuthClient::class, $client);
    }

    /**
     * Test get authorization URL
     */
    public function testGetAuthorizationUrl()
    {
        echo "\n\nSTEP 2: Testing authorization URL generation";
        echo "\n--------------------------------------------------------------";

        $config = new OAuthConfig($this->testConfig);
        $client = new OAuthClient($config);
        $state  = $this->testConfig['test_oauth_data']['state'];
        $url    = $client->getAuthorizationUrl($state);

        echo "\n• Using state parameter: " . $state;
        echo "\n• Auth URL: " . $url;
        echo "\n✓ Authorization URL generated successfully";

        $this->assertStringContainsString('client_id=' . $this->testConfig['client_id'], $url);
        $this->assertStringContainsString('state=' . $state, $url);
    }

    /**
     * Test OAuth callback validation
     */
    /**
     * Test OAuth callback validation
     */
    public function testOAuthCallbackValidation()
    {
        echo "\n\nSTEP 3: Testing OAuth callback validation";
        echo "\n--------------------------------------------------------------";

        $config = new OAuthConfig($this->testConfig);
        $client = new OAuthClient($config);
        $state  = $this->testConfig['test_oauth_data']['state'];

        echo "\n• Using state: " . $state;

        // Test successful callback
        $callbackParams = [
            'code'          => $this->testConfig['test_oauth_data']['callback']['code'],
            'restaurant_id' => $this->testConfig['test_oauth_data']['callback']['restaurant_id'],
            'state'         => $state,
        ];

        echo "\n• Callback params:";
        echo "\n  - code: " . $callbackParams['code'];
        echo "\n  - restaurant_id: " . $callbackParams['restaurant_id'];
        echo "\n  - state: " . $callbackParams['state'];

        $validatedData = $client->handleCallback($callbackParams, $state);
        echo "\n✓ Callback validated successfully";

        $this->assertEquals($callbackParams['code'], $validatedData['code']);
        $this->assertEquals($callbackParams['restaurant_id'], $validatedData['restaurant_id']);

        // Test invalid state parameter
        echo "\n• Testing invalid state validation...";
        $callbackParams['state'] = 'invalid_state'; // Change to a mismatched state

        $this->expectException(\Nava\Dinlr\Exception\ApiException::class);
        $client->handleCallback($callbackParams, $state);
    }

    /**
     * Test handling error response from OAuth server
     */
    public function testOAuthErrorResponse()
    {
        echo "\n\nSTEP 4: Testing OAuth error response handling";
        echo "\n--------------------------------------------------------------";

        $config = new OAuthConfig($this->testConfig);
        $client = new OAuthClient($config);
        $state  = $this->testConfig['test_oauth_data']['state'];

        $callbackParams = [
            'error' => 'access_denied',
            'state' => $state,
        ];

        echo "\n• Error callback params:";
        echo "\n  - error: " . $callbackParams['error'];
        echo "\n  - state: " . $callbackParams['state'];
        echo "\n• Expected exception: ApiException with message 'Authorization was denied: access_denied'";

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Authorization was denied: access_denied');

        $client->handleCallback($callbackParams, $state);
    }
}
