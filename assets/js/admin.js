/**
 * Ninja API Explorer - Admin JavaScript
 * 
 * This file contains all the JavaScript functionality for the Ninja API Explorer plugin.
 * It handles tab navigation, filtering, modal management, API testing, and user interactions.
 * 
 * Features:
 * - Tab-based navigation between All Routes, Grouped Routes, and API Tester
 * - Real-time filtering and search functionality
 * - Dynamic modal creation and management
 * - API endpoint testing with full request/response handling
 * - Namespace collapsing and expanding
 * - Copy to clipboard functionality
 * - Responsive design support
 * 
 * @author Ninja API Explorer Team
 * @version 1.0.0
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Global variables and configuration for the API Explorer
     * This object stores the current state of filters, loading status, and other global data
     */
    let ApiExplorer = {
        CurrentFilters: {},  // Current active filters (namespace, method, public_only, search)
        IsLoading: false,    // Flag to prevent multiple simultaneous requests
        ActiveTab: 'routes', // Currently active tab (routes, grouped, test)
        OpenModals: []       // Array to track open modals for proper cleanup
    };
    
    /**
     * Initialize the plugin when the document is ready
     * This function sets up all event handlers, filters, and tabs
     */
    $(document).ready(function() {
        InitializeEventHandlers();
        InitializeFilters();
        InitializeTabs();
        InitializeModals();
        console.log('Ninja API Explorer initialized successfully');
    });
    
    /**
     * Initialize all event handlers for the plugin
     * This function binds events to various UI elements like buttons, forms, and inputs
     */
    function InitializeEventHandlers() {
        // Filter event handlers - bind to all filter inputs
        $('#namespace-filter, #method-filter, #public-only-filter, #search-filter')
            .on('change input', HandleFilterChange);
        
        // Clear filters button
        $('#clear-filters').on('click', ClearAllFilters);
        
        // Namespace toggle buttons for grouped routes
        $('.toggle-namespace').on('click', ToggleNamespace);
        
        // Route action buttons (Details, Test, Copy URL)
        $(document).on('click', '.view-details', HandleViewDetails);
        $(document).on('click', '.test-endpoint', HandleTestEndpoint);
        $(document).on('click', '.copy-url', HandleCopyUrl);
        
        // Modal management events
        $('.modal-close').on('click', CloseModal);
        $('.modal').on('click', function(e) {
            if (e.target === this) {
                CloseModal();
            }
        });
        
        // ESC key to close modals
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                CloseModal();
            }
        });
        
        // Copy to clipboard buttons
        $(document).on('click', '.copy-button', HandleCopyToClipboard);
        
        console.log('Event handlers initialized');
    }
    
    /**
     * Initialize filter system
     * Sets up the initial filter state and prepares for filtering operations
     */
    function InitializeFilters() {
        ApiExplorer.CurrentFilters = {
            namespace: '',      // Selected namespace filter
            method: '',         // Selected HTTP method filter
            public_only: false, // Public routes only filter
            search: ''          // Search term filter
        };
        console.log('Filters initialized');
    }
    
    /**
     * Initialize tab functionality
     * Sets up tab switching between different views (All Routes, Grouped Routes, API Tester)
     */
    function InitializeTabs() {
        // Tab switching functionality
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const TargetTab = $(this).data('tab');
            if (!TargetTab) {
                console.error('Tab target not found');
                return;
            }
            
            // Update active tab visual state
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show/hide tab content
            $('.tab-content').hide();
            $('#' + TargetTab + '-content').show();
            
            // Update active tab state
            ApiExplorer.ActiveTab = TargetTab;
            
            console.log('Switched to tab:', TargetTab);
        });
        
        // Set initial active tab
        $('.nav-tab:first').trigger('click');
    }
    
    /**
     * Initialize modal system
     * Sets up modal event handlers and prepares modal functionality
     */
    function InitializeModals() {
        // Prevent modal content clicks from closing the modal
        $(document).on('click', '.modal-content', function(e) {
            e.stopPropagation();
        });
        
        console.log('Modal system initialized');
    }
    
    /**
     * Handle filter changes
     * Called when any filter input changes, updates the current filter state and applies filters
     */
    function HandleFilterChange() {
        const NamespaceFilter = $('#namespace-filter').val();
        const MethodFilter = $('#method-filter').val();
        const PublicOnlyFilter = $('#public-only-filter').is(':checked');
        const SearchFilter = $('#search-filter').val();
        
        // Update current filters
        ApiExplorer.CurrentFilters = {
            namespace: NamespaceFilter,
            method: MethodFilter,
            public_only: PublicOnlyFilter,
            search: SearchFilter
        };
        
        // Apply filters to the current view
        ApplyCurrentFilters();
        
        console.log('Filters updated:', ApiExplorer.CurrentFilters);
    }
    
    /**
     * Apply current filters to the displayed content
     * Filters routes based on the current filter settings
     */
    function ApplyCurrentFilters() {
        const Filters = ApiExplorer.CurrentFilters;
        
        // Filter namespace sections
        $('.namespace-section').each(function() {
            const $Section = $(this);
            const SectionNamespace = $Section.data('namespace');
            let IsVisible = true;
            
            // Apply namespace filter
            if (Filters.namespace && SectionNamespace !== Filters.namespace) {
                IsVisible = false;
            }
            
            // Apply method filter
            if (Filters.method) {
                const HasMethod = $Section.find(`.method-${Filters.method.toLowerCase()}`).length > 0;
                if (!HasMethod) {
                    IsVisible = false;
                }
            }
            
            // Apply public only filter
            if (Filters.public_only) {
                const HasPublicRoute = $Section.find('.route-card[data-public="true"]').length > 0;
                if (!HasPublicRoute) {
                    IsVisible = false;
                }
            }
            
            // Apply search filter
            if (Filters.search) {
                const SearchQuery = Filters.search.toLowerCase();
                const MatchesSearch = $Section.find('.route-path, .route-description')
                    .filter(function() {
                        return $(this).text().toLowerCase().includes(SearchQuery);
                    }).length > 0;
                
                if (!MatchesSearch) {
                    IsVisible = false;
                }
            }
            
            // Show/hide section based on filter results
            if (IsVisible) {
                $Section.show();
            } else {
                $Section.hide();
            }
        });
        
        // Hide empty namespace sections
        $('.namespace-section').each(function() {
            const $Section = $(this);
            const VisibleRoutes = $Section.find('.route-card:visible').length;
            
            if (VisibleRoutes === 0) {
                $Section.hide();
            }
        });
    }
    
    /**
     * Clear all active filters
     * Resets all filter inputs and shows all content
     */
    function ClearAllFilters() {
        // Reset filter inputs
        $('#namespace-filter, #method-filter, #search-filter').val('');
        $('#public-only-filter').prop('checked', false);
        
        // Reset filter state
        ApiExplorer.CurrentFilters = {
            namespace: '',
            method: '',
            public_only: false,
            search: ''
        };
        
        // Show all sections and routes
        $('.namespace-section').show();
        $('.route-card').show();
        
        console.log('All filters cleared');
    }
    
    /**
     * Toggle namespace visibility
     * Expands or collapses a namespace section in grouped routes view
     */
    function ToggleNamespace(e) {
        e.preventDefault();
        
        const $Button = $(this);
        const TargetId = $Button.data('target');
        
        if (!TargetId) {
            console.error('Toggle target not found');
            return;
        }
        
        // Escape special characters in selector to prevent jQuery errors
        const EscapedTargetId = TargetId.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&');
        const $Routes = $(`#routes-${EscapedTargetId}`);
        const $Icon = $Button.find('.dashicons');
        
        if ($Routes.length === 0) {
            console.error('Routes container not found:', TargetId);
            return;
        }
        
        // Toggle visibility with animation
        if ($Routes.is(':visible')) {
            $Routes.slideUp(300);
            $Icon.removeClass('dashicons-arrow-up-alt2')
                .addClass('dashicons-arrow-down-alt2');
        } else {
            $Routes.slideDown(300);
            $Icon.removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');
        }
        
        console.log('Toggled namespace:', TargetId);
    }
    
    /**
     * Handle view details button click
     * Opens a modal with detailed information about a specific route
     */
    function HandleViewDetails(e) {
        e.preventDefault();
        
        const $Button = $(this);
        const RouteName = $Button.data('route');
        
        if (!RouteName) {
            console.error('Route name not found');
            ShowNotification('Route name not found', 'error');
            return;
        }
        
        ShowLoadingIndicator();
        
        // Request route details via AJAX
        $.ajax({
            url: ninjaApiExplorer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ninja_api_get_route_details',
                route_name: RouteName,
                nonce: ninjaApiExplorer.nonce
            },
            success: function(response) {
                HideLoadingIndicator();
                
                if (response.success) {
                    ShowRouteDetailsModal(response.data);
                } else {
                    ShowNotification('Failed to load route details', 'error');
                }
            },
            error: function(xhr) {
                HideLoadingIndicator();
                console.error('AJAX error:', xhr);
                ShowNotification('Network error occurred while loading route details', 'error');
            }
        });
    }
    
    /**
     * Handle test endpoint button click
     * Opens a modal for testing a specific API endpoint
     */
    function HandleTestEndpoint(e) {
        e.preventDefault();
        
        // Close any existing modals first
        CloseAllModals();
        
        const $Button = $(this);
        const RouteData = $Button.data('route');
        
        if (!RouteData) {
            console.error('Route data not found');
            ShowNotification('Route data not found', 'error');
            return;
        }
        
        ShowApiTestModal(RouteData);
    }
    
    /**
     * Handle copy URL button click
     * Copies the endpoint URL to clipboard
     */
    function HandleCopyUrl(e) {
        e.preventDefault();
        
        const $Button = $(this);
        const Url = $Button.data('url');
        
        if (!Url) {
            console.error('URL not found');
            ShowNotification('URL not found', 'error');
            return;
        }
        
        CopyToClipboard(Url).then(() => {
            ShowNotification('URL copied to clipboard!', 'success');
            $Button.addClass('copied').text('Copied!');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                $Button.removeClass('copied').text('Copy URL');
            }, 2000);
        }).catch(() => {
            ShowNotification('Failed to copy URL', 'error');
        });
    }
    
    /**
     * Handle copy to clipboard button click
     * Generic handler for copy buttons throughout the interface
     */
    function HandleCopyToClipboard(e) {
        e.preventDefault();
        
        const $Button = $(this);
        const CopyText = $Button.data('copy') || $Button.closest('.copy-container').find('.copy-content').text();
        
        if (!CopyText) {
            console.error('No text to copy');
            ShowNotification('No text to copy', 'error');
            return;
        }
        
        CopyToClipboard(CopyText).then(() => {
            ShowNotification('Copied to clipboard!', 'success');
            $Button.addClass('copied').text('Copied!');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                $Button.removeClass('copied').text('Copy');
            }, 2000);
        }).catch(() => {
            ShowNotification('Failed to copy', 'error');
        });
    }
    
    /**
     * Show route details modal
     * Creates and displays a modal with comprehensive route information
     */
    function ShowRouteDetailsModal(RouteData) {
        if (!RouteData) {
            console.error('No route data provided');
            ShowNotification('No route data available', 'error');
            return;
        }
        
        // Process methods data
        let MethodsDisplay = 'N/A';
        if (RouteData.methods) {
            if (typeof RouteData.methods === 'object') {
                MethodsDisplay = Object.keys(RouteData.methods).join(', ');
            } else {
                MethodsDisplay = RouteData.methods;
            }
        }
        
        // Process parameters data
        let ParametersHtml = '';
        if (RouteData.parameters && RouteData.parameters.length > 0) {
            ParametersHtml = `
                <div class="detail-section">
                    <h4>Parameters</h4>
                    <ul>
                        ${RouteData.parameters.map(param => `
                            <li>
                                <strong>${param.name}</strong> 
                                <span class="param-type">${param.type || 'string'}</span>
                                ${param.required ? '<span class="required">Required</span>' : ''}
                                ${param.description ? `<br><small>${param.description}</small>` : ''}
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }
        
        // Process example URL
        let ExampleUrlHtml = '';
        if (RouteData.example_url) {
            ExampleUrlHtml = `
                <div class="detail-section">
                    <h4>Example URL</h4>
                    <code class="example-url">${RouteData.example_url}</code>
                    <button type="button" class="copy-button" data-copy="${RouteData.example_url}">
                        Copy URL
                    </button>
                </div>
            `;
        }
        
        // Create modal content
        const ModalContent = `
            <div class="route-details-content">
                <div class="detail-section">
                    <h4>Route Information</h4>
                    <p><strong>Path:</strong> <code>${RouteData.name || RouteData.path || 'N/A'}</code></p>
                    <p><strong>Methods:</strong> ${MethodsDisplay}</p>
                    <p><strong>Public:</strong> ${RouteData.is_public ? 'Yes' : 'No'}</p>
                    ${RouteData.description ? `<p><strong>Description:</strong> ${RouteData.description}</p>` : ''}
                </div>
                
                ${ParametersHtml}
                
                ${ExampleUrlHtml}
                
                ${RouteData.namespace ? `
                <div class="detail-section">
                    <h4>Namespace</h4>
                    <code>${RouteData.namespace}</code>
                </div>
                ` : ''}
            </div>
        `;
        
        const Modal = CreateModal('Route Details', ModalContent);
        $('body').append(Modal);
        TrackModal(Modal);
    }
    
    /**
     * Show API test modal
     * Creates and displays a modal for testing API endpoints
     */
    function ShowApiTestModal(RouteData) {
        // Parse route data if it's a string
        let ParsedRouteData = RouteData;
        if (typeof RouteData === 'string') {
            try {
                ParsedRouteData = JSON.parse(RouteData);
            } catch (e) {
                ParsedRouteData = { example_url: RouteData };
            }
        }
        
        // Build full URL for testing
        let FullUrl = ninjaApiExplorer.restUrl;
        if (ParsedRouteData.example_url) {
            if (ParsedRouteData.example_url.startsWith('http')) {
                FullUrl = ParsedRouteData.example_url;
            } else {
                // If it's a relative path, combine with rest URL
                const BaseUrl = ninjaApiExplorer.restUrl.endsWith('/') ? 
                    ninjaApiExplorer.restUrl.slice(0, -1) : ninjaApiExplorer.restUrl;
                const RoutePath = ParsedRouteData.example_url.startsWith('/') ? 
                    ParsedRouteData.example_url : '/' + ParsedRouteData.example_url;
                FullUrl = BaseUrl + RoutePath;
            }
        }
        
        // Debug logging
        console.log('Route Data:', ParsedRouteData);
        console.log('Generated URL:', FullUrl);
        
        const ModalContent = `
            <form id="api-test-form">
                <div class="form-group">
                    <label for="test-url">URL</label>
                    <input type="url" id="test-url" class="form-control" 
                           value="${FullUrl}" required>
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
                    <button type="button" class="button button-secondary modal-close">Cancel</button>
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
        `;
        
        const Modal = CreateModal('Test API Endpoint', ModalContent);
        $('body').append(Modal);
        TrackModal(Modal);
        
        // Bind form submission handler
        Modal.find('#api-test-form').on('submit', HandleApiTestFormSubmission);
        
        // Show/hide body field based on method
        Modal.find('#test-method').on('change', function() {
            const Method = $(this).val();
            const $BodyGroup = Modal.find('#test-body').closest('.form-group');
            if (['POST', 'PUT', 'PATCH'].includes(Method)) {
                $BodyGroup.show();
            } else {
                $BodyGroup.hide();
            }
        });
    }
    
    /**
     * Handle API test form submission
     * Processes the form data and sends an AJAX request to test the endpoint
     */
    function HandleApiTestFormSubmission(e) {
        e.preventDefault();
        
        if (ApiExplorer.IsLoading) {
            console.log('Request already in progress, ignoring');
            return;
        }
        
        const $Form = $(this);
        const Url = $Form.find('#test-url').val();
        const Method = $Form.find('#test-method').val();
        const Body = $Form.find('#test-body').val();
        const Timeout = parseInt($Form.find('#test-timeout').val()) || 30;
        
        // Validate form data
        if (!Url) {
            ShowNotification('URL is required', 'error');
            return;
        }
        
        if (!Url.startsWith('http')) {
            ShowNotification('Please enter a valid URL starting with http', 'error');
            return;
        }
        
        // Show loading state
        ShowLoadingIndicator();
        ApiExplorer.IsLoading = true;
        
        // Disable form to prevent multiple submissions
        const $SubmitBtn = $Form.find('button[type="submit"]');
        $SubmitBtn.prop('disabled', true).text('Testing...');
        
        // Prepare request data
        const RequestData = {
            url: Url,
            method: Method,
            timeout: Timeout
        };
        
        // Add headers if any
        const Headers = GetHeadersFromForm($Form);
        if (Object.keys(Headers).length > 0) {
            RequestData.headers = Headers;
        }
        
        // Add body for POST/PUT/PATCH requests
        if (['POST', 'PUT', 'PATCH'].includes(Method) && Body.trim()) {
            RequestData.body = Body;
        }
        
        console.log('Sending test request:', RequestData);
        
        // Send AJAX request to our handler
        $.ajax({
            url: ninjaApiExplorer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ninja_api_test_endpoint',
                ...RequestData,
                nonce: ninjaApiExplorer.nonce
            },
            success: function(response) {
                HideLoadingIndicator();
                ApiExplorer.IsLoading = false;
                
                if (response.success) {
                    DisplayTestResult(response.data);
                    ShowNotification('Test completed successfully!', 'success');
                } else {
                    DisplayTestResult({
                        status_code: response.data.status_code || 500,
                        response_time: response.data.response_time || 0,
                        response_body: response.data.message || 'Unknown error'
                    });
                    ShowNotification('Test failed: ' + (response.data.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr) {
                HideLoadingIndicator();
                ApiExplorer.IsLoading = false;
                
                let ErrorMessage = 'Network error occurred';
                let StatusCode = 500;
                
                if (xhr.responseJSON && xhr.responseJSON.data) {
                    ErrorMessage = xhr.responseJSON.data.message || ErrorMessage;
                    StatusCode = xhr.responseJSON.data.status_code || StatusCode;
                }
                
                DisplayTestResult({
                    status_code: StatusCode,
                    response_time: 0,
                    response_body: ErrorMessage
                });
                
                ShowNotification(ErrorMessage, 'error');
            },
            complete: function() {
                $SubmitBtn.prop('disabled', false).text('Test Endpoint');
            }
        });
    }
    
    /**
     * Display test result in the modal
     * Shows the response status, time, and body in a formatted way
     */
    function DisplayTestResult(Result) {
        const $Response = $('#test-response');
        const $Status = $('#response-status');
        const $Time = $('#response-time');
        const $Body = $('#response-body');
        
        // Update status with appropriate styling
        $Status.removeClass().addClass('status-code');
        if (Result.status_code >= 200 && Result.status_code < 300) {
            $Status.addClass('status-success');
        } else if (Result.status_code >= 400) {
            $Status.addClass('status-danger');
        } else {
            $Status.addClass('status-warning');
        }
        $Status.text(`Status: ${Result.status_code}`);
        
        // Update response time
        $Time.text(`Response Time: ${Result.response_time}ms`);
        
        // Update response body with proper formatting
        let BodyText = Result.response_body;
        if (typeof BodyText === 'object') {
            BodyText = JSON.stringify(BodyText, null, 2);
        } else if (typeof BodyText === 'string') {
            // Try to parse as JSON for pretty formatting
            try {
                const Parsed = JSON.parse(BodyText);
                BodyText = JSON.stringify(Parsed, null, 2);
            } catch (e) {
                // Keep as string if not valid JSON
            }
        }
        
        $Body.html(`<pre>${EscapeHtml(BodyText)}</pre>`);
        
        // Show response section
        $Response.show();
        
        // Scroll to response section
        $Response[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    /**
     * Create a modal dialog
     * Creates a new modal with the specified title and content
     */
    function CreateModal(Title, Content) {
        const ModalHtml = `
            <div class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${Title}</h3>
                        <button type="button" class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${Content}
                    </div>
                </div>
            </div>
        `;
        
        const $Modal = $(ModalHtml);
        
        // Bind close events
        $Modal.find('.modal-close').on('click', function() {
            CloseModal($Modal);
        });
        
        $Modal.on('click', function(e) {
            if (e.target === this) {
                CloseModal($Modal);
            }
        });
        
        return $Modal;
    }
    
    /**
     * Track a modal for proper cleanup
     * Adds a modal to the tracking array
     */
    function TrackModal($Modal) {
        ApiExplorer.OpenModals.push($Modal);
    }
    
    /**
     * Close a specific modal or all modals
     * Removes the modal from DOM and tracking array
     */
    function CloseModal($Modal) {
        if ($Modal) {
            $Modal.remove();
            // Remove from tracking array
            const Index = ApiExplorer.OpenModals.indexOf($Modal);
            if (Index > -1) {
                ApiExplorer.OpenModals.splice(Index, 1);
            }
        } else {
            // Close all modals
            CloseAllModals();
        }
    }
    
    /**
     * Close all open modals
     * Removes all tracked modals from DOM
     */
    function CloseAllModals() {
        ApiExplorer.OpenModals.forEach(function($Modal) {
            $Modal.remove();
        });
        ApiExplorer.OpenModals = [];
    }
    
    /**
     * Copy text to clipboard
     * Uses the modern clipboard API with fallback for older browsers
     */
    function CopyToClipboard(Text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(Text);
        } else {
            // Fallback for older browsers
            const TextArea = document.createElement('textarea');
            TextArea.value = Text;
            TextArea.style.position = 'fixed';
            TextArea.style.left = '-999999px';
            TextArea.style.top = '-999999px';
            document.body.appendChild(TextArea);
            TextArea.focus();
            TextArea.select();
            
            return new Promise((resolve, reject) => {
                if (document.execCommand('copy')) {
                    resolve();
                } else {
                    reject();
                }
                document.body.removeChild(TextArea);
            });
        }
    }
    
    /**
     * Translate a message using WordPress translations
     * @param string Message
     * @return string
     */
    function TranslateMessage(Message) {
        // Get current language from WordPress
        const CurrentLang = ninjaApiExplorer.language || 'en_US';
        
        // If English, return as is
        if (CurrentLang === 'en_US') {
            return Message;
        }
        
        // Try to get translation from localized data
        if (ninjaApiExplorer.translations && ninjaApiExplorer.translations[CurrentLang]) {
            return ninjaApiExplorer.translations[CurrentLang][Message] || Message;
        }
        
        return Message;
    }
    
    /**
     * Show a notification message
     * Displays a temporary notification with the specified message and type
     */
    function ShowNotification(Message, Type = 'info') {
        const TranslatedMessage = TranslateMessage(Message);
        const NotificationHtml = `
            <div class="notice notice-${Type} is-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 999999; max-width: 400px;">
                <p>${TranslatedMessage}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        
        const $Notification = $(NotificationHtml);
        
        // Bind dismiss button
        $Notification.find('.notice-dismiss').on('click', function() {
            $Notification.fadeOut(300, function() {
                $Notification.remove();
            });
        });
        
        $('body').append($Notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $Notification.fadeOut(300, function() {
                $Notification.remove();
            });
        }, 5000);
    }
    
    /**
     * Show loading indicator
     * Displays a loading overlay to indicate ongoing operations
     */
    function ShowLoadingIndicator() {
        if ($('.loading').length === 0) {
            $('body').append('<div class="loading">Loading...</div>');
        }
        $('.loading').show();
    }
    
    /**
     * Hide loading indicator
     * Hides the loading overlay
     */
    function HideLoadingIndicator() {
        $('.loading').hide();
    }
    
    /**
     * Get headers from form inputs
     * Extracts header key-value pairs from form inputs
     */
    function GetHeadersFromForm($Form) {
        const Headers = {};
        
        // Check if we have header rows
        $Form.find('.header-row').each(function() {
            const $Row = $(this);
            const Key = $Row.find('input[name="header_key[]"]').val();
            const Value = $Row.find('input[name="header_value[]"]').val();
            if (Key && Value) {
                Headers[Key] = Value;
            }
        });
        
        // If no header rows found, try alternative selectors
        if (Object.keys(Headers).length === 0) {
            $Form.find('input[name*="header_key"]').each(function(index) {
                const Key = $(this).val();
                const $ValueInput = $Form.find('input[name*="header_value"]').eq(index);
                const Value = $ValueInput.val();
                if (Key && Value) {
                    Headers[Key] = Value;
                }
            });
        }
        
        return Headers;
    }
    
    /**
     * Escape HTML to prevent XSS attacks
     * Converts HTML special characters to entities
     */
    function EscapeHtml(Text) {
        const Div = document.createElement('div');
        Div.textContent = Text;
        return Div.innerHTML;
    }
    
})(jQuery);