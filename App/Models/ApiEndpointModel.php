<?php

/**
 * Model for managing API Endpoints
 * Represents a single endpoint within a route with specific method and configuration
 */
class ApiEndpointModel
{
    /**
     * Endpoint name
     * @var string
     */
    private $Name;
    
    /**
     * HTTP method
     * @var string
     */
    private $Method;
    
    /**
     * Endpoint data
     * @var array
     */
    private $Data;
    
    /**
     * Constructor
     * @param string $Name
     * @param string $Method
     * @param array $Data
     */
    public function __construct($Name, $Method, $Data = [])
    {
        $this->Name = $Name;
        $this->Method = strtoupper($Method);
        $this->Data = $Data;
    }
    
    /**
     * Get endpoint name
     * @return string
     */
    public function GetName()
    {
        return $this->Name;
    }
    
    /**
     * Get HTTP method
     * @return string
     */
    public function GetMethod()
    {
        return $this->Method;
    }
    
    /**
     * Get endpoint data
     * @return array
     */
    public function GetData()
    {
        return $this->Data;
    }
    
    /**
     * Get callback function
     * @return callable|null
     */
    public function GetCallback()
    {
        return $this->Data['callback'] ?? null;
    }
    
    /**
     * Get permission callback
     * @return callable|null
     */
    public function GetPermissionCallback()
    {
        return $this->Data['permission_callback'] ?? null;
    }
    
    /**
     * Get arguments configuration
     * @return array
     */
    public function GetArgs()
    {
        return $this->Data['args'] ?? [];
    }
    
    /**
     * Get query parameters
     * @return array
     */
    public function GetQueryParameters()
    {
        $Parameters = [];
        $Args = $this->GetArgs();
        
        foreach ($Args as $ParamName => $ParamConfig) {
            if (is_array($ParamConfig)) {
                $Parameters[] = [
                    'name' => $ParamName,
                    'type' => $ParamConfig['type'] ?? 'string',
                    'required' => $ParamConfig['required'] ?? false,
                    'default' => $ParamConfig['default'] ?? null,
                    'description' => $ParamConfig['description'] ?? '',
                    'validation_callback' => $ParamConfig['validation_callback'] ?? null,
                    'sanitize_callback' => $ParamConfig['sanitize_callback'] ?? null
                ];
            }
        }
        
        return $Parameters;
    }
    
    /**
     * Get required parameters
     * @return array
     */
    public function GetRequiredParameters()
    {
        $Required = [];
        
        foreach ($this->GetQueryParameters() as $Param) {
            if ($Param['required']) {
                $Required[] = $Param;
            }
        }
        
        return $Required;
    }
    
    /**
     * Get optional parameters
     * @return array
     */
    public function GetOptionalParameters()
    {
        $Optional = [];
        
        foreach ($this->GetQueryParameters() as $Param) {
            if (!$Param['required']) {
                $Optional[] = $Param;
            }
        }
        
        return $Optional;
    }
    
    /**
     * Check if endpoint is public
     * @return bool
     */
    public function IsPublic()
    {
        $Callback = $this->GetPermissionCallback();
        return $Callback === '__return_true' || $Callback === null;
    }
    
    /**
     * Check if endpoint requires authentication
     * @return bool
     */
    public function RequiresAuthentication()
    {
        return !$this->IsPublic();
    }
    
    /**
     * Generate test data
     * @return array
     */
    public function GenerateTestData()
    {
        $TestData = [
            'url' => $this->GenerateTestUrl(),
            'method' => $this->Method,
            'headers' => $this->GetDefaultHeaders(),
            'body' => '',
            'parameters' => []
        ];
        
        // Generate body for POST/PUT/PATCH requests
        if (in_array($this->Method, ['POST', 'PUT', 'PATCH'])) {
            $TestData['body'] = $this->GenerateRequestBody();
        }
        
        // Generate query parameters
        $TestData['parameters'] = $this->GenerateQueryParameters();
        
        return $TestData;
    }
    
    /**
     * Generate test URL
     * @return string
     */
    public function GenerateTestUrl()
    {
        $BaseUrl = rest_url();
        $Url = $BaseUrl . $this->Name;
        
        // Replace parameters with example values
        $Parameters = $this->ExtractPathParameters();
        foreach ($Parameters as $Param) {
            $ExampleValue = $this->GenerateExampleValue($Param['name'], $Param['type'] ?? 'string');
            $Url = str_replace('{' . $Param['name'] . '}', $ExampleValue, $Url);
        }
        
        return $Url;
    }
    
    /**
     * Extract path parameters from route
     * @return array
     */
    private function ExtractPathParameters()
    {
        $Parameters = [];
        
        // Search for parameters in format {param} or (?P<param>[^/]+)
        preg_match_all('/\{([^}]+)\}|\(\?P<([^>]+)>[^)]+\)/', $this->Name, $Matches);
        
        if (!empty($Matches[1])) {
            foreach ($Matches[1] as $Parameter) {
                if (!empty($Parameter)) {
                    $Parameters[] = [
                        'name' => $Parameter,
                        'type' => 'string'
                    ];
                }
            }
        }
        
        if (!empty($Matches[2])) {
            foreach ($Matches[2] as $Parameter) {
                if (!empty($Parameter)) {
                    $Parameters[] = [
                        'name' => $Parameter,
                        'type' => 'string'
                    ];
                }
            }
        }
        
        return $Parameters;
    }
    
    /**
     * Generate request body
     * @return string
     */
    public function GenerateRequestBody()
    {
        $BodyData = [];
        $RequiredParams = $this->GetRequiredParameters();
        
        foreach ($RequiredParams as $Param) {
            $BodyData[$Param['name']] = $this->GenerateExampleValue($Param['name'], $Param['type']);
        }
        
        return json_encode($BodyData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Generate query parameters
     * @return array
     */
    public function GenerateQueryParameters()
    {
        $QueryParams = [];
        $OptionalParams = $this->GetOptionalParameters();
        
        foreach ($OptionalParams as $Param) {
            // Only add simple parameters to query params
            if (!isset($Param['type']) || in_array($Param['type'], ['string', 'integer', 'number', 'boolean'])) {
                $QueryParams[$Param['name']] = $this->GenerateExampleValue($Param['name'], $Param['type']);
            }
        }
        
        return $QueryParams;
    }
    
    /**
     * Get default headers
     * @return array
     */
    public function GetDefaultHeaders()
    {
        $Headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        
        // If requires authentication, add auth header
        if ($this->RequiresAuthentication()) {
            $Headers['Authorization'] = 'Bearer YOUR_TOKEN_HERE';
        }
        
        return $Headers;
    }
    
    /**
     * Generate example value based on parameter name and type
     * @param string $Name
     * @param string $Type
     * @return mixed
     */
    private function GenerateExampleValue($Name, $Type)
    {
        // Generate value based on parameter name
        if (strpos($Name, 'id') !== false) {
            return 123;
        } elseif (strpos($Name, 'slug') !== false) {
            return 'example-slug';
        } elseif (strpos($Name, 'type') !== false) {
            return 'post';
        } elseif (strpos($Name, 'status') !== false) {
            return 'publish';
        } elseif (strpos($Name, 'date') !== false) {
            return date('Y-m-d');
        } elseif (strpos($Name, 'email') !== false) {
            return 'user@example.com';
        } elseif (strpos($Name, 'url') !== false) {
            return 'https://example.com';
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
     * Get schema for validation
     * @return array
     */
    public function GetSchema()
    {
        $Schema = [
            'type' => 'object',
            'properties' => [],
            'required' => []
        ];
        
        foreach ($this->GetQueryParameters() as $Param) {
            $PropertySchema = [
                'type' => $Param['type'] ?? 'string',
                'description' => $Param['description'] ?? ''
            ];
            
            if (isset($Param['default'])) {
                $PropertySchema['default'] = $Param['default'];
            }
            
            $Schema['properties'][$Param['name']] = $PropertySchema;
            
            if ($Param['required']) {
                $Schema['required'][] = $Param['name'];
            }
        }
        
        return $Schema;
    }
    
    /**
     * Get OpenAPI specification
     * @return array
     */
    public function GetOpenApiSpec()
    {
        $Spec = [
            'summary' => $this->GetSummary(),
            'description' => $this->GetDescription(),
            'operationId' => $this->GenerateOperationId(),
            'tags' => [$this->GetNamespace()],
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Add parameters
        $Parameters = $this->GetQueryParameters();
        if (!empty($Parameters)) {
            $Spec['parameters'] = [];
            foreach ($Parameters as $Param) {
                $Spec['parameters'][] = [
                    'name' => $Param['name'],
                    'in' => 'query',
                    'required' => $Param['required'],
                    'schema' => [
                        'type' => $Param['type'] ?? 'string'
                    ],
                    'description' => $Param['description'] ?? ''
                ];
            }
        }
        
        // Add request body for POST/PUT/PATCH
        if (in_array($this->Method, ['POST', 'PUT', 'PATCH'])) {
            $Spec['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => $this->GetSchema()
                    ]
                ]
            ];
        }
        
        // Add security
        if ($this->RequiresAuthentication()) {
            $Spec['security'] = [
                ['bearerAuth' => []]
            ];
        }
        
        return $Spec;
    }
    
    /**
     * Generate operation ID for OpenAPI
     * @return string
     */
    private function GenerateOperationId()
    {
        $CleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $this->Name);
        $CleanName = trim($CleanName, '_');
        return strtolower($this->Method) . '_' . $CleanName;
    }
    
    /**
     * Get summary
     * @return string
     */
    private function GetSummary()
    {
        $Namespace = $this->GetNamespace();
        $Action = str_replace($Namespace . '/', '', $this->Name);
        return ucfirst($this->Method) . ' ' . $action;
    }
    
    /**
     * Get description
     * @return string
     */
    private function GetDescription()
    {
        return sprintf(
            __('Execute %s request to %s endpoint', 'ninja-api-explorer'),
            $this->Method,
            $this->Name
        );
    }
    
    /**
     * Get namespace
     * @return string
     */
    private function GetNamespace()
    {
        $Parts = explode('/', trim($this->Name, '/'));
        return count($Parts) >= 2 ? $Parts[0] . '/' . $Parts[1] : 'unknown';
    }
    
    /**
     * Convert to array
     * @return array
     */
    public function ToArray()
    {
        return [
            'name' => $this->Name,
            'method' => $this->Method,
            'data' => $this->Data,
            'is_public' => $this->IsPublic(),
            'requires_auth' => $this->RequiresAuthentication(),
            'parameters' => $this->GetQueryParameters(),
            'test_data' => $this->GenerateTestData()
        ];
    }
}