<?php
/**
 * Settings page view for API Explorer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$ViewHelper = new ViewHelper();
?>

<div class="wrap ninja-api-explorer-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php _e('API Explorer Settings', 'ninja-api-explorer'); ?>
    </h1>
    
    <div class="settings-container">
        <div class="settings-main">
            <form method="post" action="">
                <?php echo $ViewHelper->NonceField('ninja_api_settings'); ?>
                
                <div class="settings-section">
                    <h2><?php _e('General Settings', 'ninja-api-explorer'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Enable API Testing', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="enable_api_testing" value="1" 
                                           <?php checked($settings['enable_api_testing']); ?>>
                                    <?php _e('Allow testing of API endpoints from the admin interface', 'ninja-api-explorer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('When enabled, users can test API endpoints directly from the admin interface.', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Default Timeout', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <input type="number" name="default_timeout" value="<?php echo esc_attr($settings['default_timeout']); ?>" 
                                       min="1" max="300" class="regular-text">
                                <p class="description">
                                    <?php _e('Default timeout for API requests in seconds (1-300).', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Show Private Routes', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="show_private_routes" value="1" 
                                           <?php checked($settings['show_private_routes']); ?>>
                                    <?php _e('Display private/authenticated routes in the route list', 'ninja-api-explorer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Private routes require authentication to access.', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="settings-section">
                    <h2><?php _e('Caching Settings', 'ninja-api-explorer'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Cache Duration', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <input type="number" name="cache_duration" value="<?php echo esc_attr($settings['cache_duration']); ?>" 
                                       min="0" max="86400" class="regular-text">
                                <p class="description">
                                    <?php _e('How long to cache route information in seconds (0 = no caching, max 24 hours).', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="settings-section">
                    <h2><?php _e('Logging Settings', 'ninja-api-explorer'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Enable Logging', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="enable_logging" value="1" 
                                           <?php checked($settings['enable_logging'] ?? false); ?>>
                                    <?php _e('Log API test requests and responses', 'ninja-api-explorer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('When enabled, all API test requests will be logged for debugging purposes.', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Log Retention', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <input type="number" name="log_retention_days" value="<?php echo esc_attr($settings['log_retention_days'] ?? 30); ?>" 
                                       min="1" max="365" class="regular-text">
                                <p class="description">
                                    <?php _e('How many days to keep log entries (1-365 days).', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="settings-section">
                    <h2><?php _e('Security Settings', 'ninja-api-explorer'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Allowed IP Addresses', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <textarea name="allowed_ips" rows="3" class="large-text" 
                                          placeholder="192.168.1.1&#10;10.0.0.0/8&#10;172.16.0.0/12"><?php echo esc_textarea($settings['allowed_ips'] ?? ''); ?></textarea>
                                <p class="description">
                                    <?php _e('One IP address or CIDR range per line. Leave empty to allow all IPs.', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Rate Limiting', 'ninja-api-explorer'); ?>
                            </th>
                            <td>
                                <input type="number" name="rate_limit" value="<?php echo esc_attr($settings['rate_limit'] ?? 100); ?>" 
                                       min="1" max="10000" class="regular-text">
                                <p class="description">
                                    <?php _e('Maximum number of API test requests per hour per user.', 'ninja-api-explorer'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="settings-actions">
                    <?php submit_button(__('Save Settings', 'ninja-api-explorer')); ?>
                </div>
            </form>
        </div>
        
        <div class="settings-sidebar">
            <div class="settings-card">
                <h3><?php _e('Quick Actions', 'ninja-api-explorer'); ?></h3>
                <div class="quick-actions">
                    <button type="button" class="button button-secondary" id="clear-cache">
                        <?php _e('Clear Cache', 'ninja-api-explorer'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="clear-logs">
                        <?php _e('Clear Logs', 'ninja-api-explorer'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="export-settings">
                        <?php _e('Export Settings', 'ninja-api-explorer'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="import-settings">
                        <?php _e('Import Settings', 'ninja-api-explorer'); ?>
                    </button>
                </div>
            </div>
            
            <div class="settings-card">
                <h3><?php _e('System Information', 'ninja-api-explorer'); ?></h3>
                <div class="system-info">
                    <div class="info-row">
                        <span class="info-label"><?php _e('Plugin Version:', 'ninja-api-explorer'); ?></span>
                        <span class="info-value"><?php echo NINJA_API_EXPLORER_VERSION; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php _e('WordPress Version:', 'ninja-api-explorer'); ?></span>
                        <span class="info-value"><?php echo get_bloginfo('version'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php _e('PHP Version:', 'ninja-api-explorer'); ?></span>
                        <span class="info-value"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php _e('REST API URL:', 'ninja-api-explorer'); ?></span>
                        <span class="info-value">
                            <code><?php echo rest_url(); ?></code>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="settings-card">
                <h3><?php _e('Help & Support', 'ninja-api-explorer'); ?></h3>
                <div class="help-links">
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=ninja-api-documentation'); ?>" class="button button-primary">
                            <?php _e('View Documentation', 'ninja-api-explorer'); ?>
                        </a>
                    </p>
                    <p>
                        <a href="https://github.com/your-username/ninja-api-explorer/issues" target="_blank" class="button">
                            <?php _e('Report Bug', 'ninja-api-explorer'); ?>
                        </a>
                    </p>
                    <p>
                        <a href="https://github.com/your-username/ninja-api-explorer" target="_blank" class="button">
                            <?php _e('GitHub Repository', 'ninja-api-explorer'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Import Settings -->
<div id="import-settings-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Import Settings', 'ninja-api-explorer'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="import-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="settings-file"><?php _e('Select Settings File:', 'ninja-api-explorer'); ?></label>
                    <input type="file" id="settings-file" name="settings_file" accept=".json" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php _e('Import', 'ninja-api-explorer'); ?>
                    </button>
                    <button type="button" class="button modal-close">
                        <?php _e('Cancel', 'ninja-api-explorer'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.settings-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.settings-section h2 {
    margin-top: 0;
    color: #333;
    border-bottom: 1px solid #e1e5e9;
    padding-bottom: 10px;
}

.settings-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.settings-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.settings-card h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 1px solid #e1e5e9;
    padding-bottom: 10px;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.system-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
}

.info-label {
    font-weight: 600;
    color: #555;
}

.info-value {
    color: #333;
    font-family: monospace;
    font-size: 12px;
}

.help-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.help-links .button {
    width: 100%;
    text-align: center;
}

.settings-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e1e5e9;
}

@media (max-width: 768px) {
    .settings-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Clear Cache
    $('#clear-cache').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to clear the cache?', 'ninja-api-explorer'); ?>')) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ninja_api_clear_cache',
                    nonce: ninjaApiExplorer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Cache cleared successfully!', 'ninja-api-explorer'); ?>');
                    } else {
                        alert('<?php _e('Failed to clear cache', 'ninja-api-explorer'); ?>');
                    }
                }
            });
        }
    });
    
    // Clear Logs
    $('#clear-logs').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to clear all logs? This action cannot be undone.', 'ninja-api-explorer'); ?>')) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ninja_api_clear_logs',
                    nonce: ninjaApiExplorer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Logs cleared successfully!', 'ninja-api-explorer'); ?>');
                    } else {
                        alert('<?php _e('Failed to clear logs', 'ninja-api-explorer'); ?>');
                    }
                }
            });
        }
    });
    
    // Export Settings
    $('#export-settings').on('click', function() {
        const settings = {
            enable_api_testing: $('input[name="enable_api_testing"]').is(':checked'),
            default_timeout: $('input[name="default_timeout"]').val(),
            show_private_routes: $('input[name="show_private_routes"]').is(':checked'),
            cache_duration: $('input[name="cache_duration"]').val(),
            enable_logging: $('input[name="enable_logging"]').is(':checked'),
            log_retention_days: $('input[name="log_retention_days"]').val(),
            allowed_ips: $('textarea[name="allowed_ips"]').val(),
            rate_limit: $('input[name="rate_limit"]').val(),
            exported_at: new Date().toISOString(),
            version: '<?php echo NINJA_API_EXPLORER_VERSION; ?>'
        };
        
        const dataStr = JSON.stringify(settings, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        const url = URL.createObjectURL(dataBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'ninja-api-explorer-settings.json';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });
    
    // Import Settings
    $('#import-settings').on('click', function() {
        $('#import-settings-modal').show();
    });
    
    // Import Form
    $('#import-form').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = $('#settings-file')[0];
        const file = fileInput.files[0];
        
        if (!file) {
            alert('<?php _e('Please select a file', 'ninja-api-explorer'); ?>');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const settings = JSON.parse(e.target.result);
                
                // Validate file
                if (!settings.version || !settings.exported_at) {
                    alert('<?php _e('Invalid settings file format', 'ninja-api-explorer'); ?>');
                    return;
                }
                
                // Apply settings
                if (settings.enable_api_testing !== undefined) {
                    $('input[name="enable_api_testing"]').prop('checked', settings.enable_api_testing);
                }
                if (settings.default_timeout !== undefined) {
                    $('input[name="default_timeout"]').val(settings.default_timeout);
                }
                if (settings.show_private_routes !== undefined) {
                    $('input[name="show_private_routes"]').prop('checked', settings.show_private_routes);
                }
                if (settings.cache_duration !== undefined) {
                    $('input[name="cache_duration"]').val(settings.cache_duration);
                }
                if (settings.enable_logging !== undefined) {
                    $('input[name="enable_logging"]').prop('checked', settings.enable_logging);
                }
                if (settings.log_retention_days !== undefined) {
                    $('input[name="log_retention_days"]').val(settings.log_retention_days);
                }
                if (settings.allowed_ips !== undefined) {
                    $('textarea[name="allowed_ips"]').val(settings.allowed_ips);
                }
                if (settings.rate_limit !== undefined) {
                    $('input[name="rate_limit"]').val(settings.rate_limit);
                }
                
                $('#import-settings-modal').hide();
                alert('<?php _e('Settings imported successfully! Please save to apply changes.', 'ninja-api-explorer'); ?>');
                
            } catch (error) {
                alert('<?php _e('Error parsing settings file', 'ninja-api-explorer'); ?>');
            }
        };
        
        reader.readAsText(file);
    });
    
    // Modal close
    $('.modal-close').on('click', function() {
        $(this).closest('.modal').hide();
    });
    
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});
</script>
