<?php
/**
 * Plugin Name: Ninja API Explorer
 * Plugin URI: https://github.com/your-username/ninja-api-explorer
 * Description: A powerful tool for exploring and testing WordPress REST APIs with Swagger-like interface
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ninja-api-explorer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NINJA_API_EXPLORER_VERSION', '1.0.0');
define('NINJA_API_EXPLORER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NINJA_API_EXPLORER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NINJA_API_EXPLORER_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('NINJA_API_EXPLORER_APP_PATH', NINJA_API_EXPLORER_PLUGIN_PATH . 'App/');

/**
 * Main plugin class - Bootstrap
 */
class NinjaApiExplorer
{
    /**
     * Singleton instance
     * @var NinjaApiExplorer|null
     */
    private static $Instance = null;
    
    /**
     * Main plugin file path
     * @var string
     */
    private $PluginFile;
    
    /**
     * Get singleton instance
     * @return NinjaApiExplorer
     */
    public static function GetInstance()
    {
        if (self::$Instance === null) {
            self::$Instance = new self();
        }
        return self::$Instance;
    }
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->PluginFile = __FILE__;
        $this->InitializePlugin();
    }
    
    /**
     * Initialize plugin
     */
    private function InitializePlugin()
    {
        // Load required files
        $this->LoadRequiredFiles();
        
        // Register WordPress hooks
        $this->RegisterHooks();
    }
    
    /**
     * Load required files
     */
    private function LoadRequiredFiles()
    {
        $requiredFiles = [
            'Helpers/RouteHelper.php',
            'Helpers/ViewHelper.php',
            'Services/ApiService.php',
            'Models/ApiRouteModel.php',
            'Models/ApiEndpointModel.php',
            'Controllers/BaseController.php',
            'Controllers/AdminController.php',
            'Controllers/ApiTestController.php'
        ];
        
        foreach ($requiredFiles as $file) {
            $filePath = NINJA_API_EXPLORER_APP_PATH . $file;
            if (file_exists($filePath)) {
                require_once $filePath;
            } else {
                error_log("Ninja API Explorer: File not found: $filePath");
            }
        }
        
        // Check if classes are loaded
        if (!class_exists('BaseController')) {
            error_log('Ninja API Explorer: BaseController class not found');
        }
        if (!class_exists('AdminController')) {
            error_log('Ninja API Explorer: AdminController class not found');
        }
    }
    
    
    /**
     * Register WordPress hooks
     */
    private function RegisterHooks()
    {
        // Plugin activation and deactivation
        register_activation_hook($this->PluginFile, [$this, 'ActivatePlugin']);
        register_deactivation_hook($this->PluginFile, [$this, 'DeactivatePlugin']);
        
        // WordPress hooks
        add_action('init', [$this, 'InitializePluginAfterWpInit']);
        add_action('admin_menu', [$this, 'RegisterAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'EnqueueAdminAssets']);
        add_action('wp_ajax_ninja_api_test_endpoint', [$this, 'HandleAjaxApiTest']);
        add_action('wp_ajax_ninja_api_get_route_details', [$this, 'HandleAjaxGetRouteDetails']);
    }
    
    /**
     * Initialize after WordPress
     */
    public function InitializePluginAfterWpInit()
    {
        // Load translation files
        load_plugin_textdomain(
            'ninja-api-explorer',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    /**
     * Register admin menu
     */
    public function RegisterAdminMenu()
    {
        add_menu_page(
            __('API Explorer', 'ninja-api-explorer'),
            __('API Explorer', 'ninja-api-explorer'),
            'manage_options',
            'ninja-api-explorer',
            [$this, 'RenderMainPage'],
            'dashicons-rest-api',
            30
        );
        
        add_submenu_page(
            'ninja-api-explorer',
            __('All APIs', 'ninja-api-explorer'),
            __('All APIs', 'ninja-api-explorer'),
            'manage_options',
            'ninja-api-explorer',
            [$this, 'RenderMainPage']
        );
        
        add_submenu_page(
            'ninja-api-explorer',
            __('API Documentation', 'ninja-api-explorer'),
            __('Documentation', 'ninja-api-explorer'),
            'manage_options',
            'ninja-api-documentation',
            [$this, 'RenderDocumentationPage']
        );
        
        add_submenu_page(
            'ninja-api-explorer',
            __('Settings', 'ninja-api-explorer'),
            __('Settings', 'ninja-api-explorer'),
            'manage_options',
            'ninja-api-settings',
            [$this, 'RenderSettingsPage']
        );
    }
    
    /**
     * Enqueue admin CSS and JS files
     */
    public function EnqueueAdminAssets($hook)
    {
        // Only load files on plugin pages
        if (strpos($hook, 'ninja-api') === false) {
            return;
        }
        
        wp_enqueue_style(
            'ninja-api-explorer-admin',
            NINJA_API_EXPLORER_PLUGIN_URL . 'assets/css/admin.css',
            [],
            NINJA_API_EXPLORER_VERSION
        );
        
        wp_enqueue_script(
            'ninja-api-explorer-admin',
            NINJA_API_EXPLORER_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            NINJA_API_EXPLORER_VERSION,
            true
        );
        
        // Pass PHP variables to JavaScript
        wp_localize_script('ninja-api-explorer-admin', 'ninjaApiExplorer', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ninja_api_explorer_nonce'),
            'restUrl' => rest_url(),
            'homeUrl' => home_url(),
            'language' => get_locale(),
            'translations' => $this->GetJavaScriptTranslations(),
            'strings' => [
                'loading' => __('Loading...', 'ninja-api-explorer'),
                'error' => __('Error occurred', 'ninja-api-explorer'),
                'success' => __('Request successful', 'ninja-api-explorer'),
                'copySuccess' => __('Copied to clipboard', 'ninja-api-explorer'),
                'copyError' => __('Failed to copy', 'ninja-api-explorer')
            ]
        ]);
    }
    
    /**
     * Render main page
     */
    public function RenderMainPage()
    {
        $ApiService = new ApiService();
        $Routes = $ApiService->GetAllRegisteredRoutes();
        $GroupedRoutes = $ApiService->GetGroupedRoutes();
        $Stats = $ApiService->GetRoutesStats();
        
        $ViewHelper = new ViewHelper();
        $ViewHelper->Render('admin/main-page', [
            'routes' => $Routes,
            'grouped_routes' => $GroupedRoutes,
            'stats' => $Stats,
            'totalRoutes' => count($Routes)
        ]);
    }
    
    /**
     * Render documentation page
     */
    public function RenderDocumentationPage()
    {
        $ApiService = new ApiService();
        $Routes = $ApiService->GetAllRegisteredRoutes();
        $OpenApiSpec = $this->GenerateOpenApiSpec($Routes);
        
        $ViewHelper = new ViewHelper();
        $ViewHelper->Render('admin/documentation-page', [
            'routes' => $Routes,
            'openapi_spec' => $OpenApiSpec
        ]);
    }
    
    /**
     * Render settings page
     */
    public function RenderSettingsPage()
    {
        if (isset($_POST['submit'])) {
            $this->SaveSettings();
        }
        
        $Settings = $this->GetSettings();
        
        $ViewHelper = new ViewHelper();
        $ViewHelper->Render('admin/settings-page', [
            'settings' => $Settings
        ]);
    }
    
    /**
     * Save settings
     */
    private function SaveSettings()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'ninja_api_settings')) {
            wp_die(__('Security check failed', 'ninja-api-explorer'));
        }
        
        $Settings = [
            'enable_api_testing' => isset($_POST['enable_api_testing']),
            'default_timeout' => intval($_POST['default_timeout']),
            'show_private_routes' => isset($_POST['show_private_routes']),
            'cache_duration' => intval($_POST['cache_duration'])
        ];
        
        update_option('ninja_api_explorer_settings', $Settings);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . 
                 __('Settings saved successfully!', 'ninja-api-explorer') . 
                 '</p></div>';
        });
    }
    
    /**
     * Get settings
     */
    private function GetSettings()
    {
        $DefaultSettings = [
            'enable_api_testing' => true,
            'default_timeout' => 30,
            'show_private_routes' => false,
            'cache_duration' => 3600
        ];
        
        return wp_parse_args(
            get_option('ninja_api_explorer_settings', []),
            $DefaultSettings
        );
    }
    
    /**
     * Handle AJAX API test
     */
    public function HandleAjaxApiTest()
    {
        check_ajax_referer('ninja_api_explorer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ninja-api-explorer'));
        }
        
        $ApiTestController = new ApiTestController();
        $ApiTestController->HandleApiTest();
    }
    
    /**
     * Handle AJAX get route details
     */
    public function HandleAjaxGetRouteDetails()
    {
        check_ajax_referer('ninja_api_explorer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ninja-api-explorer'));
        }
        
        $RouteName = sanitize_text_field($_POST['route_name']);
        $ApiService = new ApiService();
        $RouteDetails = $ApiService->GetRouteDetails($RouteName);
        
        wp_send_json_success($RouteDetails);
    }
    
    /**
     * Get JavaScript translations
     * @return array
     */
    private function GetJavaScriptTranslations()
    {
        $TranslationsFile = NINJA_API_EXPLORER_PLUGIN_PATH . 'languages/translations.json';
        
        if (!file_exists($TranslationsFile)) {
            return [];
        }
        
        $TranslationsJson = file_get_contents($TranslationsFile);
        $Translations = json_decode($TranslationsJson, true);
        
        return $Translations ?: [];
    }
    
    /**
     * Activate plugin
     */
    public function ActivatePlugin()
    {
        // Create required tables
        $this->CreateDatabaseTables();
        
        // Set default settings
        $this->SetDefaultSettings();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Deactivate plugin
     */
    public function DeactivatePlugin()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private function CreateDatabaseTables()
    {
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        
        $Sql = "CREATE TABLE IF NOT EXISTS $TableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            endpoint varchar(255) NOT NULL,
            method varchar(10) NOT NULL,
            status_code int(3) NOT NULL,
            response_time int(10) NOT NULL,
            user_id bigint(20),
            ip_address varchar(45),
            user_agent text,
            request_data text,
            response_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY endpoint (endpoint),
            KEY method (method),
            KEY status_code (status_code),
            KEY created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($Sql);
    }
    
    /**
     * Set default settings
     */
    private function SetDefaultSettings()
    {
        $DefaultSettings = [
            'enable_api_testing' => true,
            'default_timeout' => 30,
            'show_private_routes' => false,
            'cache_duration' => 3600
        ];
        
        add_option('ninja_api_explorer_settings', $DefaultSettings);
        add_option('ninja_api_explorer_version', NINJA_API_EXPLORER_VERSION);
    }
    
    /**
     * Generate OpenAPI specification
     * @param array $Routes
     * @return array
     */
    private function GenerateOpenApiSpec($Routes)
    {
        $OpenApiSpec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => get_bloginfo('name') . ' API',
                'description' => get_bloginfo('description'),
                'version' => '1.0.0',
                'contact' => [
                    'name' => get_bloginfo('name'),
                    'url' => home_url()
                ]
            ],
            'servers' => [
                [
                    'url' => rest_url(),
                    'description' => 'WordPress REST API Server'
                ]
            ],
            'paths' => [],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ]
                ]
            ]
        ];
        
        foreach ($Routes as $RouteName => $RouteData) {
            $Path = $this->FormatRouteForOpenApi($RouteName);
            
            if (!isset($OpenApiSpec['paths'][$Path])) {
                $OpenApiSpec['paths'][$Path] = [];
            }
            
            foreach ($RouteData['methods'] as $Method => $MethodData) {
                $MethodLower = strtolower($Method);
                
                $OperationSpec = [
                    'summary' => $this->GenerateOperationSummary($RouteName, $Method),
                    'description' => $this->GenerateOperationDescription($RouteName, $Method),
                    'parameters' => $this->GenerateOperationParameters($MethodData),
                    'responses' => $this->GenerateOperationResponses($Method),
                    'tags' => [$RouteData['namespace']]
                ];
                
                // Add request body for POST/PUT/PATCH
                if (in_array($Method, ['POST', 'PUT', 'PATCH'])) {
                    $OperationSpec['requestBody'] = $this->GenerateRequestBody($MethodData);
                }
                
                // Add security
                if (!$MethodData['is_public']) {
                    $OperationSpec['security'] = [
                        ['bearerAuth' => []]
                    ];
                }
                
                $OpenApiSpec['paths'][$Path][$MethodLower] = $OperationSpec;
            }
        }
        
        return $OpenApiSpec;
    }
    
    /**
     * Format route for OpenAPI
     * @param string $RouteName
     * @return string
     */
    private function FormatRouteForOpenApi($RouteName)
    {
        // Convert {param} to {param} for OpenAPI
        $FormattedRoute = preg_replace('/\{([^}]+)\}/', '{$1}', $RouteName);
        $FormattedRoute = preg_replace('/\(\?P<([^>]+)>[^)]+\)/', '{$1}', $FormattedRoute);
        
        return $FormattedRoute;
    }
    
    /**
     * Generate operation summary
     * @param string $RouteName
     * @param string $Method
     * @return string
     */
    private function GenerateOperationSummary($RouteName, $Method)
    {
        $MethodLower = strtolower($Method);
        $RouteParts = explode('/', trim($RouteName, '/'));
        $LastPart = end($RouteParts);
        
        return ucfirst($MethodLower) . ' ' . $LastPart;
    }
    
    /**
     * Generate operation description
     * @param string $RouteName
     * @param string $Method
     * @return string
     */
    private function GenerateOperationDescription($RouteName, $Method)
    {
        return sprintf(
            __('Execute %s request to %s endpoint', 'ninja-api-explorer'),
            $Method,
            $RouteName
        );
    }
    
    /**
     * Generate operation parameters
     * @param array $MethodData
     * @return array
     */
    private function GenerateOperationParameters($MethodData)
    {
        $Parameters = [];
        $QueryParameters = $MethodData['query_parameters'] ?? [];
        
        foreach ($QueryParameters as $Param) {
            $Parameters[] = [
                'name' => $Param['name'],
                'in' => 'query',
                'required' => $Param['required'],
                'schema' => [
                    'type' => $Param['type']
                ],
                'description' => $Param['description'] ?? ''
            ];
        }
        
        return $Parameters;
    }
    
    /**
     * Generate operation responses
     * @param string $Method
     * @return array
     */
    private function GenerateOperationResponses($Method)
    {
        $Responses = [
            '200' => [
                'description' => 'Successful response'
            ],
            '400' => [
                'description' => 'Bad Request'
            ],
            '401' => [
                'description' => 'Unauthorized'
            ],
            '403' => [
                'description' => 'Forbidden'
            ],
            '404' => [
                'description' => 'Not Found'
            ],
            '500' => [
                'description' => 'Internal Server Error'
            ]
        ];
        
        // Add specific responses for different methods
        switch ($Method) {
            case 'POST':
                $Responses['201'] = [
                    'description' => 'Created'
                ];
                break;
            case 'PUT':
            case 'PATCH':
                $Responses['200'] = [
                    'description' => 'Updated successfully'
                ];
                break;
            case 'DELETE':
                $Responses['204'] = [
                    'description' => 'Deleted successfully'
                ];
                break;
        }
        
        return $Responses;
    }
    
    /**
     * Generate request body
     * @param array $MethodData
     * @return array
     */
    private function GenerateRequestBody($MethodData)
    {
        $QueryParameters = $MethodData['query_parameters'] ?? [];
        $Properties = [];
        $Required = [];
        
        foreach ($QueryParameters as $Param) {
            $PropertySchema = [
                'type' => $Param['type']
            ];
            
            if (!empty($Param['description'])) {
                $PropertySchema['description'] = $Param['description'];
            }
            
            $Properties[$Param['name']] = $PropertySchema;
            
            if ($Param['required']) {
                $Required[] = $Param['name'];
            }
        }
        
        $Schema = [
            'type' => 'object',
            'properties' => $Properties
        ];
        
        if (!empty($Required)) {
            $Schema['required'] = $Required;
        }
        
        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => $Schema
                ]
            ]
        ];
    }
}

// Initialize plugin
NinjaApiExplorer::GetInstance();