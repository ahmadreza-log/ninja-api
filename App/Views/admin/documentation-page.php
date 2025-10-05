<?php
/**
 * Documentation page view for API Explorer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$ViewHelper = new ViewHelper();
?>

<div class="wrap ninja-api-explorer-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-book"></span>
        <?php _e('API Documentation', 'ninja-api-explorer'); ?>
    </h1>
    
    <div class="documentation-container">
        <div class="doc-sidebar">
            <div class="doc-nav">
                <h3><?php _e('Quick Navigation', 'ninja-api-explorer'); ?></h3>
                <ul class="doc-nav-list">
                    <li><a href="#overview"><?php _e('Overview', 'ninja-api-explorer'); ?></a></li>
                    <li><a href="#getting-started"><?php _e('Getting Started', 'ninja-api-explorer'); ?></a></li>
                    <li><a href="#endpoints"><?php _e('Available Endpoints', 'ninja-api-explorer'); ?></a></li>
                    <li><a href="#authentication"><?php _e('Authentication', 'ninja-api-explorer'); ?></a></li>
                    <li><a href="#testing"><?php _e('Testing APIs', 'ninja-api-explorer'); ?></a></li>
                    <li><a href="#examples"><?php _e('Examples', 'ninja-api-explorer'); ?></a></li>
                    <li><a href="#troubleshooting"><?php _e('Troubleshooting', 'ninja-api-explorer'); ?></a></li>
                </ul>
            </div>
            
            <div class="doc-stats">
                <h3><?php _e('API Statistics', 'ninja-api-explorer'); ?></h3>
                <div class="stats-summary">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Total Routes:', 'ninja-api-explorer'); ?></span>
                        <span class="stat-value"><?php echo count($routes); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Public Routes:', 'ninja-api-explorer'); ?></span>
                        <span class="stat-value"><?php echo count(array_filter($routes, function($r) { return $r['is_public']; })); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Namespaces:', 'ninja-api-explorer'); ?></span>
                        <span class="stat-value"><?php echo count(array_unique(array_column($routes, 'namespace'))); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="doc-actions">
                <h3><?php _e('Quick Actions', 'ninja-api-explorer'); ?></h3>
                <div class="action-buttons">
                    <button type="button" class="button button-primary" id="download-openapi">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download OpenAPI Spec', 'ninja-api-explorer'); ?>
                    </button>
                    <button type="button" class="button" id="copy-base-url">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php _e('Copy Base URL', 'ninja-api-explorer'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="doc-content">
            <section id="overview" class="doc-section">
                <h2><?php _e('Overview', 'ninja-api-explorer'); ?></h2>
                <p>
                    <?php _e('The WordPress REST API provides a simple, consistent interface for applications to interact with your site by sending and receiving data as JSON (JavaScript Object Notation) objects. This plugin helps you explore and test all available API endpoints.', 'ninja-api-explorer'); ?>
                </p>
                
                <div class="info-box">
                    <h4><?php _e('Base URL', 'ninja-api-explorer'); ?></h4>
                    <code class="base-url"><?php echo rest_url(); ?></code>
                </div>
                
                <div class="info-box">
                    <h4><?php _e('Content Type', 'ninja-api-explorer'); ?></h4>
                    <p><?php _e('All API requests and responses use JSON format with the Content-Type header set to', 'ninja-api-explorer'); ?> <code>application/json</code></p>
                </div>
            </section>
            
            <section id="getting-started" class="doc-section">
                <h2><?php _e('Getting Started', 'ninja-api-explorer'); ?></h2>
                
                <h3><?php _e('Making Your First Request', 'ninja-api-explorer'); ?></h3>
                <p><?php _e('Here\'s how to make a simple GET request to retrieve site information:', 'ninja-api-explorer'); ?></p>
                
                <div class="code-example">
                    <div class="code-header">
                        <span class="method-badge method-get">GET</span>
                        <span class="code-url"><?php echo rest_url(); ?>wp/v2/</span>
                    </div>
                    <pre><code class="language-http">GET <?php echo rest_url(); ?>wp/v2/
Content-Type: application/json</code></pre>
                </div>
                
                <h3><?php _e('cURL Example', 'ninja-api-explorer'); ?></h3>
                <div class="code-example">
                    <pre><code class="language-bash">curl -X GET "<?php echo rest_url(); ?>wp/v2/" \
  -H "Content-Type: application/json"</code></pre>
                </div>
                
                <h3><?php _e('JavaScript Example', 'ninja-api-explorer'); ?></h3>
                <div class="code-example">
                    <pre><code class="language-javascript">fetch('<?php echo rest_url(); ?>wp/v2/')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));</code></pre>
                </div>
            </section>
            
            <section id="endpoints" class="doc-section">
                <h2><?php _e('Available Endpoints', 'ninja-api-explorer'); ?></h2>
                
                <div class="endpoints-summary">
                    <?php
                    $groupedRoutes = [];
                    foreach ($routes as $routeName => $routeData) {
                        $namespace = $routeData['namespace'];
                        if (!isset($groupedRoutes[$namespace])) {
                            $groupedRoutes[$namespace] = [];
                        }
                        $groupedRoutes[$namespace][] = $routeData;
                    }
                    ?>
                    
                    <?php foreach ($groupedRoutes as $namespace => $namespaceRoutes): ?>
                        <div class="namespace-doc">
                            <h3><?php echo esc_html($namespace); ?></h3>
                            <p class="namespace-description">
                                <?php echo sprintf(__('Contains %d endpoints', 'ninja-api-explorer'), count($namespaceRoutes)); ?>
                            </p>
                            
                            <div class="endpoints-list">
                                <?php foreach ($namespaceRoutes as $route): ?>
                                    <div class="endpoint-item">
                                        <div class="endpoint-header">
                                            <div class="endpoint-methods">
                                                <?php foreach ($route['methods'] as $method => $methodData): ?>
                                                    <span class="method-badge method-<?php echo strtolower($method); ?>">
                                                        <?php echo esc_html($method); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="endpoint-path"><?php echo esc_html($route['name']); ?></div>
                                            <div class="endpoint-actions">
                                                <button type="button" class="button button-small test-endpoint" 
                                                        data-route="<?php echo esc_attr($route['name']); ?>">
                                                    <?php _e('Test', 'ninja-api-explorer'); ?>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($route['parameters']) || !empty($route['methods'])): ?>
                                            <div class="endpoint-details">
                                                <?php if (!empty($route['parameters'])): ?>
                                                    <div class="parameters-section">
                                                        <h4><?php _e('Path Parameters:', 'ninja-api-explorer'); ?></h4>
                                                        <ul>
                                                            <?php foreach ($route['parameters'] as $param): ?>
                                                                <li>
                                                                    <code><?php echo esc_html($param['name']); ?></code>
                                                                    <span class="param-type">(<?php echo esc_html($param['type']); ?>)</span>
                                                                    <?php if (!empty($param['description'])): ?>
                                                                        - <?php echo esc_html($param['description']); ?>
                                                                    <?php endif; ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="example-section">
                                                    <h4><?php _e('Example URL:', 'ninja-api-explorer'); ?></h4>
                                                    <code class="example-url"><?php echo esc_html($route['example_url']); ?></code>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section id="authentication" class="doc-section">
                <h2><?php _e('Authentication', 'ninja-api-explorer'); ?></h2>
                
                <p><?php _e('WordPress REST API supports several authentication methods:', 'ninja-api-explorer'); ?></p>
                
                <h3><?php _e('1. Application Passwords (Recommended)', 'ninja-api-explorer'); ?></h3>
                <p><?php _e('Create an application password in your WordPress user profile and use it for authentication.', 'ninja-api-explorer'); ?></p>
                
                <div class="code-example">
                    <pre><code class="language-http">Authorization: Basic <?php echo base64_encode('username:application_password'); ?></code></pre>
                </div>
                
                <h3><?php _e('2. Cookie Authentication', 'ninja-api-explorer'); ?></h3>
                <p><?php _e('Use WordPress nonces for authenticated requests when logged in.', 'ninja-api-explorer'); ?></p>
                
                <div class="code-example">
                    <pre><code class="language-http">X-WP-Nonce: <?php echo wp_create_nonce('wp_rest'); ?></code></pre>
                </div>
                
                <h3><?php _e('3. OAuth 1.0a', 'ninja-api-explorer'); ?></h3>
                <p><?php _e('For third-party applications, OAuth 1.0a authentication is available through plugins.', 'ninja-api-explorer'); ?></p>
            </section>
            
            <section id="testing" class="doc-section">
                <h2><?php _e('Testing APIs', 'ninja-api-explorer'); ?></h2>
                
                <p><?php _e('You can test any API endpoint directly from this interface:', 'ninja-api-explorer'); ?></p>
                
                <ol>
                    <li><?php _e('Navigate to the main API Explorer page', 'ninja-api-explorer'); ?></li>
                    <li><?php _e('Find the endpoint you want to test', 'ninja-api-explorer'); ?></li>
                    <li><?php _e('Click the "Test" button next to the endpoint', 'ninja-api-explorer'); ?></li>
                    <li><?php _e('Modify the URL, method, headers, or body as needed', 'ninja-api-explorer'); ?></li>
                    <li><?php _e('Click "Send Request" to execute the test', 'ninja-api-explorer'); ?></li>
                </ol>
                
                <div class="info-box">
                    <h4><?php _e('Testing Tips', 'ninja-api-explorer'); ?></h4>
                    <ul>
                        <li><?php _e('Start with GET requests to explore data structure', 'ninja-api-explorer'); ?></li>
                        <li><?php _e('Use the browser\'s developer tools to inspect responses', 'ninja-api-explorer'); ?></li>
                        <li><?php _e('Check the WordPress REST API documentation for parameter details', 'ninja-api-explorer'); ?></li>
                    </ul>
                </div>
            </section>
            
            <section id="examples" class="doc-section">
                <h2><?php _e('Common Examples', 'ninja-api-explorer'); ?></h2>
                
                <h3><?php _e('Get All Posts', 'ninja-api-explorer'); ?></h3>
                <div class="code-example">
                    <div class="code-header">
                        <span class="method-badge method-get">GET</span>
                        <span class="code-url"><?php echo rest_url(); ?>wp/v2/posts</span>
                    </div>
                    <pre><code class="language-bash">curl -X GET "<?php echo rest_url(); ?>wp/v2/posts"</code></pre>
                </div>
                
                <h3><?php _e('Get a Specific Post', 'ninja-api-explorer'); ?></h3>
                <div class="code-example">
                    <div class="code-header">
                        <span class="method-badge method-get">GET</span>
                        <span class="code-url"><?php echo rest_url(); ?>wp/v2/posts/123</span>
                    </div>
                    <pre><code class="language-bash">curl -X GET "<?php echo rest_url(); ?>wp/v2/posts/123"</code></pre>
                </div>
                
                <h3><?php _e('Create a New Post', 'ninja-api-explorer'); ?></h3>
                <div class="code-example">
                    <div class="code-header">
                        <span class="method-badge method-post">POST</span>
                        <span class="code-url"><?php echo rest_url(); ?>wp/v2/posts</span>
                    </div>
                    <pre><code class="language-bash">curl -X POST "<?php echo rest_url(); ?>wp/v2/posts" \
  -H "Content-Type: application/json" \
  -H "Authorization: Basic <?php echo base64_encode('username:password'); ?>" \
  -d '{
    "title": "My New Post",
    "content": "This is the content of my new post.",
    "status": "publish"
  }'</code></pre>
                </div>
            </section>
            
            <section id="troubleshooting" class="doc-section">
                <h2><?php _e('Troubleshooting', 'ninja-api-explorer'); ?></h2>
                
                <h3><?php _e('Common Issues', 'ninja-api-explorer'); ?></h3>
                
                <div class="troubleshooting-item">
                    <h4><?php _e('404 Not Found', 'ninja-api-explorer'); ?></h4>
                    <p><?php _e('Make sure the endpoint URL is correct and the route is registered.', 'ninja-api-explorer'); ?></p>
                </div>
                
                <div class="troubleshooting-item">
                    <h4><?php _e('401 Unauthorized', 'ninja-api-explorer'); ?></h4>
                    <p><?php _e('Check your authentication credentials and make sure you have permission to access the endpoint.', 'ninja-api-explorer'); ?></p>
                </div>
                
                <div class="troubleshooting-item">
                    <h4><?php _e('403 Forbidden', 'ninja-api-explorer'); ?></h4>
                    <p><?php _e('You don\'t have permission to perform this action. Check your user capabilities.', 'ninja-api-explorer'); ?></p>
                </div>
                
                <div class="troubleshooting-item">
                    <h4><?php _e('500 Internal Server Error', 'ninja-api-explorer'); ?></h4>
                    <p><?php _e('Check the WordPress error logs for more details about the server error.', 'ninja-api-explorer'); ?></p>
                </div>
                
                <h3><?php _e('Debug Mode', 'ninja-api-explorer'); ?></h3>
                <p><?php _e('Enable WordPress debug mode to see detailed error messages:', 'ninja-api-explorer'); ?></p>
                
                <div class="code-example">
                    <pre><code class="language-php">// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);</code></pre>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.documentation-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    margin-top: 20px;
}

.doc-sidebar {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    height: fit-content;
    position: sticky;
    top: 20px;
}

.doc-nav h3,
.doc-stats h3,
.doc-actions h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 1px solid #e1e5e9;
    padding-bottom: 10px;
}

.doc-nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.doc-nav-list li {
    margin: 5px 0;
}

.doc-nav-list a {
    display: block;
    padding: 8px 12px;
    text-decoration: none;
    color: #555;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.doc-nav-list a:hover {
    background: #f0f0f0;
    color: #333;
}

.doc-stats {
    margin-top: 20px;
}

.stats-summary {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}

.stat-label {
    color: #666;
}

.stat-value {
    font-weight: bold;
    color: #007cba;
}

.doc-actions {
    margin-top: 20px;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.action-buttons .button {
    width: 100%;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.doc-content {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 30px;
}

.doc-section {
    margin-bottom: 40px;
    scroll-margin-top: 20px;
}

.doc-section h2 {
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.doc-section h3 {
    color: #555;
    margin-top: 30px;
    margin-bottom: 15px;
}

.doc-section h4 {
    color: #666;
    margin-top: 20px;
    margin-bottom: 10px;
}

.info-box {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    padding: 15px;
    margin: 15px 0;
}

.info-box h4 {
    margin-top: 0;
    color: #333;
}

.base-url {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 14px;
    display: inline-block;
    word-break: break-all;
}

.code-example {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    margin: 15px 0;
    overflow: hidden;
}

.code-header {
    background: #e9ecef;
    padding: 10px 15px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    align-items: center;
    gap: 10px;
}

.code-url {
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    color: #666;
}

.code-example pre {
    margin: 0;
    padding: 15px;
    background: transparent;
    overflow-x: auto;
}

.code-example code {
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    line-height: 1.5;
}

.endpoints-summary {
    margin-top: 20px;
}

.namespace-doc {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.namespace-doc:last-child {
    border-bottom: none;
}

.namespace-description {
    color: #666;
    font-style: italic;
    margin-bottom: 15px;
}

.endpoints-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.endpoint-item {
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    overflow: hidden;
}

.endpoint-header {
    background: #f8f9fa;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.endpoint-methods {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}

.endpoint-path {
    flex: 1;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 14px;
    color: #333;
}

.endpoint-actions {
    flex-shrink: 0;
}

.endpoint-details {
    padding: 15px;
    background: #fff;
}

.parameters-section,
.example-section {
    margin-bottom: 15px;
}

.parameters-section:last-child,
.example-section:last-child {
    margin-bottom: 0;
}

.parameters-section ul {
    margin: 10px 0;
    padding-left: 20px;
}

.parameters-section li {
    margin: 5px 0;
    font-size: 14px;
}

.parameters-section code {
    background: #f1f3f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}

.param-type {
    color: #007cba;
    font-weight: 600;
    margin-left: 5px;
}

.example-url {
    background: #f1f3f4;
    border: 1px solid #dadce0;
    border-radius: 4px;
    padding: 8px 12px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    color: #333;
    display: block;
    word-break: break-all;
}

.troubleshooting-item {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.troubleshooting-item:last-child {
    border-bottom: none;
}

.troubleshooting-item h4 {
    color: #dc3545;
    margin-top: 0;
}

@media (max-width: 1024px) {
    .documentation-container {
        grid-template-columns: 1fr;
    }
    
    .doc-sidebar {
        position: static;
        order: 2;
    }
    
    .doc-content {
        order: 1;
    }
}

@media (max-width: 768px) {
    .doc-content {
        padding: 20px;
    }
    
    .endpoint-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .endpoint-path {
        width: 100%;
    }
    
    .action-buttons {
        flex-direction: row;
        flex-wrap: wrap;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Smooth scrolling for navigation links
    $('.doc-nav-list a').on('click', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        const $target = $(target);
        
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - 20
            }, 500);
        }
    });
    
    // Download OpenAPI Spec
    $('#download-openapi').on('click', function() {
        const openApiSpec = <?php echo json_encode($openapi_spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;
        
        const dataStr = JSON.stringify(openApiSpec, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        const url = URL.createObjectURL(dataBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'wordpress-api-spec.json';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });
    
    // Copy Base URL
    $('#copy-base-url').on('click', function() {
        const baseUrl = '<?php echo rest_url(); ?>';
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(baseUrl).then(function() {
                alert('<?php _e('Base URL copied to clipboard!', 'ninja-api-explorer'); ?>');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = baseUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('<?php _e('Base URL copied to clipboard!', 'ninja-api-explorer'); ?>');
        }
    });
    
    // Test endpoint from documentation
    $('.test-endpoint').on('click', function() {
        const route = $(this).data('route');
        
        // Open the main page and trigger test modal
        const mainPageUrl = '<?php echo admin_url('admin.php?page=ninja-api-explorer'); ?>';
        window.open(mainPageUrl, '_blank');
    });
    
    // Highlight current section in navigation
    function updateNavigation() {
        const scrollPos = $(window).scrollTop();
        
        $('.doc-section').each(function() {
            const section = $(this);
            const sectionTop = section.offset().top - 50;
            const sectionHeight = section.outerHeight();
            const sectionId = section.attr('id');
            
            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                $('.doc-nav-list a').removeClass('active');
                $(`.doc-nav-list a[href="#${sectionId}"]`).addClass('active');
            }
        });
    }
    
    $(window).on('scroll', updateNavigation);
    updateNavigation(); // Initial call
});
</script>

<style>
.doc-nav-list a.active {
    background: #007cba;
    color: white;
}
</style>
