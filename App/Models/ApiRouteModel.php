<?php

/**
 * Model for managing API Routes
 * Represents a single REST API route with its methods, parameters, and metadata
 */
class ApiRouteModel
{
    /**
     * Route name/path
     * @var string
     */
    private $Name;
    
    /**
     * Route data including methods and callbacks
     * @var array
     */
    private $Data;
    
    /**
     * Constructor
     * @param string $Name
     * @param array $Data
     */
    public function __construct($Name, $Data = [])
    {
        $this->Name = $Name;
        $this->Data = $Data;
    }
    
    /**
     * Get route name
     * @return string
     */
    public function GetName()
    {
        return $this->Name;
    }
    
    /**
     * Get route data
     * @return array
     */
    public function GetData()
    {
        return $this->Data;
    }
    
    /**
     * Get available HTTP methods
     * @return array
     */
    public function GetMethods()
    {
        return isset($this->Data['methods']) ? array_keys($this->Data['methods']) : [];
    }
    
    /**
     * Check if route has specific method
     * @param string $Method
     * @return bool
     */
    public function HasMethod($Method)
    {
        return isset($this->Data['methods'][strtoupper($Method)]);
    }
    
    /**
     * Get method information
     * @param string $Method
     * @return array|null
     */
    public function GetMethodInfo($Method)
    {
        return $this->Data['methods'][strtoupper($Method)] ?? null;
    }
    
    /**
     * Get route parameters
     * @return array
     */
    public function GetParameters()
    {
        return $this->Data['parameters'] ?? [];
    }
    
    /**
     * Get query parameters for specific method
     * @param string $Method
     * @return array
     */
    public function GetQueryParameters($Method = null)
    {
        if ($Method && isset($this->Data['methods'][$Method]['args'])) {
            return $this->Data['methods'][$Method]['args'];
        }
        
        // If no method specified, return all query parameters
        $AllParams = [];
        foreach ($this->Data['methods'] ?? [] as $MethodName => $MethodData) {
            if (isset($MethodData['args'])) {
                $AllParams = array_merge($AllParams, $MethodData['args']);
            }
        }
        
        return $AllParams;
    }
    
    /**
     * Get namespace
     * @return string
     */
    public function GetNamespace()
    {
        $Parts = explode('/', trim($this->Name, '/'));
        return count($Parts) >= 2 ? $Parts[0] . '/' . $Parts[1] : 'unknown';
    }
    
    /**
     * Get route description
     * @return string
     */
    public function GetDescription()
    {
        return $this->Data['description'] ?? '';
    }
    
    /**
     * Check if route is public
     * @return bool
     */
    public function IsPublic()
    {
        foreach ($this->Data['methods'] ?? [] as $Method => $MethodData) {
            if (isset($MethodData['permission_callback'])) {
                $Callback = $MethodData['permission_callback'];
                if ($Callback === '__return_true' || $Callback === null) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Get example URL
     * @return string
     */
    public function GetExampleUrl()
    {
        return $this->Data['example_url'] ?? '';
    }
    
    /**
     * Generate complete URL with parameters
     * @param array $Parameters
     * @return string
     */
    public function GenerateUrl($Parameters = [])
    {
        $BaseUrl = rest_url();
        $Url = $BaseUrl . $this->Name;
        
        // Replace parameters
        foreach ($Parameters as $Key => $Value) {
            $Url = str_replace('{' . $Key . '}', $Value, $Url);
        }
        
        return $Url;
    }
    
    /**
     * Generate test data for this route
     * @param string $Method
     * @return array
     */
    public function GenerateTestData($Method = 'GET')
    {
        $TestData = [
            'url' => $this->GenerateUrl(),
            'method' => strtoupper($Method),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => '',
            'parameters' => []
        ];
        
        // Set method
        if ($this->HasMethod($Method)) {
            $TestData['method'] = strtoupper($Method);
        }
        
        // Generate body for POST/PUT/PATCH requests
        if (in_array($TestData['method'], ['POST', 'PUT', 'PATCH'])) {
            $TestData['body'] = $this->GenerateRequestBody($Method);
        }
        
        // Generate query parameters
        $TestData['parameters'] = $this->GenerateQueryParameters($Method);
        
        return $TestData;
    }
    
    /**
     * Generate request body
     * @param string $Method
     * @return string
     */
    public function GenerateRequestBody($Method)
    {
        $BodyData = [];
        $MethodInfo = $this->GetMethodInfo($Method);
        
        if ($MethodInfo && isset($MethodInfo['args'])) {
            foreach ($MethodInfo['args'] as $ParamName => $ParamConfig) {
                if (isset($ParamConfig['required']) && $ParamConfig['required']) {
                    $BodyData[$ParamName] = $this->GenerateExampleValue($ParamConfig);
                }
            }
        }
        
        return json_encode($BodyData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Generate query parameters
     * @param string $Method
     * @return array
     */
    public function GenerateQueryParameters($Method)
    {
        $QueryParams = [];
        $MethodInfo = $this->GetMethodInfo($Method);
        
        if ($MethodInfo && isset($MethodInfo['args'])) {
            foreach ($MethodInfo['args'] as $ParamName => $ParamConfig) {
                if (isset($ParamConfig['required']) && !$ParamConfig['required']) {
                    // Only add simple parameters to query params
                    if (!isset($ParamConfig['type']) || in_array($ParamConfig['type'], ['string', 'integer', 'number', 'boolean'])) {
                        $QueryParams[$ParamName] = $this->GenerateExampleValue($ParamConfig);
                    }
                }
            }
        }
        
        return $QueryParams;
    }
    
    /**
     * Generate example value based on parameter type
     * @param array $ParamConfig
     * @return mixed
     */
    private function GenerateExampleValue($ParamConfig)
    {
        $Type = $ParamConfig['type'] ?? 'string';
        $Name = $ParamConfig['name'] ?? '';
        
        // Generate value based on parameter name
        if (strpos($Name, 'id') !== false) {
            return 123;
        } elseif (strpos($Name, 'slug') !== false) {
            return 'example-slug';
        } elseif (strpos($Name, 'type') !== false) {
            return 'post';
        } elseif (strpos($Name, 'status') !== false) {
            return 'publish';
        }
        
        // Generate value based on type
        switch ($Type) {
            case 'integer':
                return 123;
            case 'number':
                return 123.45;
            case 'boolean':
                return true;
            case 'array':
                return ['item1', 'item2'];
            case 'object':
                return ['key' => 'value'];
            default:
                return 'example_value';
        }
    }
    
    /**
     * Get list of available methods
     * @return array
     */
    public function GetAvailableMethods()
    {
        return array_keys($this->Data['methods'] ?? []);
    }
    
    /**
     * Check if route requires authentication
     * @param string $Method
     * @return bool
     */
    public function RequiresAuthentication($Method)
    {
        $MethodInfo = $this->GetMethodInfo($Method);
        
        if (!$MethodInfo) {
            return true;
        }
        
        $Callback = $MethodInfo['permission_callback'] ?? null;
        
        return $Callback !== '__return_true' && $Callback !== null;
    }
    
    /**
     * Get callback function
     * @param string $Method
     * @return callable|null
     */
    public function GetCallback($Method)
    {
        $MethodInfo = $this->GetMethodInfo($Method);
        return $MethodInfo['callback'] ?? null;
    }
    
    /**
     * Get permission callback
     * @param string $Method
     * @return callable|null
     */
    public function GetPermissionCallback($Method)
    {
        $MethodInfo = $this->GetMethodInfo($Method);
        return $MethodInfo['permission_callback'] ?? null;
    }
    
    /**
     * Convert to array
     * @return array
     */
    public function ToArray()
    {
        return [
            'name' => $this->Name,
            'data' => $this->Data,
            'namespace' => $this->GetNamespace(),
            'is_public' => $this->IsPublic(),
            'methods' => $this->GetMethods(),
            'description' => $this->GetDescription()
        ];
    }
}