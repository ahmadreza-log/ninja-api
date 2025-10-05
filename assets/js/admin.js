/**
 * Ninja API Explorer - Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Global variables
    let ApiExplorer = {
        currentFilters: {},
        isLoading: false
    };
    
    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        initializeEventHandlers();
        initializeFilters();
        initializeTabs();
    });
    
    /**
     * Initialize event handlers
     */
    function initializeEventHandlers() {
        // Filters
        $('#namespace-filter, #method-filter, #public-only-filter, #search-filter')
            .on('change input', handleFilterChange);
        
        $('#clear-filters').on('click', clearFilters);
        
        // Namespace toggle
        $('.toggle-namespace').on('click', toggleNamespace);
        
        // Route actions
        $(document).on('click', '.view-details', handleViewDetails);
        $(document).on('click', '.test-endpoint', handleTestEndpoint);
        $(document).on('click', '.copy-url', handleCopyUrl);
        
        // Modal events
        $('.modal-close').on('click', closeModal);
        $('.modal').on('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // ESC key to close modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Form submissions
        $('#api-test-form').on('submit', handleApiTest);
        
        // Copy buttons
        $(document).on('click', '.copy-button', handleCopy);
    }
    
    /**
     * Initialize filters
     */
    function initializeFilters() {
        ApiExplorer.currentFilters = {
            namespace: '',
            method: '',
            public_only: false,
            search: ''
        };
    }
    
    /**
     * Initialize tabs functionality
     */
    function initializeTabs() {
        // Tab switching
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).data('tab');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show/hide tab content
            $('.tab-content').hide();
            $('#' + target + '-content').show();
        });
        
        // Set initial active tab
        $('.nav-tab:first').trigger('click');
    }
    
    /**
     * Handle filter changes
     */
    function handleFilterChange() {
        const namespace = $('#namespace-filter').val();
        const method = $('#method-filter').val();
        const publicOnly = $('#public-only-filter').is(':checked');
        const search = $('#search-filter').val();
        
        ApiExplorer.currentFilters = {
            namespace: namespace,
            method: method,
            public_only: publicOnly,
            search: search
        };
        
        applyFilters();
    }
    
    /**
     * Apply filters
     */
    function applyFilters() {
        const filters = ApiExplorer.currentFilters;
        
        $('.namespace-section').each(function() {
            const $section = $(this);
            const namespace = $section.data('namespace');
            let visible = true;
            
            // Namespace filter
            if (filters.namespace && namespace !== filters.namespace) {
                visible = false;
            }
            
            // Method filter
            if (filters.method) {
                const hasMethod = $section.find(`.method-${filters.method.toLowerCase()}`).length > 0;
                if (!hasMethod) {
                    visible = false;
                }
            }
            
            // Public only filter
            if (filters.public_only) {
                const hasPublicRoute = $section.find('.route-card[data-public="true"]').length > 0;
                if (!hasPublicRoute) {
                    visible = false;
                }
            }
            
            // Search filter
            if (filters.search) {
                const searchQuery = filters.search.toLowerCase();
                const matchesSearch = $section.find('.route-path, .route-description')
                    .filter(function() {
                        return $(this).text().toLowerCase().includes(searchQuery);
                    }).length > 0;
                
                if (!matchesSearch) {
                    visible = false;
                }
            }
            
            // Show/hide section
            if (visible) {
                $section.show();
            } else {
                $section.hide();
            }
        });
        
        // Hide empty namespaces
        $('.namespace-section').each(function() {
            const $section = $(this);
            const visibleRoutes = $section.find('.route-card:visible').length;
            
            if (visibleRoutes === 0) {
                $section.hide();
            }
        });
    }
    
    /**
     * Clear all filters
     */
    function clearFilters() {
        $('#namespace-filter, #method-filter, #search-filter').val('');
        $('#public-only-filter').prop('checked', false);
        
        ApiExplorer.currentFilters = {
            namespace: '',
            method: '',
            public_only: false,
            search: ''
        };
        
        // Show all sections
        $('.namespace-section').show();
        $('.route-card').show();
    }
    
    /**
     * Toggle namespace
     */
    function toggleNamespace() {
        const $button = $(this);
        const targetId = $button.data('target');
        const $routes = $(`#routes-${targetId}`);
        
        if ($routes.is(':visible')) {
            $routes.hide();
            $button.find('.dashicons').removeClass('dashicons-arrow-up-alt2')
                .addClass('dashicons-arrow-down-alt2');
        } else {
            $routes.show();
            $button.find('.dashicons').removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');
        }
    }
    
    /**
     * Handle view details
     */
    function handleViewDetails(e) {
        e.preventDefault();
        const $button = $(this);
        const routeName = $button.data('route');
        
        showLoading();
        
        $.ajax({
            url: ninjaApiExplorer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ninja_api_get_route_details',
                route_name: routeName,
                nonce: ninjaApiExplorer.nonce
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showRouteDetailsModal(response.data);
                } else {
                    showNotification('Failed to load route details', 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotification('Network error occurred', 'error');
            }
        });
    }
    
    /**
     * Handle test endpoint
     */
    function handleTestEndpoint(e) {
        e.preventDefault();
        const $button = $(this);
        const routeData = $button.data('route');
        
        showApiTestModal(routeData);
    }
    
    /**
     * Handle copy URL
     */
    function handleCopyUrl(e) {
        e.preventDefault();
        const $button = $(this);
        const url = $button.data('url');
        
        copyToClipboard(url).then(() => {
            showNotification('URL copied to clipboard!', 'success');
            $button.addClass('copied').text('Copied!');
            
            setTimeout(() => {
                $button.removeClass('copied').text('Copy URL');
            }, 2000);
        }).catch(() => {
            showNotification('Failed to copy URL', 'error');
        });
    }
    
    /**
     * Handle API test form submission
     */
    function handleApiTest(e) {
        e.preventDefault();
        
        if (ApiExplorer.isLoading) {
            return;
        }
        
        const $form = $(this);
        const formData = {
            url: $form.find('#test-url').val(),
            method: $form.find('#test-method').val(),
            headers: getHeadersFromForm($form),
            body: $form.find('#test-body').val(),
            timeout: parseInt($form.find('#test-timeout').val()) || 30
        };
        
        // Validate form
        if (!formData.url) {
            showNotification('URL is required', 'error');
            return;
        }
        
        // Show loading
        showLoading();
        ApiExplorer.isLoading = true;
        
        // Disable form
        $form.find('button[type="submit"]').prop('disabled', true).text('Testing...');
        
        // Send request
        $.ajax({
            url: ninjaApiExplorer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ninja_api_test_endpoint',
                ...formData,
                nonce: ninjaApiExplorer.nonce
            },
            success: function(response) {
                hideLoading();
                ApiExplorer.isLoading = false;
                
                if (response.success) {
                    displayTestResult(response.data);
                    showNotification('Test completed successfully!', 'success');
                } else {
                    showNotification('Test failed: ' + (response.data.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                ApiExplorer.isLoading = false;
                
                let errorMessage = 'Network error occurred';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                
                showNotification(errorMessage, 'error');
            },
            complete: function() {
                $form.find('button[type="submit"]').prop('disabled', false).text('Test Endpoint');
            }
        });
    }
    
    /**
     * Handle copy to clipboard
     */
    function handleCopy(e) {
        e.preventDefault();
        const $button = $(this);
        const text = $button.data('copy') || $button.closest('.copy-container').find('.copy-content').text();
        
        copyToClipboard(text).then(() => {
            showNotification('Copied to clipboard!', 'success');
            $button.addClass('copied').text('Copied!');
            
            setTimeout(() => {
                $button.removeClass('copied').text('Copy');
            }, 2000);
        }).catch(() => {
            showNotification('Failed to copy', 'error');
        });
    }
    
    /**
     * Show route details modal
     */
    function showRouteDetailsModal(routeData) {
        const modal = createModal('Route Details', `
            <div class="route-details-content">
                <div class="detail-section">
                    <h4>Route Information</h4>
                    <p><strong>Path:</strong> <code>${routeData.path || 'N/A'}</code></p>
                    <p><strong>Methods:</strong> ${routeData.methods ? routeData.methods.join(', ') : 'N/A'}</p>
                    <p><strong>Public:</strong> ${routeData.is_public ? 'Yes' : 'No'}</p>
                </div>
                
                ${routeData.parameters && routeData.parameters.length > 0 ? `
                <div class="detail-section">
                    <h4>Parameters</h4>
                    <ul>
                        ${routeData.parameters.map(param => `
                            <li>
                                <strong>${param.name}</strong> 
                                <span class="param-type">${param.type || 'string'}</span>
                                ${param.required ? '<span class="required">Required</span>' : ''}
                                ${param.description ? `<br><small>${param.description}</small>` : ''}
                            </li>
                        `).join('')}
                    </ul>
                </div>
                ` : ''}
                
                ${routeData.example_url ? `
                <div class="detail-section">
                    <h4>Example URL</h4>
                    <code class="example-url">${routeData.example_url}</code>
                </div>
                ` : ''}
            </div>
        `);
        
        $('body').append(modal);
    }
    
    /**
     * Show API test modal
     */
    function showApiTestModal(routeData) {
        const modal = createModal('Test API Endpoint', `
            <form id="api-test-form">
                <div class="form-group">
                    <label for="test-url">URL</label>
                    <input type="url" id="test-url" class="form-control" 
                           value="${routeData.example_url || ''}" required>
                </div>
                
                <div class="form-group">
                    <label for="test-method">Method</label>
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
                    <label for="test-timeout">Timeout (seconds)</label>
                    <input type="number" id="test-timeout" class="form-control" 
                           value="30" min="1" max="300">
                </div>
                
                <div class="form-group">
                    <label for="test-body">Request Body (JSON)</label>
                    <textarea id="test-body" class="form-control" rows="6" 
                              placeholder='{"key": "value"}'></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button button-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="button button-primary">Test Endpoint</button>
                </div>
            </form>
            
            <div id="test-response" style="display: none;">
                <h4>Response</h4>
                <div class="response-info">
                    <span class="status-code" id="response-status"></span>
                    <span class="response-time" id="response-time"></span>
                </div>
                <div class="response-body" id="response-body"></div>
            </div>
        `);
        
        $('body').append(modal);
    }
    
    /**
     * Display test result
     */
    function displayTestResult(result) {
        const $response = $('#test-response');
        const $status = $('#response-status');
        const $time = $('#response-time');
        const $body = $('#response-body');
        
        // Update status
        $status.removeClass().addClass('status-code');
        if (result.status_code >= 200 && result.status_code < 300) {
            $status.addClass('status-success');
        } else if (result.status_code >= 400) {
            $status.addClass('status-danger');
        } else {
            $status.addClass('status-warning');
        }
        $status.text(result.status_code);
        
        // Update time
        $time.text(`${result.response_time}ms`);
        
        // Update body
        let bodyText = result.response_body;
        if (typeof bodyText === 'object') {
            bodyText = JSON.stringify(bodyText, null, 2);
        }
        $body.text(bodyText);
        
        // Show response
        $response.show();
    }
    
    /**
     * Create modal
     */
    function createModal(title, content) {
        const modal = $(`
            <div class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${title}</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            </div>
        `);
        
        // Bind close events
        modal.find('.modal-close').on('click', () => closeModal(modal));
        modal.on('click', function(e) {
            if (e.target === this) {
                closeModal(modal);
            }
        });
        
        return modal;
    }
    
    /**
     * Close modal
     */
    function closeModal(modal) {
        if (modal) {
            modal.remove();
        } else {
            $('.modal').remove();
        }
    }
    
    /**
     * Copy to clipboard
     */
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            return new Promise((resolve, reject) => {
                if (document.execCommand('copy')) {
                    resolve();
                } else {
                    reject();
                }
                document.body.removeChild(textArea);
            });
        }
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notice notice-${type} is-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 999999; max-width: 400px;">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);
        
        notification.find('.notice-dismiss').on('click', function() {
            notification.fadeOut(300, function() {
                notification.remove();
            });
        });
        
        $('body').append(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.fadeOut(300, function() {
                notification.remove();
            });
        }, 5000);
    }
    
    /**
     * Show loading
     */
    function showLoading() {
        if ($('.loading').length === 0) {
            $('body').append('<div class="loading">Loading...</div>');
        }
        $('.loading').show();
    }
    
    /**
     * Hide loading
     */
    function hideLoading() {
        $('.loading').hide();
    }
    
    /**
     * Get headers from form
     */
    function getHeadersFromForm($form) {
        const headers = {};
        $form.find('.header-row').each(function() {
            const key = $(this).find('.header-key').val();
            const value = $(this).find('.header-value').val();
            if (key && value) {
                headers[key] = value;
            }
        });
        return headers;
    }
    
})(jQuery);