<?php

/**
 * Admin panel controller
 */
class AdminController extends BaseController
{
    /**
     * ApiService instance
     * @var ApiService
     */
    private $ApiService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ApiService = new ApiService();
    }
    
    /**
     * Display main page
     */
    public function Index()
    {
        if (!$this->CheckPermission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ninja-api-explorer'));
        }
        
        $Routes = $this->ApiService->GetAllRegisteredRoutes();
        $GroupedRoutes = $this->ApiService->GetGroupedRoutes();
        $Stats = $this->ApiService->GetRoutesStats();
        $Settings = $this->GetPluginSettings();
        
        $this->RenderView('admin/main-page', [
            'routes' => $Routes,
            'grouped_routes' => $GroupedRoutes,
            'stats' => $Stats,
            'settings' => $Settings,
            'total_routes' => count($Routes)
        ]);
    }
    
    /**
     * Display documentation page
     */
    public function Documentation()
    {
        if (!$this->CheckPermission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ninja-api-explorer'));
        }
        
        $Routes = $this->ApiService->GetAllRegisteredRoutes();
        $OpenApiSpec = $this->GenerateOpenApiSpec($Routes);
        
        $this->RenderView('admin/documentation-page', [
            'routes' => $Routes,
            'openapi_spec' => $OpenApiSpec
        ]);
    }
    
    /**
     * Display settings page
     */
    public function Settings()
    {
        if (!$this->CheckPermission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ninja-api-explorer'));
        }
        
        if (isset($_POST['submit'])) {
            $this->SaveSettings();
        }
        
        $Settings = $this->GetPluginSettings();
        
        $this->RenderView('admin/settings-page', [
            'settings' => $Settings
        ]);
    }
    
    /**
     * Save settings
     */
    private function SaveSettings()
    {
        if (!$this->VerifyNonce('ninja_api_settings', $_POST['_wpnonce'])) {
            wp_die(__('Security check failed', 'ninja-api-explorer'));
        }
        
        $Settings = [
            'enable_api_testing' => isset($_POST['enable_api_testing']),
            'default_timeout' => intval($_POST['default_timeout']),
            'show_private_routes' => isset($_POST['show_private_routes']),
            'cache_duration' => intval($_POST['cache_duration']),
            'enable_logging' => isset($_POST['enable_logging']),
            'log_retention_days' => intval($_POST['log_retention_days'])
        ];
        
        $this->SavePluginSettings($Settings);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . 
                 __('Settings saved successfully!', 'ninja-api-explorer') . 
                 '</p></div>';
        });
    }
    
    /**
     * Display route details
     */
    public function RouteDetails()
    {
        if (!$this->CheckPermission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ninja-api-explorer'));
        }
        
        $RouteName = sanitize_text_field($_GET['route'] ?? '');
        
        if (empty($RouteName)) {
            wp_die(__('Route name is required', 'ninja-api-explorer'));
        }
        
        $RouteDetails = $this->ApiService->GetRouteDetails($RouteName);
        
        if (!$RouteDetails) {
            wp_die(__('Route not found', 'ninja-api-explorer'));
        }
        
        $ApiRouteModel = new ApiRouteModel($RouteName, $RouteDetails);
        
        $this->RenderView('admin/route-details', [
            'route' => $RouteDetails,
            'route_model' => $ApiRouteModel
        ]);
    }
    
    /**
     * Filter routes
     */
    public function FilterRoutes()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        $Filters = [
            'namespace' => sanitize_text_field($_POST['namespace'] ?? ''),
            'method' => sanitize_text_field($_POST['method'] ?? ''),
            'public_only' => isset($_POST['public_only']),
            'search' => sanitize_text_field($_POST['search'] ?? '')
        ];
        
        $FilteredRoutes = $this->ApiService->FilterRoutes($Filters);
        
        $this->SendJsonSuccess([
            'routes' => $FilteredRoutes,
            'count' => count($FilteredRoutes)
        ]);
    }
    
    /**
     * Get route statistics
     */
    public function GetStats()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        $Stats = $this->ApiService->GetRoutesStats();
        
        $this->SendJsonSuccess($Stats);
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
    
    /**
     * Get request history
     */
    public function GetRequestHistory()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        $Limit = intval($_POST['limit'] ?? 50);
        $Offset = intval($_POST['offset'] ?? 0);
        
        $Results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $TableName ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $Limit,
            $Offset
        ), ARRAY_A);
        
        $this->SendJsonSuccess($Results);
    }
    
    /**
     * Clear request history
     */
    public function ClearRequestHistory()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        $Deleted = $wpdb->query("TRUNCATE TABLE $TableName");
        
        if ($Deleted !== false) {
            $this->SendJsonSuccess([
                'message' => __('Request history cleared successfully', 'ninja-api-explorer')
            ]);
        } else {
            $this->SendJsonError(__('Failed to clear request history', 'ninja-api-explorer'));
        }
    }
}