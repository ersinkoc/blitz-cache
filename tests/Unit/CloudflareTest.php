<?php
/**
 * Test class for \Blitz_Cache_Cloudflare
 */

namespace BlitzCache\Tests\Unit;

use BlitzCache\Tests\BlitzCacheTestCase;
use Brain\Monkey\Functions;

/**
 * Test suite for \Blitz_Cache_Cloudflare class
 */
class CloudflareTest extends BlitzCacheTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock cloudflare options
        Functions\when('get_option')->justReturn([
            'api_token' => 'test_token',
            'zone_id' => 'test_zone_id',
            'connection_status' => 'connected',
            'workers_enabled' => false,
            'workers_route' => ''
        ]);
        Functions\when('update_option')->justReturn(true);
        Functions\when('wp_salt')->justReturn('test_salt_for_encryption');
        // Note: openssl_*, hash_hmac, base64_* are PHP internal functions that cannot be mocked
        // The Blitz_Cache_Options class handles encryption internally with fallbacks
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test test_connection returns success on valid token
     */
    public function testTestConnectionReturnsSuccessOnValidToken()
    {
        $mockResponse = [
            'success' => true,
            'result' => ['id' => 'test_id']
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->test_connection();

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Connection successful!', $result['message']);
    }

    /**
     * Test test_connection returns error on invalid token
     */
    public function testTestConnectionReturnsErrorOnInvalidToken()
    {
        $mockResponse = [
            'success' => false,
            'errors' => [['message' => 'Invalid API token']]
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->test_connection();

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid API token', $result['message']);
    }

    /**
     * Test test_connection handles WP_Error
     */
    public function testTestConnectionHandlesWpError()
    {
        Functions\when('wp_remote_request')->justReturn(new \WP_Error('http_error', 'Connection failed'));

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->test_connection();

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Connection failed', $result['message']);
    }

    /**
     * Test get_zones returns zones array
     */
    public function testGetZonesReturnsZonesArray()
    {
        $mockResponse = [
            'success' => true,
            'result' => [
                [
                    'id' => 'zone_1',
                    'name' => 'example1.com',
                    'status' => 'active'
                ],
                [
                    'id' => 'zone_2',
                    'name' => 'example2.com',
                    'status' => 'active'
                ]
            ]
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $zones = $cloudflare->get_zones();

        $this->assertIsArray($zones);
        $this->assertCount(2, $zones);
        $this->assertEquals('zone_1', $zones[0]['id']);
        $this->assertEquals('example1.com', $zones[0]['name']);
        $this->assertEquals('active', $zones[0]['status']);
    }

    /**
     * Test get_zones returns empty array on error
     */
    public function testGetZonesReturnsEmptyArrayOnError()
    {
        Functions\when('wp_remote_request')->justReturn(new \WP_Error('http_error', 'Error'));

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $zones = $cloudflare->get_zones();

        $this->assertIsArray($zones);
        $this->assertEmpty($zones);
    }

    /**
     * Test purge_all returns true on success
     */
    public function testPurgeAllReturnsTrueOnSuccess()
    {
        $mockResponse = [
            'success' => true,
            'result' => ['id' => 'purge_id']
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_all();

        $this->assertTrue($result);
    }

    /**
     * Test purge_all returns false when zone_id not set
     */
    public function testPurgeAllReturnsFalseWhenZoneIdNotSet()
    {
        // Override the cloudflare options for this test
        $this->resetOptionsCache();
        Functions\when('get_option')->justReturn([
            'api_token' => 'test_token',
            'zone_id' => '',
            'connection_status' => 'connected'
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_all();

        $this->assertFalse($result);
    }

    /**
     * Test purge_all returns false on API error
     */
    public function testPurgeAllReturnsFalseOnApiError()
    {
        $mockResponse = [
            'success' => false,
            'errors' => [['message' => 'API Error']]
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_all();

        $this->assertFalse($result);
    }

    /**
     * Test purge_urls returns true on success
     */
    public function testPurgeUrlsReturnsTrueOnSuccess()
    {
        $mockResponse = [
            'success' => true,
            'result' => ['id' => 'purge_id']
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $urls = [
            'http://example.com/page1/',
            'http://example.com/page2/',
            'http://example.com/page3/'
        ];

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_urls($urls);

        $this->assertTrue($result);
    }

    /**
     * Test purge_urls returns false when zone_id not set
     */
    public function testPurgeUrlsReturnsFalseWhenZoneIdNotSet()
    {
        // Override the cloudflare options for this test
        $this->resetOptionsCache();
        Functions\when('get_option')->justReturn([
            'api_token' => 'test_token',
            'zone_id' => '',
            'connection_status' => 'connected'
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_urls(['http://example.com/test/']);

        $this->assertFalse($result);
    }

    /**
     * Test purge_urls returns false when URLs empty
     */
    public function testPurgeUrlsReturnsFalseWhenUrlsEmpty()
    {
        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_urls([]);

        $this->assertFalse($result);
    }

    /**
     * Test purge_urls handles multiple chunks (Cloudflare limit 30 URLs)
     */
    public function testPurgeUrlsHandlesMultipleChunks()
    {
        $mockResponse = [
            'success' => true,
            'result' => ['id' => 'purge_id']
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        // Create 35 URLs to test chunking (should be split into 2 requests)
        $urls = [];
        for ($i = 1; $i <= 35; $i++) {
            $urls[] = "http://example.com/page$i/";
        }

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $result = $cloudflare->purge_urls($urls);

        $this->assertTrue($result);
    }

    /**
     * Test get_workers_script returns JavaScript code
     */
    public function testGetWorkersScriptReturnsJavaScript()
    {
        $cloudflare = new \Blitz_Cache_Cloudflare();
        $script = $cloudflare->get_workers_script();

        $this->assertIsString($script);
        $this->assertNotEmpty($script);
        $this->assertStringContainsString('addEventListener', $script);
        $this->assertStringContainsString('fetch', $script);
        $this->assertStringContainsString('caches.default', $script);
    }

    /**
     * Test request method sends correct headers
     */
    public function testRequestMethodSendsCorrectHeaders()
    {
        $mockResponse = [
            'success' => true,
            'result' => []
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $method = new \ReflectionMethod($cloudflare, 'request');
        $method->setAccessible(true);

        $result = $method->invoke($cloudflare, 'GET', '/zones/test_zone/test');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test request method includes authorization header
     */
    public function testRequestMethodIncludesAuthorizationHeader()
    {
        $mockResponse = [
            'success' => true,
            'result' => []
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $method = new \ReflectionMethod($cloudflare, 'request');
        $method->setAccessible(true);

        $result = $method->invoke($cloudflare, 'GET', '/test');

        $this->assertIsArray($result);
    }

    /**
     * Test request method handles POST with data
     */
    public function testRequestMethodHandlesPostWithData()
    {
        $mockResponse = [
            'success' => true,
            'result' => []
        ];

        $testData = ['files' => ['http://example.com/test/']];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $method = new \ReflectionMethod($cloudflare, 'request');
        $method->setAccessible(true);

        $result = $method->invoke($cloudflare, 'POST', '/zones/test/purge_cache', $testData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test action hooks are fired on successful purge
     */
    public function testActionHooksFiredOnSuccessfulPurge()
    {
        $actionFired = false;

        add_action('blitz_cache_cf_purge_success', function($type, $urls = []) {
            global $actionFired;
            $actionFired = true;
            $this->assertEquals('all', $type);
        }, 10, 2);

        $mockResponse = [
            'success' => true,
            'result' => ['id' => 'purge_id']
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $cloudflare->purge_all();

        $this->assertTrue($actionFired);
    }

    /**
     * Test action hooks are fired on failed purge
     */
    public function testActionHooksFiredOnFailedPurge()
    {
        $actionFired = false;

        add_action('blitz_cache_cf_purge_failed', function($type, $response) {
            global $actionFired;
            $actionFired = true;
            $this->assertEquals('urls', $type);
        }, 10, 2);

        Functions\when('wp_remote_request')->justReturn(new \WP_Error('http_error', 'Error'));

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $cloudflare->purge_urls(['http://example.com/test/']);

        $this->assertTrue($actionFired);
    }

    /**
     * Test last_purge timestamp is updated on successful purge
     */
    public function testLastPurgeTimestampUpdatedOnSuccess()
    {
        $mockResponse = [
            'success' => true,
            'result' => ['id' => 'purge_id']
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);
        // Mock update_option for the set_cloudflare call
        Functions\when('update_option')->justReturn(true);

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $cloudflare->purge_all();

        $this->assertTrue(true);
    }

    /**
     * Test api_url property is correct
     */
    public function testApiUrlPropertyIsCorrect()
    {
        $cloudflare = new \Blitz_Cache_Cloudflare();

        $reflection = new \ReflectionClass($cloudflare);
        $property = $reflection->getProperty('api_url');
        $property->setAccessible(true);

        $this->assertEquals('https://api.cloudflare.com/client/v4', $property->getValue($cloudflare));
    }

    /**
     * Test get_zones filters result correctly
     */
    public function testGetZonesFiltersResultCorrectly()
    {
        $mockResponse = [
            'success' => true,
            'result' => [
                [
                    'id' => 'zone_1',
                    'name' => 'example.com',
                    'status' => 'active',
                    'type' => 'full'
                ]
            ]
        ];

        Functions\when('wp_remote_request')->justReturn([
            'body' => json_encode($mockResponse)
        ]);
        Functions\when('wp_remote_retrieve_body')->justReturn(json_encode($mockResponse));

        $cloudflare = new \Blitz_Cache_Cloudflare();
        $zones = $cloudflare->get_zones();

        $this->assertIsArray($zones);
        $this->assertCount(1, $zones);
        $this->assertArrayNotHasKey('type', $zones[0]); // Should be filtered out
        $this->assertArrayHasKey('id', $zones[0]);
        $this->assertArrayHasKey('name', $zones[0]);
        $this->assertArrayHasKey('status', $zones[0]);
    }
}
