/**
 * Blitz Cache Admin JavaScript
 */

(function($) {
    'use strict';

    // Main admin object
    window.blitzCache = {
        ajaxUrl: blitzCache.ajaxUrl,
        nonce: blitzCache.nonce,
        strings: blitzCache.strings,

        /**
         * Purge all cache
         */
        purgeAll: function() {
            if (!confirm(this.strings.purging)) {
                return;
            }

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blitz_cache_purge_all',
                    nonce: this.nonce
                },
                beforeSend: function() {
                    $('#btn-purge-all, .button-primary').prop('disabled', true).addClass('blitz-cache-loading');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || blitzCache.strings.error);
                    }
                },
                error: function() {
                    alert(blitzCache.strings.error);
                },
                complete: function() {
                    $('#btn-purge-all, .button-primary').prop('disabled', false).removeClass('blitz-cache-loading');
                }
            });
        },

        /**
         * Purge specific URL
         */
        purgeUrl: function(url) {
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blitz_cache_purge_url',
                    url: url,
                    nonce: this.nonce
                },
                beforeSend: function() {
                    $('#wp-admin-bar-blitz-cache-purge-all, #wp-admin-bar-blitz-cache-purge-page').addClass('blitz-cache-loading');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || blitzCache.strings.error);
                    }
                },
                error: function() {
                    alert(blitzCache.strings.error);
                },
                complete: function() {
                    $('#wp-admin-bar-blitz-cache-purge-all, #wp-admin-bar-blitz-cache-purge-page').removeClass('blitz-cache-loading');
                }
            });
        },

        /**
         * Warm up cache
         */
        warmup: function() {
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blitz_cache_warmup',
                    nonce: this.nonce
                },
                beforeSend: function() {
                    $('#btn-warmup').prop('disabled', true).addClass('blitz-cache-loading');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || blitzCache.strings.error);
                    }
                },
                error: function() {
                    alert(blitzCache.strings.error);
                },
                complete: function() {
                    $('#btn-warmup').prop('disabled', false).removeClass('blitz-cache-loading');
                }
            });
        },

        /**
         * Save settings
         */
        saveSettings: function(form) {
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: $(form).serialize() + '&action=blitz_cache_save_settings&nonce=' + this.nonce,
                beforeSend: function() {
                    $(form).find('button[type="submit"]').prop('disabled', true).text(blitzCache.strings.saving);
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || blitzCache.strings.error);
                    }
                },
                error: function() {
                    alert(blitzCache.strings.error);
                },
                complete: function() {
                    $(form).find('button[type="submit"]').prop('disabled', false).text('Save Settings');
                }
            });
        },

        /**
         * Test Cloudflare connection
         */
        testCloudflare: function() {
            const token = $('#cf_api_token').val();

            if (!token) {
                alert('Please enter an API token');
                return;
            }

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blitz_cache_test_cloudflare',
                    nonce: this.nonce
                },
                beforeSend: function() {
                    $('#btn-test-connection').prop('disabled', true).text(blitzCache.strings.testing);
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);

                        // Populate zones dropdown
                        const zoneSelect = $('#cf_zone_id');
                        zoneSelect.empty().append('<option value="">Select a zone</option>');

                        if (response.data.zones && response.data.zones.length > 0) {
                            response.data.zones.forEach(function(zone) {
                                zoneSelect.append('<option value="' + zone.id + '">' + zone.name + '</option>');
                            });
                        }
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(blitzCache.strings.error);
                },
                complete: function() {
                    $('#btn-test-connection').prop('disabled', false).text('Test Connection');
                }
            });
        },

        /**
         * Save Cloudflare settings
         */
        saveCloudflare: function() {
            const form = $('#blitz-cache-cloudflare-form');
            if (!form.length) {
                // Create form dynamically
                const token = $('#cf_api_token').val();
                const zoneId = $('#cf_zone_id').val();
                const workersEnabled = $('#cf_workers_enabled').is(':checked') ? 1 : 0;
                const workersRoute = $('#cf_workers_route').val();

                $.ajax({
                    url: this.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'blitz_cache_save_cloudflare',
                        api_token: token,
                        zone_id: zoneId,
                        workers_enabled: workersEnabled,
                        workers_route: workersRoute,
                        nonce: this.nonce
                    },
                    beforeSend: function() {
                        $('#btn-save-cloudflare').prop('disabled', true).text('Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                        } else {
                            alert(response.data.message || blitzCache.strings.error);
                        }
                    },
                    error: function() {
                        alert(blitzCache.strings.error);
                    },
                    complete: function() {
                        $('#btn-save-cloudflare').prop('disabled', false).text('Save Cloudflare Settings');
                    }
                });
            } else {
                this.saveSettings(form[0]);
            }
        },

        /**
         * Toggle debug info
         */
        toggleDebug: function() {
            $('#debug-info').toggle();
            const btn = $('#btn-toggle-debug');
            btn.text($('#debug-info').is(':visible') ? 'Hide Debug Info' : 'Show Debug Info');
        },

        /**
         * Export settings
         */
        exportSettings: function() {
            const data = {
                action: 'blitz_cache_export_settings',
                nonce: this.nonce
            };

            window.location.href = this.ajaxUrl + '?' + $.param(data);
        },

        /**
         * Show uninstall modal
         */
        showUninstallModal: function() {
            $('#blitz-cache-uninstall-modal').show();
        },

        /**
         * Close uninstall modal
         */
        closeUninstallModal: function() {
            $('#blitz-cache-uninstall-modal').hide();
        },

        /**
         * Confirm uninstall
         */
        confirmUninstall: function() {
            const choice = $('input[name="uninstall_choice"]:checked').val();

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blitz_cache_set_uninstall_preference',
                    choice: choice,
                    nonce: this.nonce
                },
                success: function() {
                    window.location.href = 'plugins.php?action=delete-selected&checked[]=blitz-cache/blitz-cache.php&plugin_status=all&paged=1&s=';
                }
            });
        },

        /**
         * Toggle Workers script
         */
        toggleWorkersScript: function() {
            const container = $('#workers-script-container');
            const checkbox = $('#cf_workers_enabled');

            if (checkbox.is(':checked')) {
                container.show();
                this.loadWorkersScript();
            } else {
                container.hide();
            }
        },

        /**
         * Load Workers script
         */
        loadWorkersScript: function() {
            const textarea = $('#workers_script');

            if (textarea.val()) {
                return; // Already loaded
            }

            // Fetch the script from the server
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blitz_cache_get_workers_script',
                    nonce: this.nonce
                },
                success: function(response) {
                    if (response.success) {
                        textarea.val(response.data.script);
                    }
                }
            });
        }
    };

    // Document ready
    $(document).ready(function() {
        // Purge all button
        $('#btn-purge-all').on('click', function(e) {
            e.preventDefault();
            window.blitzCache.purgeAll();
        });

        // Warmup button
        $('#btn-warmup').on('click', function(e) {
            e.preventDefault();
            window.blitzCache.warmup();
        });

        // Save settings form
        $('#blitz-cache-settings-form').on('submit', function(e) {
            e.preventDefault();
            window.blitzCache.saveSettings(this);
        });

        // Test Cloudflare connection
        $('#btn-test-connection').on('click', function(e) {
            e.preventDefault();
            window.blitzCache.testCloudflare();
        });

        // Save Cloudflare settings
        $('#btn-save-cloudflare').on('click', function(e) {
            e.preventDefault();
            window.blitzCache.saveCloudflare();
        });

        // Toggle debug info
        $('#btn-toggle-debug').on('click', function(e) {
            e.preventDefault();
            window.blitzCache.toggleDebug();
        });

        // Export settings
        $('#btn-export-settings').on('click', function(e) {
            e.preventDefault();
            window.blitzCache.exportSettings();
        });

        // Workers enabled toggle
        $('#cf_workers_enabled').on('change', function() {
            window.blitzCache.toggleWorkersScript();
        });

        // Copy Workers script
        $('#btn-copy-script').on('click', async function(e) {
            e.preventDefault();
            const textarea = document.getElementById('workers_script');

            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(textarea.value);
                    alert('Script copied to clipboard');
                } catch (err) {
                    // Fallback to deprecated method if modern API fails
                    textarea.select();
                    try {
                        document.execCommand('copy');
                        alert('Script copied to clipboard');
                    } catch (fallbackErr) {
                        alert('Failed to copy script to clipboard');
                    }
                }
            } else {
                // Fallback for older browsers
                textarea.select();
                document.execCommand('copy');
                alert('Script copied to clipboard');
            }
        });
    });

})(jQuery);
