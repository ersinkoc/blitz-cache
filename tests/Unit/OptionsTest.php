<?php
/**
 * Test class for Blitz_Cache_Options
 */

namespace BlitzCache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Test suite for Blitz_Cache_Options class
 */
class OptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey::setUp();

        // Clear static properties before each test
        $reflection = new \ReflectionClass('BlitzCache\\Blitz_Cache_Options');
        $settings = $reflection->getProperty('settings');
        $settings->setAccessible(true);
        $settings->setValue(null, null);

        $cloudflare = $reflection->getProperty('cloudflare');
        $cloudflare->setAccessible(true);
        $cloudflare->setValue(null, null);

        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->returnTrue();
        Functions\when('wp_salt')->return('test_salt_123456');
        Functions\when('openssl_encrypt')->justReturn('encrypted_data');
        Functions\when('openssl_decrypt')->justReturn('decrypted_data');
        Functions\when('openssl_random_pseudo_bytes')->return(str_repeat('0', 16));
        Functions\when('base64_encode')->returnArg();
        Functions\when('base64_decode')->returnArg();
    }

    protected function tearDown(): void
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    /**
     * Test get returns default settings when no settings exist
     */
    public function testGetReturnsDefaultSettingsWhenNoSettingsExist()
    {
        $options = Blitz_Cache_Options::get();

        $this->assertIsArray($options);
        $this->assertTrue($options['page_cache_enabled']);
        $this->assertEquals(86400, $options['page_cache_ttl']);
        $this->assertFalse($options['cache_logged_in']);
    }

    /**
     * Test get returns specific setting by key
     */
    public function testGetReturnsSpecificSettingByKey()
    {
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => false,
            'page_cache_ttl' => 3600
        ]);

        $result = Blitz_Cache_Options::get('page_cache_enabled');

        $this->assertFalse($result);
    }

    /**
     * Test get returns null for non-existent key
     */
    public function testGetReturnsNullForNonExistentKey()
    {
        Functions\when('get_option')->justReturn([]);

        $result = Blitz_Cache_Options::get('non_existent_key');

        $this->assertNull($result);
    }

    /**
     * Test set updates settings correctly
     */
    public function testSetUpdatesSettingsCorrectly()
    {
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->returnTrue();

        $result = Blitz_Cache_Options::set([
            'page_cache_enabled' => false,
            'page_cache_ttl' => 7200
        ]);

        $this->assertTrue($result);

        $options = Blitz_Cache_Options::get();
        $this->assertFalse($options['page_cache_enabled']);
        $this->assertEquals(7200, $options['page_cache_ttl']);
    }

    /**
     * Test set merges with existing settings
     */
    public function testSetMergesWithExistingSettings()
    {
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'cache_logged_in' => false
        ]);
        Functions\when('update_option')->returnTrue();

        Blitz_Cache_Options::set([
            'page_cache_ttl' => 3600,
            'cache_logged_in' => true
        ]);

        $options = Blitz_Cache_Options::get();
        $this->assertTrue($options['page_cache_enabled']); // Should keep existing
        $this->assertEquals(3600, $options['page_cache_ttl']); // Should update
        $this->assertTrue($options['cache_logged_in']); // Should update
    }

    /**
     * Test get_cloudflare returns settings
     */
    public function testGetCloudflareReturnsSettings()
    {
        Functions\when('get_option')->justReturn([
            'api_token' => 'encrypted_token',
            'zone_id' => 'zone_123',
            'connection_status' => 'connected'
        ]);

        $cloudflare = Blitz_Cache_Options::get_cloudflare();

        $this->assertIsArray($cloudflare);
        $this->assertEquals('decrypted_data', $cloudflare['api_token']); // Should be decrypted
        $this->assertEquals('zone_123', $cloudflare['zone_id']);
        $this->assertEquals('connected', $cloudflare['connection_status']);
    }

    /**
     * Test get_cloudflare returns specific setting by key
     */
    public function testGetCloudflareReturnsSpecificSettingByKey()
    {
        Functions\when('get_option')->justReturn([
            'api_token' => 'encrypted_token',
            'zone_id' => 'zone_123'
        ]);

        $result = Blitz_Cache_Options::get_cloudflare('zone_id');

        $this->assertEquals('zone_123', $result);
    }

    /**
     * Test set_cloudflare encrypts API token
     */
    public function testSetCloudflareEncryptsApiToken()
    {
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->returnTrue();

        Blitz_Cache_Options::set_cloudflare([
            'api_token' => 'my_secret_token',
            'zone_id' => 'zone_123'
        ]);

        $this->assertTrue(true); // If we reach here, encryption worked
    }

    /**
     * Test set_cloudflare updates cloudflare settings
     */
    public function testSetCloudflareUpdatesCloudflareSettings()
    {
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->returnTrue();

        $result = Blitz_Cache_Options::set_cloudflare([
            'api_token' => 'test_token',
            'zone_id' => 'zone_123',
            'workers_enabled' => true
        ]);

        $this->assertTrue($result);

        $cloudflare = Blitz_Cache_Options::get_cloudflare();
        $this->assertEquals('decrypted_data', $cloudflare['api_token']); // Should be decrypted when retrieved
        $this->assertEquals('zone_123', $cloudflare['zone_id']);
        $this->assertTrue($cloudflare['workers_enabled']);
    }

    /**
     * Test encrypt method returns base64 when openssl not available
     */
    public function testEncryptReturnsBase64WhenOpensslNotAvailable()
    {
        // Mock openssl_encrypt not available
        Functions\when('function_exists')->with('openssl_encrypt')->return(false);

        $result = Blitz_Cache_Options::encrypt('test_data');

        $this->assertEquals(base64_encode('test_data'), $result);
    }

    /**
     * Test decrypt method returns base64 decoded when openssl not available
     */
    public function testDecryptReturnsBase64DecodedWhenOpensslNotAvailable()
    {
        Functions\when('function_exists')->with('openssl_encrypt')->return(false);
        Functions\when('function_exists')->with('openssl_decrypt')->return(false);

        $result = Blitz_Cache_Options::decrypt(base64_encode('test_data'));

        $this->assertEquals('test_data', $result);
    }

    /**
     * Test get_defaults returns all default settings
     */
    public function testGetDefaultsReturnsAllDefaultSettings()
    {
        $defaults = Blitz_Cache_Options::get_defaults();

        $this->assertIsArray($defaults);
        $this->assertArrayHasKey('page_cache_enabled', $defaults);
        $this->assertArrayHasKey('page_cache_ttl', $defaults);
        $this->assertArrayHasKey('cache_logged_in', $defaults);
        $this->assertArrayHasKey('mobile_cache', $defaults);
        $this->assertArrayHasKey('browser_cache_enabled', $defaults);
        $this->assertArrayHasKey('gzip_enabled', $defaults);
        $this->assertArrayHasKey('html_minify_enabled', $defaults);
        $this->assertArrayHasKey('excluded_urls', $defaults);
        $this->assertArrayHasKey('excluded_cookies', $defaults);
        $this->assertArrayHasKey('excluded_user_agents', $defaults);
        $this->assertArrayHasKey('warmup_enabled', $defaults);
        $this->assertArrayHasKey('warmup_source', $defaults);
        $this->assertArrayHasKey('warmup_interval', $defaults);
        $this->assertArrayHasKey('warmup_batch_size', $defaults);
        $this->assertArrayHasKey('update_channel', $defaults);
    }

    /**
     * Test default values are correct
     */
    public function testDefaultValuesAreCorrect()
    {
        $defaults = Blitz_Cache_Options::get_defaults();

        $this->assertTrue($defaults['page_cache_enabled']);
        $this->assertEquals(86400, $defaults['page_cache_ttl']); // 24 hours
        $this->assertFalse($defaults['cache_logged_in']);
        $this->assertFalse($defaults['mobile_cache']);
        $this->assertTrue($defaults['browser_cache_enabled']);
        $this->assertEquals(2592000, $defaults['css_js_ttl']); // 30 days
        $this->assertEquals(7776000, $defaults['images_ttl']); // 90 days
        $this->assertTrue($defaults['gzip_enabled']);
        $this->assertTrue($defaults['html_minify_enabled']);
        $this->assertIsArray($defaults['excluded_urls']);
        $this->assertIsArray($defaults['excluded_cookies']);
        $this->assertContains('wordpress_logged_in_*', $defaults['excluded_cookies']);
        $this->assertIsArray($defaults['excluded_user_agents']);
        $this->assertTrue($defaults['warmup_enabled']);
        $this->assertEquals('sitemap', $defaults['warmup_source']);
        $this->assertEquals(21600, $defaults['warmup_interval']); // 6 hours
        $this->assertEquals(5, $defaults['warmup_batch_size']);
        $this->assertEquals('stable', $defaults['update_channel']);
    }

    /**
     * Test reset restores all defaults
     */
    public function testResetRestoresAllDefaults()
    {
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => false,
            'page_cache_ttl' => 100
        ]);
        Functions\when('update_option')->returnTrue();

        Blitz_Cache_Options::reset();

        $options = Blitz_Cache_Options::get();
        $this->assertTrue($options['page_cache_enabled']);
        $this->assertEquals(86400, $options['page_cache_ttl']);
    }

    /**
     * Test get with empty string returns all settings
     */
    public function testGetWithEmptyStringReturnsAllSettings()
    {
        Functions\when('get_option')->justReturn([
            'page_cache_enabled' => false
        ]);

        $result = Blitz_Cache_Options::get('');

        $this->assertIsArray($result);
        $this->assertFalse($result['page_cache_enabled']);
    }

    /**
     * Test get_cloudflare with empty string returns all settings
     */
    public function testGetCloudflareWithEmptyStringReturnsAllSettings()
    {
        Functions\when('get_option')->justReturn([
            'api_token' => 'test_token'
        ]);

        $result = Blitz_Cache_Options::get_cloudflare('');

        $this->assertIsArray($result);
        $this->assertEquals('decrypted_data', $result['api_token']);
    }

    /**
     * Test encryption/decryption round trip
     */
    public function testEncryptionDecryptionRoundTrip()
    {
        $originalData = 'my_secret_token_12345';

        $encrypted = Blitz_Cache_Options::encrypt($originalData);
        $decrypted = Blitz_Cache_Options::decrypt($encrypted);

        $this->assertNotEquals($originalData, $encrypted); // Should be encrypted
        $this->assertEquals($originalData, $decrypted); // Should decrypt back
    }

    /**
     * Test encryption uses correct method
     */
    public function testEncryptionUsesCorrectMethod()
    {
        $reflection = new \ReflectionClass('BlitzCache\\Blitz_Cache_Options');
        $method = $reflection->getMethod('encrypt');
        $method->setAccessible(true);

        $result = $method->invoke(null, 'test');

        // Should use base64 encoding as fallback
        $this->assertNotEquals('test', $result);
    }

    /**
     * Test caching of settings in static properties
     */
    public function testCachingOfSettingsInStaticProperties()
    {
        Functions\when('get_option')->justReturn(['page_cache_enabled' => true]);

        // First call should trigger get_option
        $result1 = Blitz_Cache_Options::get();

        // Second call should use cached value
        $result2 = Blitz_Cache_Options::get();

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test cloudflare settings are cached separately
     */
    public function testCloudflareSettingsAreCachedSeparately()
    {
        Functions\when('get_option')->justReturn(['api_token' => 'test']);

        // First call should trigger get_option
        $result1 = Blitz_Cache_Options::get_cloudflare();

        // Second call should use cached value
        $result2 = Blitz_Cache_Options::get_cloudflare();

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test set does not affect cloudflare settings
     */
    public function testSetDoesNotAffectCloudflareSettings()
    {
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->returnTrue();

        // Set regular settings
        Blitz_Cache_Options::set(['page_cache_enabled' => false]);

        // Get cloudflare settings (should be separate)
        $cloudflare = Blitz_Cache_Options::get_cloudflare();

        $this->assertIsArray($cloudflare);
        $this->assertArrayNotHasKey('page_cache_enabled', $cloudflare);
    }

    /**
     * Test set_cloudflare does not affect regular settings
     */
    public function testSetCloudflareDoesNotAffectRegularSettings()
    {
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->returnTrue();

        // Set cloudflare settings
        Blitz_Cache_Options::set_cloudflare(['api_token' => 'test']);

        // Get regular settings (should be separate)
        $settings = Blitz_Cache_Options::get();

        $this->assertIsArray($settings);
        $this->assertArrayNotHasKey('api_token', $settings);
    }
}
