<?php
class Blitz_Cache_Options {
    private static ?array $settings = null;
    private static ?array $cloudflare = null;

    public static function get(string $key = ''): mixed {
        if (self::$settings === null) {
            self::$settings = get_option('blitz_cache_settings', []);
        }

        if ($key === '') {
            return self::$settings;
        }

        return self::$settings[$key] ?? null;
    }

    public static function set(array $settings): bool {
        // Merge with existing settings
        $merged = array_merge(self::$settings ?? [], $settings);
        $result = update_option('blitz_cache_settings', $merged);

        // Reset cache so next get() will fetch fresh data from DB
        if ($result) {
            self::$settings = null;
        }

        return $result;
    }

    public static function get_cloudflare(string $key = ''): mixed {
        if (self::$cloudflare === null) {
            self::$cloudflare = get_option('blitz_cache_cloudflare', []);

            // Decrypt token with error handling
            if (!empty(self::$cloudflare['api_token'])) {
                try {
                    self::$cloudflare['api_token'] = self::decrypt(self::$cloudflare['api_token']);
                } catch (Blitz_Cache_Exception $e) {
                    // Log error and return null for token
                    if (function_exists('Blitz_Cache_Logger')) {
                        Blitz_Cache_Logger::get_instance()->error(
                            'Failed to decrypt Cloudflare API token',
                            ['error' => $e->getMessage()]
                        );
                    }
                    self::$cloudflare['api_token'] = null;
                }
            }
        }

        if ($key === '') {
            return self::$cloudflare;
        }

        return self::$cloudflare[$key] ?? null;
    }

    public static function set_cloudflare(array $settings): bool {
        // Encrypt token before saving with error handling
        if (!empty($settings['api_token'])) {
            try {
                $settings['api_token'] = self::encrypt($settings['api_token']);
            } catch (Blitz_Cache_Exception $e) {
                // Log error and don't save the token
                if (function_exists('Blitz_Cache_Logger')) {
                    Blitz_Cache_Logger::get_instance()->error(
                        'Failed to encrypt Cloudflare API token',
                        ['error' => $e->getMessage()]
                    );
                }
                return false;
            }
        }

        // Merge with existing settings and save
        $merged = array_merge(self::$cloudflare ?? [], $settings);
        $result = update_option('blitz_cache_cloudflare', $merged);

        // Reset cache so next get_cloudflare() will fetch from DB and decrypt properly
        if ($result) {
            self::$cloudflare = null;
        }

        return $result;
    }

    /**
     * Encrypt data using AES-256-CBC with HMAC for integrity.
     *
     * @param string $data Data to encrypt.
     * @return string Base64-encoded encrypted data with HMAC.
     * @throws Blitz_Cache_Exception If OpenSSL is not available or encryption fails.
     */
    private static function encrypt(string $data): string {
        if (!function_exists('openssl_encrypt')) {
            throw new Blitz_Cache_Exception('OpenSSL extension is not available for encryption');
        }

        $key = wp_salt('auth');
        $iv = openssl_random_pseudo_bytes(16);
        if ($iv === false) {
            throw new Blitz_Cache_Exception('Failed to generate IV for encryption');
        }

        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new Blitz_Cache_Exception('AES-256 encryption failed');
        }

        // Generate HMAC for integrity verification (32 bytes for SHA-256)
        $hmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
        if ($hmac === false) {
            throw new Blitz_Cache_Exception('HMAC generation failed');
        }

        // Format: HMAC (32 bytes) + IV (16 bytes) + encrypted data
        return base64_encode($hmac . $iv . $encrypted);
    }

    /**
     * Decrypt data using AES-256-CBC with HMAC verification.
     *
     * @param string $data Base64-encoded encrypted data with HMAC.
     * @return string Decrypted data.
     * @throws Blitz_Cache_Exception If OpenSSL is not available or decryption/verification fails.
     */
    private static function decrypt(string $data): string {
        if (!function_exists('openssl_decrypt')) {
            throw new Blitz_Cache_Exception('OpenSSL extension is not available for decryption');
        }

        $key = wp_salt('auth');
        $decoded = base64_decode($data);

        if ($decoded === false) {
            throw new Blitz_Cache_Exception('Failed to decode base64 encrypted data');
        }

        // Minimum length: HMAC (32) + IV (16) = 48 bytes
        if (strlen($decoded) < 48) {
            throw new Blitz_Cache_Exception('Encrypted data is too short to be valid');
        }

        // Extract components: HMAC (32 bytes) + IV (16 bytes) + encrypted data
        $hmac = substr($decoded, 0, 32);
        $iv = substr($decoded, 32, 16);
        $encrypted = substr($decoded, 48);

        // Verify HMAC for integrity (constant-time comparison)
        $calculated_hmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
        if (!hash_equals($hmac, $calculated_hmac)) {
            throw new Blitz_Cache_Exception('HMAC verification failed - data may be tampered');
        }

        // Decrypt the data
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new Blitz_Cache_Exception('AES-256 decryption failed');
        }

        return $decrypted;
    }

    public static function get_defaults(): array {
        return [
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'cache_logged_in' => false,
            'mobile_cache' => false,
            'browser_cache_enabled' => true,
            'css_js_ttl' => 2592000,
            'images_ttl' => 7776000,
            'gzip_enabled' => true,
            'html_minify_enabled' => true,
            'excluded_urls' => [],
            'excluded_cookies' => ['wordpress_logged_in_*', 'woocommerce_cart_hash', 'woocommerce_items_in_cart'],
            'excluded_user_agents' => [],
            'warmup_enabled' => true,
            'warmup_source' => 'sitemap',
            'warmup_interval' => 21600,
            'warmup_batch_size' => 5,
            'update_channel' => 'stable',
        ];
    }

    public static function reset(): void {
        self::$settings = self::get_defaults();
        update_option('blitz_cache_settings', self::$settings);
    }
}
