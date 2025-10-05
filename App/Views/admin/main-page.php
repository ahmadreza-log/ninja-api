<?php
/**
 * صفحه اصلی API Explorer
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

$ViewHelper = new ViewHelper();
?>

<div class="wrap ninja-api-explorer-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-rest-api"></span>
        <?php _e('API Explorer', 'ninja-api-explorer'); ?>
    </h1>
    
    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper wp-clearfix">
        <a href="#" class="nav-tab nav-tab-active" data-tab="routes">
            <?php _e('All Routes', 'ninja-api-explorer'); ?>
        </a>
        <a href="#" class="nav-tab" data-tab="grouped">
            <?php _e('Grouped Routes', 'ninja-api-explorer'); ?>
        </a>
        <a href="#" class="nav-tab" data-tab="test">
            <?php _e('API Tester', 'ninja-api-explorer'); ?>
        </a>
    </nav>
    
    <!-- Tab Contents -->
    <div class="tab-content" id="routes-content">
        <!-- آمار کلی -->
        <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html($stats['total_routes'] ?? 0); ?></div>
            <div class="stat-label"><?php _e('Total Routes', 'ninja-api-explorer'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html($stats['public_routes'] ?? 0); ?></div>
            <div class="stat-label"><?php _e('Public Routes', 'ninja-api-explorer'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html($stats['total_endpoints'] ?? 0); ?></div>
            <div class="stat-label"><?php _e('Total Endpoints', 'ninja-api-explorer'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo esc_html(count($stats['namespaces_count'] ?? [])); ?></div>
            <div class="stat-label"><?php _e('Namespaces', 'ninja-api-explorer'); ?></div>
        </div>
    </div>

    <!-- فیلترها -->
    <div class="filters-section">
        <div class="filter-row">
            <div class="filter-group">
                <label for="namespace-filter"><?php _e('Namespace:', 'ninja-api-explorer'); ?></label>
                <select id="namespace-filter" class="filter-select">
                    <option value=""><?php _e('All Namespaces', 'ninja-api-explorer'); ?></option>
                    <?php foreach (($stats['namespaces_count'] ?? []) as $namespace => $count): ?>
                        <option value="<?php echo esc_attr($namespace); ?>">
                            <?php echo esc_html($namespace); ?> (<?php echo $count; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="method-filter"><?php _e('Method:', 'ninja-api-explorer'); ?></label>
                <select id="method-filter" class="filter-select">
                    <option value=""><?php _e('All Methods', 'ninja-api-explorer'); ?></option>
                    <?php foreach (($stats['methods_count'] ?? []) as $method => $count): ?>
                        <option value="<?php echo esc_attr($method); ?>">
                            <?php echo esc_html($method); ?> (<?php echo $count; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>
                    <input type="checkbox" id="public-only-filter">
                    <?php _e('Public Routes Only', 'ninja-api-explorer'); ?>
                </label>
            </div>
            
            <div class="filter-group">
                <input type="text" id="search-filter" placeholder="<?php _e('Search routes...', 'ninja-api-explorer'); ?>" class="filter-input">
            </div>
            
            <div class="filter-group">
                <button type="button" id="clear-filters" class="button">
                    <?php _e('Clear Filters', 'ninja-api-explorer'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- نمایش routes -->
    <div class="routes-container">
        <?php if (!empty($grouped_routes)): ?>
            <?php foreach ($grouped_routes as $namespace => $namespace_data): ?>
                <div class="namespace-section" data-namespace="<?php echo esc_attr($namespace); ?>">
                    <div class="namespace-header">
                        <h2 class="namespace-title">
                            <span class="dashicons dashicons-folder"></span>
                            <?php echo esc_html($namespace); ?>
                            <span class="route-count">(<?php echo count($namespace_data['routes']); ?> routes)</span>
                        </h2>
                        <button type="button" class="toggle-namespace" data-target="<?php echo esc_attr($namespace); ?>">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    
                    <div class="namespace-routes" id="routes-<?php echo esc_attr($namespace); ?>">
                        <?php foreach ($namespace_data['routes'] as $route_name => $route_data): ?>
                            <div class="route-card" data-route="<?php echo esc_attr($route_name); ?>" 
                                 data-methods="<?php echo esc_attr(implode(',', array_keys($route_data['methods']))); ?>"
                                 data-public="<?php echo $route_data['is_public'] ? 'true' : 'false'; ?>">
                                
                                <div class="route-header">
                                    <div class="route-methods">
                                        <?php foreach ($route_data['methods'] as $method => $method_data): ?>
                                            <span class="method-badge method-<?php echo strtolower($method); ?>">
                                                <?php echo esc_html($method); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="route-info">
                                        <div class="route-path"><?php echo esc_html($route_name); ?></div>
                                        <?php if (!empty($route_data['description'])): ?>
                                            <div class="route-description"><?php echo esc_html($route_data['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="route-actions">
                                        <button type="button" class="button button-small view-details" 
                                                data-route="<?php echo esc_attr($route_name); ?>">
                                            <?php _e('Details', 'ninja-api-explorer'); ?>
                                        </button>
                                        <button type="button" class="button button-small test-endpoint" 
                                                data-route="<?php echo esc_attr($route_name); ?>">
                                            <?php _e('Test', 'ninja-api-explorer'); ?>
                                        </button>
                                        <button type="button" class="button button-small copy-url" 
                                                data-url="<?php echo esc_attr($route_data['example_url']); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="route-details" style="display: none;">
                                    <div class="route-parameters">
                                        <?php if (!empty($route_data['parameters'])): ?>
                                            <h4><?php _e('Path Parameters:', 'ninja-api-explorer'); ?></h4>
                                            <ul>
                                                <?php foreach ($route_data['parameters'] as $param): ?>
                                                    <li>
                                                        <strong><?php echo esc_html($param['name']); ?></strong>
                                                        <span class="param-type">(<?php echo esc_html($param['type']); ?>)</span>
                                                        <?php if (!empty($param['description'])): ?>
                                                            - <?php echo esc_html($param['description']); ?>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        
                                        <?php foreach ($route_data['methods'] as $method => $method_data): ?>
                                            <?php if (!empty($method_data['query_parameters'])): ?>
                                                <h4><?php echo esc_html($method); ?> <?php _e('Query Parameters:', 'ninja-api-explorer'); ?></h4>
                                                <ul>
                                                    <?php foreach ($method_data['query_parameters'] as $param): ?>
                                                        <li>
                                                            <strong><?php echo esc_html($param['name']); ?></strong>
                                                            <span class="param-type">(<?php echo esc_html($param['type']); ?>)</span>
                                                            <?php if ($param['required']): ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($param['description'])): ?>
                                                                - <?php echo esc_html($param['description']); ?>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="route-example">
                                        <h4><?php _e('Example URL:', 'ninja-api-explorer'); ?></h4>
                                        <code class="example-url"><?php echo esc_html($route_data['example_url']); ?></code>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-routes">
                <p><?php _e('No routes found.', 'ninja-api-explorer'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal برای تست API -->
    <div id="api-test-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('Test API Endpoint', 'ninja-api-explorer'); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="api-test-form">
                    <div class="form-group">
                        <label for="test-url"><?php _e('URL:', 'ninja-api-explorer'); ?></label>
                        <input type="url" id="test-url" name="url" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="test-method"><?php _e('Method:', 'ninja-api-explorer'); ?></label>
                        <select id="test-method" name="method" class="form-control">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                            <option value="OPTIONS">OPTIONS</option>
                            <option value="HEAD">HEAD</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="headers-group">
                        <label><?php _e('Headers:', 'ninja-api-explorer'); ?></label>
                        <div id="headers-container">
                            <div class="header-row">
                                <input type="text" name="header_key[]" placeholder="<?php _e('Header Name', 'ninja-api-explorer'); ?>" class="form-control">
                                <input type="text" name="header_value[]" placeholder="<?php _e('Header Value', 'ninja-api-explorer'); ?>" class="form-control">
                                <button type="button" class="button remove-header"><?php _e('Remove', 'ninja-api-explorer'); ?></button>
                            </div>
                        </div>
                        <button type="button" id="add-header" class="button button-small">
                            <?php _e('Add Header', 'ninja-api-explorer'); ?>
                        </button>
                    </div>
                    
                    <div class="form-group" id="body-group" style="display: none;">
                        <label for="test-body"><?php _e('Request Body:', 'ninja-api-explorer'); ?></label>
                        <textarea id="test-body" name="body" rows="10" class="form-control" 
                                  placeholder='{"key": "value"}'></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="test-timeout"><?php _e('Timeout (seconds):', 'ninja-api-explorer'); ?></label>
                        <input type="number" id="test-timeout" name="timeout" value="30" min="1" max="300" class="form-control">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Send Request', 'ninja-api-explorer'); ?>
                        </button>
                        <button type="button" class="button modal-close">
                            <?php _e('Cancel', 'ninja-api-explorer'); ?>
                        </button>
                    </div>
                </form>
                
                <div id="test-response" style="display: none;">
                    <h4><?php _e('Response:', 'ninja-api-explorer'); ?></h4>
                    <div class="response-info">
                        <span class="status-code"></span>
                        <span class="response-time"></span>
                    </div>
                    <pre class="response-body"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal برای جزئیات Route -->
    <div id="route-details-modal" class="modal" style="display: none;">
        <div class="modal-content large">
            <div class="modal-header">
                <h3 id="route-details-title"><?php _e('Route Details', 'ninja-api-explorer'); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body" id="route-details-content">
                <!-- محتوا از طریق AJAX بارگذاری می‌شود -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // فیلترها
    $('#namespace-filter, #method-filter, #public-only-filter, #search-filter').on('change input', function() {
        filterRoutes();
    });
    
    // پاک کردن فیلترها
    $('#clear-filters').on('click', function() {
        $('#namespace-filter, #method-filter').val('');
        $('#public-only-filter').prop('checked', false);
        $('#search-filter').val('');
        filterRoutes();
    });
    
    // تابع فیلتر کردن routes
    function filterRoutes() {
        var namespace = $('#namespace-filter').val();
        var method = $('#method-filter').val();
        var publicOnly = $('#public-only-filter').is(':checked');
        var search = $('#search-filter').val().toLowerCase();
        
        $('.route-card').each(function() {
            var $card = $(this);
            var cardNamespace = $card.closest('.namespace-section').data('namespace');
            var cardMethods = $card.data('methods').toLowerCase();
            var cardPublic = $card.data('public');
            var cardPath = $card.find('.route-path').text().toLowerCase();
            var cardDescription = $card.find('.route-description').text().toLowerCase();
            
            var show = true;
            
            if (namespace && cardNamespace !== namespace) {
                show = false;
            }
            
            if (method && !cardMethods.includes(method.toLowerCase())) {
                show = false;
            }
            
            if (publicOnly && !cardPublic) {
                show = false;
            }
            
            if (search && !cardPath.includes(search) && !cardDescription.includes(search)) {
                show = false;
            }
            
            $card.toggle(show);
        });
        
        // مخفی کردن namespace های خالی
        $('.namespace-section').each(function() {
            var $section = $(this);
            var visibleRoutes = $section.find('.route-card:visible').length;
            $section.toggle(visibleRoutes > 0);
        });
    }
    
    // باز/بسته کردن namespace ها
    $('.toggle-namespace').on('click', function() {
        var target = $(this).data('target');
        var $routes = $('#routes-' + target);
        var $icon = $(this).find('.dashicons');
        
        $routes.slideToggle();
        $icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    });
    
    // نمایش جزئیات route
    $('.view-details').on('click', function() {
        var route = $(this).data('route');
        showRouteDetails(route);
    });
    
    // تست endpoint
    $('.test-endpoint').on('click', function() {
        var route = $(this).data('route');
        showApiTestModal(route);
    });
    
    // کپی کردن URL
    $('.copy-url').on('click', function() {
        var url = $(this).data('url');
        navigator.clipboard.writeText(url).then(function() {
            alert('<?php _e('URL copied to clipboard!', 'ninja-api-explorer'); ?>');
        });
    });
    
    // نمایش modal تست API
    function showApiTestModal(route) {
        $('#test-url').val('<?php echo rest_url(); ?>' + route);
        $('#api-test-modal').show();
    }
    
    // بستن modal ها
    $('.modal-close').on('click', function() {
        $(this).closest('.modal').hide();
    });
    
    // خارج شدن از modal با کلیک روی پس‌زمینه
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // تغییر method برای نمایش/مخفی کردن body
    $('#test-method').on('change', function() {
        var method = $(this).val();
        if (['POST', 'PUT', 'PATCH'].includes(method)) {
            $('#body-group').show();
        } else {
            $('#body-group').hide();
        }
    });
    
    // اضافه کردن header جدید
    $('#add-header').on('click', function() {
        var headerRow = '<div class="header-row">' +
            '<input type="text" name="header_key[]" placeholder="<?php _e('Header Name', 'ninja-api-explorer'); ?>" class="form-control">' +
            '<input type="text" name="header_value[]" placeholder="<?php _e('Header Value', 'ninja-api-explorer'); ?>" class="form-control">' +
            '<button type="button" class="button remove-header"><?php _e('Remove', 'ninja-api-explorer'); ?></button>' +
            '</div>';
        $('#headers-container').append(headerRow);
    });
    
    // حذف header
    $(document).on('click', '.remove-header', function() {
        $(this).closest('.header-row').remove();
    });
    
    // ارسال درخواست تست
    $('#api-test-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var headers = {};
        
        // جمع‌آوری headers
        $('input[name="header_key[]"]').each(function(index) {
            var key = $(this).val();
            var value = $('input[name="header_value[]"]').eq(index).val();
            if (key && value) {
                headers[key] = value;
            }
        });
        
        formData.append('action', 'ninja_api_test_endpoint');
        formData.append('nonce', ninjaApiExplorer.nonce);
        formData.append('headers', JSON.stringify(headers));
        
        $.ajax({
            url: ninjaApiExplorer.ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#test-response').hide();
                $('#api-test-form button[type="submit"]').prop('disabled', true).text('<?php _e('Sending...', 'ninja-api-explorer'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    displayTestResponse(response.data);
                } else {
                    alert('<?php _e('Error:', 'ninja-api-explorer'); ?> ' + response.data.message);
                }
            },
            error: function() {
                alert('<?php _e('Request failed', 'ninja-api-explorer'); ?>');
            },
            complete: function() {
                $('#api-test-form button[type="submit"]').prop('disabled', false).text('<?php _e('Send Request', 'ninja-api-explorer'); ?>');
            }
        });
    });
    
    // نمایش پاسخ تست
    function displayTestResponse(data) {
        var $response = $('#test-response');
        var $statusCode = $response.find('.status-code');
        var $responseTime = $response.find('.response-time');
        var $responseBody = $response.find('.response-body');
        
        $statusCode.text('Status: ' + data.status_code).removeClass().addClass('status-code status-' + getStatusClass(data.status_code));
        $responseTime.text('Response Time: ' + data.response_time + 'ms');
        
        try {
            var jsonResponse = JSON.parse(data.body);
            $responseBody.text(JSON.stringify(jsonResponse, null, 2));
        } catch (e) {
            $responseBody.text(data.body);
        }
        
        $response.show();
    }
    
    // دریافت کلاس وضعیت
    function getStatusClass(statusCode) {
        if (statusCode >= 200 && statusCode < 300) return 'success';
        if (statusCode >= 300 && statusCode < 400) return 'info';
        if (statusCode >= 400 && statusCode < 500) return 'warning';
        return 'danger';
    }
    
    // نمایش جزئیات route
    function showRouteDetails(route) {
        $.ajax({
            url: ninjaApiExplorer.ajaxUrl,
            method: 'POST',
            data: {
                action: 'ninja_api_get_route_details',
                nonce: ninjaApiExplorer.nonce,
                route_name: route
            },
            beforeSend: function() {
                $('#route-details-content').html('<p><?php _e('Loading...', 'ninja-api-explorer'); ?></p>');
                $('#route-details-modal').show();
            },
            success: function(response) {
                if (response.success) {
                    $('#route-details-title').text('Route: ' + route);
                    $('#route-details-content').html(response.data.html || 'No details available');
                } else {
                    $('#route-details-content').html('<p><?php _e('Error loading route details', 'ninja-api-explorer'); ?></p>');
                }
            },
            error: function() {
                $('#route-details-content').html('<p><?php _e('Error loading route details', 'ninja-api-explorer'); ?></p>');
            }
        });
    }
});
</script>

    </div> <!-- End routes-content -->
    
    <!-- Grouped Routes Tab -->
    <div class="tab-content" id="grouped-content" style="display: none;">
        <h2><?php _e('Routes by Namespace', 'ninja-api-explorer'); ?></h2>
        
        <?php if (!empty($grouped_routes)): ?>
            <?php foreach ($grouped_routes as $namespace => $namespace_data): ?>
                <div class="namespace-section" data-namespace="<?php echo esc_attr($namespace); ?>">
                    <div class="namespace-header">
                        <h3 class="namespace-title">
                            <span class="dashicons dashicons-category"></span>
                            <?php echo esc_html($namespace); ?>
                            <span class="route-count">(<?php echo count($namespace_data['routes']); ?> routes)</span>
                        </h3>
                        <button type="button" class="toggle-namespace" data-target="<?php echo esc_attr($namespace); ?>">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    
                    <div class="namespace-routes" id="routes-<?php echo esc_attr($namespace); ?>">
                        <?php foreach ($namespace_data['routes'] as $route_name => $route_data): ?>
                            <div class="route-card" data-public="<?php echo $route_data['is_public'] ? 'true' : 'false'; ?>">
                                <div class="route-header">
                                    <div class="route-methods">
                                        <?php foreach ($route_data['methods'] as $method): ?>
                                            <span class="method-badge method-<?php echo strtolower($method); ?>">
                                                <?php echo esc_html($method); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="route-info">
                                        <div class="route-path"><?php echo esc_html($route_name); ?></div>
                                        <?php if (!empty($route_data['description'])): ?>
                                            <div class="route-description"><?php echo esc_html($route_data['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="route-actions">
                                        <button class="button button-secondary view-details" data-route="<?php echo esc_attr($route_name); ?>">
                                            <?php _e('Details', 'ninja-api-explorer'); ?>
                                        </button>
                                        <button class="button button-primary test-endpoint" data-route="<?php echo esc_attr(json_encode($route_data)); ?>">
                                            <?php _e('Test', 'ninja-api-explorer'); ?>
                                        </button>
                                        <?php if (!empty($route_data['example_url'])): ?>
                                            <button class="button button-secondary copy-url" data-url="<?php echo esc_attr($route_data['example_url']); ?>">
                                                <?php _e('Copy URL', 'ninja-api-explorer'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-routes">
                <span class="dashicons dashicons-warning"></span>
                <h3><?php _e('No routes found', 'ninja-api-explorer'); ?></h3>
                <p><?php _e('No API routes are currently registered.', 'ninja-api-explorer'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- API Tester Tab -->
    <div class="tab-content" id="test-content" style="display: none;">
        <h2><?php _e('API Endpoint Tester', 'ninja-api-explorer'); ?></h2>
        
        <div class="filters-section">
            <form id="api-test-form">
                <div class="form-group">
                    <label for="test-url"><?php _e('Endpoint URL', 'ninja-api-explorer'); ?></label>
                    <input type="url" id="test-url" class="form-control" 
                           placeholder="<?php echo esc_attr(rest_url()); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="test-method"><?php _e('HTTP Method', 'ninja-api-explorer'); ?></label>
                    <select id="test-method" class="form-control">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                        <option value="OPTIONS">OPTIONS</option>
                        <option value="HEAD">HEAD</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="test-timeout"><?php _e('Timeout (seconds)', 'ninja-api-explorer'); ?></label>
                    <input type="number" id="test-timeout" class="form-control" 
                           value="30" min="1" max="300">
                </div>
                
                <div class="form-group">
                    <label for="test-body"><?php _e('Request Body (JSON)', 'ninja-api-explorer'); ?></label>
                    <textarea id="test-body" class="form-control" rows="6" 
                              placeholder='{"key": "value"}'></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php _e('Test Endpoint', 'ninja-api-explorer'); ?>
                    </button>
                </div>
            </form>
            
            <div id="test-response" style="display: none;">
                <h3><?php _e('Response', 'ninja-api-explorer'); ?></h3>
                <div class="response-info">
                    <span class="status-code" id="response-status"></span>
                    <span class="response-time" id="response-time"></span>
                </div>
                <div class="response-body" id="response-body"></div>
            </div>
        </div>
    </div>
