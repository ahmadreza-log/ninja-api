<?php

/**
 * Service class for API operations
 * Handles all REST API related functionality including route discovery, processing, and testing
 */
class ApiService
{
    private $RestServer;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->RestServer = rest_get_server();
    }
    
    /**
     * Get all registered REST API routes
     * @return array
     */
    public function GetAllRegisteredRoutes()
    {
        $Routes = $this->RestServer->get_routes();
        $ProcessedRoutes = [];
        
        foreach ($Routes as $RouteName => $RouteData) {
            $ProcessedRoutes[$RouteName] = $this->ProcessRouteData($RouteName, $RouteData);
        }
        
        return $ProcessedRoutes;
    }
    
    /**
     * Process route data and extract relevant information
     * @param string $RouteName
     * @param array $RouteData
     * @return array
     */
    private function ProcessRouteData($RouteName, $RouteData)
    {
        // Debug logs removed for production
        
        $ProcessedRoute = [
            'name' => $RouteName,
            'pattern' => $RouteName,
            'methods' => [],
            'parameters' => [],
            'query_parameters' => [],
            'description' => '',
            'is_public' => RouteHelper::IsPublicRoute($RouteData),
            'example_url' => '',
            'namespace' => $this->ExtractNamespace($RouteName)
        ];
        
        // Process HTTP methods - WordPress REST API stores methods in endpoints array
        if (is_array($RouteData)) {
            foreach ($RouteData as $Endpoint) {
                if (isset($Endpoint['methods']) && is_array($Endpoint['methods'])) {
                    foreach ($Endpoint['methods'] as $Method) {
                        $ProcessedRoute['methods'][$Method] = [
                            'name' => $Method,
                            'description' => $this->GetMethodDescription($Method)
                        ];
                    }
                }
            }
        }
        
        // If no methods found, add default methods
        if (empty($ProcessedRoute['methods'])) {
            $ProcessedRoute['methods'] = $this->GetDefaultMethodsForRoute($RouteName);
        }
        
        // Extract parameters from route pattern
        $ProcessedRoute['parameters'] = RouteHelper::ExtractParametersFromPattern($RouteName);
        
        // Generate example URL
        // Extract route pattern by removing namespace from the beginning
        $NamespacePrefix = $ProcessedRoute['namespace'] . '/';
        if (strpos($RouteName, $NamespacePrefix) === 0) {
            $RoutePattern = substr($RouteName, strlen($NamespacePrefix));
        } else {
            $RoutePattern = $RouteName;
        }
        $ProcessedRoute['example_url'] = RouteHelper::GenerateExampleUrl($RoutePattern, $ProcessedRoute['namespace']);
        
        return $ProcessedRoute;
    }
    
    /**
     * Process method data for a specific HTTP method
     * @param string $Method
     * @param array $MethodData
     * @return array
     */
    private function ProcessMethodData($Method, $MethodData)
    {
        $ProcessedMethod = [
            'method' => $Method,
            'callback' => $MethodData['callback'] ?? null,
            'permission_callback' => $MethodData['permission_callback'] ?? null,
            'args' => $MethodData['args'] ?? [],
            'is_public' => false,
            'query_parameters' => []
        ];
        
        // Check if method is public
        $ProcessedMethod['is_public'] = $this->IsMethodPublic($MethodData);
        
        // Extract query parameters
        $ProcessedMethod['query_parameters'] = RouteHelper::ExtractQueryParameters($MethodData);
        
        return $ProcessedMethod;
    }
    
    /**
     * Extract namespace from route name
     * @param string $RouteName
     * @return string
     */
    private function ExtractNamespace($RouteName)
    {
        $Parts = explode('/', trim($RouteName, '/'));
        
        if (count($Parts) >= 2) {
            return $Parts[0] . '/' . $Parts[1];
        }
        
        return 'unknown';
    }
    
    /**
     * Get detailed information for a specific route
     * @param string $RouteName
     * @return array|null
     */
    public function GetRouteDetails($RouteName)
    {
        $Routes = $this->GetAllRegisteredRoutes();
        
        if (!isset($Routes[$RouteName])) {
            return null;
        }
        
        $Route = $Routes[$RouteName];
        
        // Add additional information
        $Route['full_details'] = $this->GetFullRouteDetails($RouteName);
        $Route['test_data'] = $this->GenerateTestData($Route);
        
        return $Route;
    }
    
    /**
     * Get full route details from WordPress REST server
     * @param string $RouteName
     * @return array
     */
    private function GetFullRouteDetails($RouteName)
    {
        $Routes = $this->RestServer->get_routes();
        
        if (!isset($Routes[$RouteName])) {
            return [];
        }
        
        return $Routes[$RouteName];
    }
    
    /**
     * Generate test data for a route
     * @param array $Route
     * @return array
     */
    private function GenerateTestData($Route)
    {
        $TestData = [
            'url' => $Route['example_url'],
            'method' => 'GET',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => '',
            'parameters' => []
        ];
        
        // Set default method based on available methods
        if (!empty($Route['methods'])) {
            $TestData['method'] = array_keys($Route['methods'])[0];
        }
        
        // Generate body for POST/PUT/PATCH requests
        if (in_array($TestData['method'], ['POST', 'PUT', 'PATCH'])) {
            $TestData['body'] = $this->GenerateRequestBody($Route);
        }
        
        return $TestData;
    }
    
    /**
     * Generate request body based on route parameters
     * @param array $Route
     * @return string
     */
    private function GenerateRequestBody($Route)
    {
        $BodyData = [];
        
        // Extract query parameters for generating body
        foreach ($Route['methods'] as $Method => $MethodData) {
            if (isset($MethodData['query_parameters'])) {
                foreach ($MethodData['query_parameters'] as $Param) {
                    if (!$Param['required']) {
                        continue;
                    }
                    
                    $ParamName = $Param['name'];
                    $ParamType = $Param['type'] ?? 'string';
                    
                    // Generate example value based on type
                    switch ($ParamType) {
                        case 'integer':
                            $BodyData[$ParamName] = 123;
                            break;
                        case 'number':
                            $BodyData[$ParamName] = 123.45;
                            break;
                        case 'boolean':
                            $BodyData[$ParamName] = true;
                            break;
                        case 'array':
                            $BodyData[$ParamName] = ['item1', 'item2'];
                            break;
                        case 'object':
                            $BodyData[$ParamName] = ['key' => 'value'];
                            break;
                        default:
                            $BodyData[$ParamName] = 'example_value';
                    }
                }
            }
        }
        
        return json_encode($BodyData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Group routes by namespace
     * @return array
     */
    public function GetGroupedRoutes()
    {
        $Routes = $this->GetAllRegisteredRoutes();
        return RouteHelper::GroupRoutesByNamespace($Routes);
    }
    
    /**
     * Filter routes based on various criteria
     * @param array $Filters
     * @return array
     */
    public function FilterRoutes($Filters = [])
    {
        $Routes = $this->GetAllRegisteredRoutes();
        
        // Filter by namespace
        if (!empty($Filters['namespace'])) {
            $Routes = array_filter($Routes, function($Route) use ($Filters) {
                return $Route['namespace'] === $Filters['namespace'];
            });
        }
        
        // Filter by method
        if (!empty($Filters['method'])) {
            $Routes = array_filter($Routes, function($Route) use ($Filters) {
                return isset($Route['methods'][$Filters['method']]);
            });
        }
        
        // Filter by public/private
        if (isset($Filters['public_only']) && $Filters['public_only']) {
            $Routes = array_filter($Routes, function($Route) {
                return $Route['is_public'];
            });
        }
        
        // Filter by search term
        if (!empty($Filters['search'])) {
            $SearchTerm = strtolower($Filters['search']);
            $Routes = array_filter($Routes, function($Route) use ($SearchTerm) {
                return strpos(strtolower($Route['name']), $SearchTerm) !== false ||
                       strpos(strtolower($Route['description']), $SearchTerm) !== false;
            });
        }
        
        return $Routes;
    }
    
    /**
     * Get statistics about routes
     * @return array
     */
    public function GetRoutesStats()
    {
        $Routes = $this->GetAllRegisteredRoutes();
        $Stats = [
            'total_routes' => count($Routes),
            'public_routes' => 0,
            'private_routes' => 0,
            'methods_count' => [],
            'namespaces_count' => []
        ];
        
        foreach ($Routes as $Route) {
            // Count public/private routes
            if ($Route['is_public']) {
                $Stats['public_routes']++;
            } else {
                $Stats['private_routes']++;
            }
            
            // Count methods
            foreach ($Route['methods'] as $Method => $MethodData) {
                if (!isset($Stats['methods_count'][$Method])) {
                    $Stats['methods_count'][$Method] = 0;
                }
                $Stats['methods_count'][$Method]++;
            }
            
            // Count namespaces
            $Namespace = $Route['namespace'];
            if (!isset($Stats['namespaces_count'][$Namespace])) {
                $Stats['namespaces_count'][$Namespace] = 0;
            }
            $Stats['namespaces_count'][$Namespace]++;
        }
        
        return $Stats;
    }
    
    /**
     * Test an API endpoint
     * @param string $Url
     * @param string $Method
     * @param array $Headers
     * @param string $Body
     * @param int $Timeout
     * @return array
     */
    public function TestEndpoint($Url, $Method = 'GET', $Headers = [], $Body = '', $Timeout = 30)
    {
        $StartTime = microtime(true);
        
        // Debug logs removed for production
        
        // Prepare request arguments
        $Args = [
            'method' => strtoupper($Method),
            'timeout' => $Timeout,
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ], $Headers)
        ];
        
        // Add body for POST/PUT/PATCH requests
        if (in_array(strtoupper($Method), ['POST', 'PUT', 'PATCH']) && !empty($Body)) {
            $Args['body'] = $Body;
        }
        
        // Make the request
        $Response = wp_remote_request($Url, $Args);
        
        $EndTime = microtime(true);
        $ResponseTime = round(($EndTime - $StartTime) * 1000); // Convert to milliseconds
        
        // Process response
        if (is_wp_error($Response)) {
            return [
                'success' => false,
                'status_code' => 0,
                'response_time' => $ResponseTime,
                'response_body' => $Response->get_error_message(),
                'error' => true
            ];
        }
        
        $StatusCode = wp_remote_retrieve_response_code($Response);
        $ResponseBody = wp_remote_retrieve_body($Response);
        
        // Try to parse JSON response
        $ParsedBody = $this->TryParseJson($ResponseBody);
        
        return [
            'success' => $StatusCode >= 200 && $StatusCode < 300,
            'status_code' => $StatusCode,
            'response_time' => $ResponseTime,
            'response_body' => $ParsedBody,
            'headers' => wp_remote_retrieve_headers($Response)
        ];
    }
    
    /**
     * Try to parse JSON response
     * @param string $ResponseBody
     * @return mixed
     */
    private function TryParseJson($ResponseBody)
    {
        $Decoded = json_decode($ResponseBody, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $Decoded;
        }
        
        return $ResponseBody;
    }
    
    /**
     * Check if a method is public
     * @param array $MethodData
     * @return bool
     */
    private function IsMethodPublic($MethodData)
    {
        if (isset($MethodData['permission_callback'])) {
            $Callback = $MethodData['permission_callback'];
            
            // If permission_callback is '__return_true', it's public
            if ($Callback === '__return_true' || 
                (is_string($Callback) && $Callback === '__return_true')) {
                return true;
            }
            
            // If permission_callback is null, it's public
            if ($Callback === null) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Extract methods from WordPress REST API callbacks
     * @param array $RouteData
     * @return array
     */
    private function ExtractMethodsFromCallbacks($RouteData)
    {
        $Methods = [];
        
        // WordPress REST API stores methods in the endpoint callbacks
        if (isset($RouteData) && is_array($RouteData)) {
            foreach ($RouteData as $Endpoint) {
                if (isset($Endpoint['methods']) && is_array($Endpoint['methods'])) {
                    foreach ($Endpoint['methods'] as $Method) {
                        $Methods[$Method] = [
                            'name' => $Method,
                            'description' => $this->GetMethodDescription($Method)
                        ];
                    }
                }
            }
        }
        
        return $Methods;
    }
    
    /**
     * Get default methods for a route based on its name
     * @param string $RouteName
     * @return array
     */
    private function GetDefaultMethodsForRoute($RouteName)
    {
        $Methods = [];
        
        // Common WordPress REST API methods
        $CommonMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        
        // Add methods based on route patterns
        foreach ($CommonMethods as $Method) {
            $Methods[$Method] = [
                'name' => $Method,
                'description' => $this->GetMethodDescription($Method)
            ];
        }
        
        return $Methods;
    }
    
    /**
     * Get description for HTTP method
     * @param string $Method
     * @return string
     */
    private function GetMethodDescription($Method)
    {
        $Descriptions = [
            'GET' => 'Retrieve data',
            'POST' => 'Create new data',
            'PUT' => 'Update existing data',
            'PATCH' => 'Partially update data',
            'DELETE' => 'Delete data',
            'OPTIONS' => 'Get available options',
            'HEAD' => 'Get headers only'
        ];
        
        return $Descriptions[$Method] ?? 'HTTP ' . $Method . ' method';
    }
}