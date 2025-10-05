<?php

/**
 * Main service for working with APIs
 */
class ApiService
{
    /**
     * REST Server instance
     * @var \WP_REST_Server
     */
    private $RestServer;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->RestServer = rest_get_server();
    }
    
    /**
     * دریافت تمام route های ثبت شده
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
     * پردازش داده‌های route
     * @param string $RouteName
     * @param array $RouteData
     * @return array
     */
    private function ProcessRouteData($RouteName, $RouteData)
    {
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
        
        // پردازش methods
        if (isset($RouteData['methods'])) {
            foreach ($RouteData['methods'] as $Method => $MethodData) {
                $ProcessedRoute['methods'][$Method] = $this->ProcessMethodData($Method, $MethodData);
            }
        }
        
        // استخراج parameters از pattern
        $ProcessedRoute['parameters'] = RouteHelper::ExtractParametersFromPattern($RouteName);
        
        // تولید example URL
        $ProcessedRoute['example_url'] = RouteHelper::GenerateExampleUrl($RouteName, $ProcessedRoute['namespace']);
        
        return $ProcessedRoute;
    }
    
    /**
     * پردازش داده‌های method
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
            'query_parameters' => [],
            'is_public' => false,
            'description' => ''
        ];
        
        // بررسی عمومی بودن method
        $PermissionCallback = $MethodData['permission_callback'] ?? null;
        $ProcessedMethod['is_public'] = ($PermissionCallback === '__return_true' || $PermissionCallback === null);
        
        // استخراج query parameters
        if (isset($MethodData['args']) && is_array($MethodData['args'])) {
            $ProcessedMethod['query_parameters'] = RouteHelper::ExtractQueryParameters(['args' => $MethodData['args']]);
        }
        
        return $ProcessedMethod;
    }
    
    /**
     * استخراج namespace از نام route
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
     * دریافت جزئیات یک route خاص
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
        
        // اضافه کردن اطلاعات بیشتر
        $Route['full_details'] = $this->GetFullRouteDetails($RouteName);
        $Route['test_data'] = $this->GenerateTestData($Route);
        
        return $Route;
    }
    
    /**
     * دریافت جزئیات کامل route از WordPress
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
     * تولید داده‌های تست برای route
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
        
        // تنظیم method پیش‌فرض
        if (!empty($Route['methods'])) {
            $TestData['method'] = array_keys($Route['methods'])[0];
        }
        
        // تولید body برای POST/PUT/PATCH
        if (in_array($TestData['method'], ['POST', 'PUT', 'PATCH'])) {
            $TestData['body'] = $this->GenerateRequestBody($Route);
        }
        
        return $TestData;
    }
    
    /**
     * تولید request body
     * @param array $Route
     * @return string
     */
    private function GenerateRequestBody($Route)
    {
        $BodyData = [];
        
        // استخراج query parameters برای تولید body
        foreach ($Route['methods'] as $Method => $MethodData) {
            if (isset($MethodData['query_parameters'])) {
                foreach ($MethodData['query_parameters'] as $Param) {
                    if (!$Param['required']) {
                        continue;
                    }
                    
                    $ParamName = $Param['name'];
                    $ParamType = $Param['type'] ?? 'string';
                    
                    // تولید مقدار مثال بر اساس نوع
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
     * گروه‌بندی routes بر اساس namespace
     * @return array
     */
    public function GetGroupedRoutes()
    {
        $Routes = $this->GetAllRegisteredRoutes();
        return RouteHelper::GroupRoutesByNamespace($Routes);
    }
    
    /**
     * فیلتر کردن routes بر اساس معیارهای مختلف
     * @param array $Filters
     * @return array
     */
    public function FilterRoutes($Filters = [])
    {
        $Routes = $this->GetAllRegisteredRoutes();
        
        // فیلتر بر اساس namespace
        if (!empty($Filters['namespace'])) {
            $Routes = array_filter($Routes, function($Route) use ($Filters) {
                return $Route['namespace'] === $Filters['namespace'];
            });
        }
        
        // فیلتر بر اساس method
        if (!empty($Filters['method'])) {
            $Routes = array_filter($Routes, function($Route) use ($Filters) {
                return isset($Route['methods'][$Filters['method']]);
            });
        }
        
        // فیلتر بر اساس public/private
        if (isset($Filters['public_only']) && $Filters['public_only']) {
            $Routes = array_filter($Routes, function($Route) {
                return $Route['is_public'];
            });
        }
        
        // فیلتر بر اساس جستجو
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
     * دریافت آمار routes
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
            'namespaces_count' => [],
            'total_endpoints' => 0
        ];
        
        foreach ($Routes as $Route) {
            // شمارش public/private
            if ($Route['is_public']) {
                $Stats['public_routes']++;
            } else {
                $Stats['private_routes']++;
            }
            
            // شمارش methods
            foreach ($Route['methods'] as $Method => $MethodData) {
                if (!isset($Stats['methods_count'][$Method])) {
                    $Stats['methods_count'][$Method] = 0;
                }
                $Stats['methods_count'][$Method]++;
                $Stats['total_endpoints']++;
            }
            
            // شمارش namespaces
            $Namespace = $Route['namespace'];
            if (!isset($Stats['namespaces_count'][$Namespace])) {
                $Stats['namespaces_count'][$Namespace] = 0;
            }
            $Stats['namespaces_count'][$Namespace]++;
        }
        
        return $Stats;
    }
    
    /**
     * تست یک endpoint
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
        
        $Args = [
            'method' => $Method,
            'timeout' => $Timeout,
            'headers' => $Headers,
            'body' => $Body,
            'sslverify' => false
        ];
        
        $Response = wp_remote_request($Url, $Args);
        $EndTime = microtime(true);
        $ResponseTime = round(($EndTime - $StartTime) * 1000, 2);
        
        if (is_wp_error($Response)) {
            return [
                'success' => false,
                'error' => $Response->get_error_message(),
                'response_time' => $ResponseTime,
                'status_code' => 0
            ];
        }
        
        $StatusCode = wp_remote_retrieve_response_code($Response);
        $ResponseBody = wp_remote_retrieve_body($Response);
        $ResponseHeaders = wp_remote_retrieve_headers($Response);
        
        return [
            'success' => true,
            'status_code' => $StatusCode,
            'response_time' => $ResponseTime,
            'headers' => $ResponseHeaders->getAll(),
            'body' => $ResponseBody,
            'json' => $this->TryParseJson($ResponseBody)
        ];
    }
    
    /**
     * تلاش برای parse کردن JSON
     * @param string $JsonString
     * @return mixed|null
     */
    private function TryParseJson($JsonString)
    {
        $Decoded = json_decode($JsonString, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $Decoded : null;
    }
}
