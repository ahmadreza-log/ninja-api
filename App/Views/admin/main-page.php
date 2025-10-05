<?php
/**
 * Main page view for API Explorer
 */

// Prevent direct access
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
    </nav>
    
    <!-- Tab Contents -->
    <div class="tab-content" id="routes-content">
        <!-- Overall Statistics -->
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

    <!-- Filters -->
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

    <!-- Display Routes -->
    <div class="routes-container">
        <?php if (!empty($grouped_routes)): ?>
            <?php foreach ($grouped_routes as $namespace => $namespace_data): ?>
                <div class="namespace-section" data-namespace="<?php echo esc_attr($namespace); ?>">
                    <div class="namespace-header">
                        <h2 class="namespace-title">
                            <span class="dashicons dashicons-open-folder"></span>
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

    <!-- Modals are created dynamically by JavaScript -->
</div>

<!-- JavaScript functionality is handled by admin.js -->

    </div> <!-- End routes-content -->
    
