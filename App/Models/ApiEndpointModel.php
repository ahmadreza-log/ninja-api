<?php

/**
 * Model برای مدیریت Endpoint های API
 */
class ApiEndpointModel
{
    /**
     * نام endpoint
     * @var string
     */
    private $EndpointName;
    
    /**
     * method HTTP
     * @var string
     */
    private $Method;
    
    /**
     * داده‌های endpoint
     * @var array
     */
    private $EndpointData;
    
    /**
     * سازنده کلاس
     * @param string $EndpointName
     * @param string $Method
     * @param array $EndpointData
     */
    public function __construct($EndpointName, $Method, $EndpointData = [])
    {
        $this->EndpointName = $EndpointName;
        $this->Method = strtoupper($Method);
        $this->EndpointData = $EndpointData;
    }
    
    /**
     * دریافت نام endpoint
     * @return string
     */
    public function GetEndpointName()
    {
        return $this->EndpointName;
    }
    
    /**
     * دریافت method HTTP
     * @return string
     */
    public function GetMethod()
    {
        return $this->Method;
    }
    
    /**
     * دریافت داده‌های endpoint
     * @return array
     */
    public function GetEndpointData()
    {
        return $this->EndpointData;
    }
    
    /**
     * دریافت callback function
     * @return callable|null
     */
    public function GetCallback()
    {
        return $this->EndpointData['callback'] ?? null;
    }
    
    /**
     * دریافت permission callback
     * @return callable|null
     */
    public function GetPermissionCallback()
    {
        return $this->EndpointData['permission_callback'] ?? null;
    }
    
    /**
     * دریافت args
     * @return array
     */
    public function GetArgs()
    {
        return $this->EndpointData['args'] ?? [];
    }
    
    /**
     * دریافت query parameters
     * @return array
     */
    public function GetQueryParameters()
    {
        $Args = $this->GetArgs();
        $QueryParameters = [];
        
        foreach ($Args as $ParamName => $ParamConfig) {
            if (is_array($ParamConfig)) {
                $QueryParameters[] = [
                    'name' => $ParamName,
                    'type' => $ParamConfig['type'] ?? 'string',
                    'required' => $ParamConfig['required'] ?? false,
                    'default' => $ParamConfig['default'] ?? null,
                    'description' => $ParamConfig['description'] ?? '',
                    'validation_callback' => $ParamConfig['validation_callback'] ?? null,
                    'sanitize_callback' => $ParamConfig['sanitize_callback'] ?? null,
                    'format' => $ParamConfig['format'] ?? null,
                    'enum' => $ParamConfig['enum'] ?? null,
                    'minimum' => $ParamConfig['minimum'] ?? null,
                    'maximum' => $ParamConfig['maximum'] ?? null,
                    'minLength' => $ParamConfig['minLength'] ?? null,
                    'maxLength' => $ParamConfig['maxLength'] ?? null
                ];
            }
        }
        
        return $QueryParameters;
    }
    
    /**
     * دریافت required parameters
     * @return array
     */
    public function GetRequiredParameters()
    {
        $QueryParameters = $this->GetQueryParameters();
        
        return array_filter($QueryParameters, function($Param) {
            return $Param['required'];
        });
    }
    
    /**
     * دریافت optional parameters
     * @return array
     */
    public function GetOptionalParameters()
    {
        $QueryParameters = $this->GetQueryParameters();
        
        return array_filter($QueryParameters, function($Param) {
            return !$Param['required'];
        });
    }
    
    /**
     * بررسی عمومی بودن endpoint
     * @return bool
     */
    public function IsPublic()
    {
        $PermissionCallback = $this->GetPermissionCallback();
        
        return $PermissionCallback === '__return_true' || $PermissionCallback === null;
    }
    
    /**
     * بررسی نیاز به authentication
     * @return bool
     */
    public function RequiresAuthentication()
    {
        return !$this->IsPublic();
    }
    
    /**
     * تولید داده‌های تست
     * @return array
     */
    public function GenerateTestData()
    {
        $TestData = [
            'url' => $this->BuildTestUrl(),
            'method' => $this->Method,
            'headers' => $this->GetDefaultHeaders(),
            'body' => '',
            'query_params' => []
        ];
        
        // تولید body برای POST/PUT/PATCH
        if (in_array($this->Method, ['POST', 'PUT', 'PATCH'])) {
            $TestData['body'] = $this->GenerateRequestBody();
        }
        
        // تولید query parameters
        $TestData['query_params'] = $this->GenerateQueryParameters();
        
        return $TestData;
    }
    
    /**
     * تولید URL تست
     * @return string
     */
    private function BuildTestUrl()
    {
        $BaseUrl = rest_url();
        $Url = $BaseUrl . $this->EndpointName;
        
        // جایگزینی پارامترها با مقادیر مثال
        $Parameters = $this->ExtractPathParameters();
        foreach ($Parameters as $ParamName => $ParamData) {
            $ExampleValue = $this->GenerateExampleValue($ParamData['type'] ?? 'string', $ParamName);
            $Url = str_replace('{' . $ParamName . '}', $ExampleValue, $Url);
            $Url = str_replace('(?P<' . $ParamName . '>[^/]+)', $ExampleValue, $Url);
        }
        
        return $Url;
    }
    
    /**
     * استخراج path parameters
     * @return array
     */
    private function ExtractPathParameters()
    {
        $Parameters = [];
        
        // جستجوی پارامترهای {param} یا (?P<param>[^/]+)
        preg_match_all('/\{([^}]+)\}|\(\?P<([^>]+)>[^)]+\)/', $this->EndpointName, $Matches);
        
        if (!empty($Matches[1])) {
            foreach ($Matches[1] as $Parameter) {
                if (!empty($Parameter)) {
                    $Parameters[$Parameter] = [
                        'name' => $Parameter,
                        'type' => 'string'
                    ];
                }
            }
        }
        
        if (!empty($Matches[2])) {
            foreach ($Matches[2] as $Parameter) {
                if (!empty($Parameter)) {
                    $Parameters[$Parameter] = [
                        'name' => $Parameter,
                        'type' => 'string'
                    ];
                }
            }
        }
        
        return $Parameters;
    }
    
    /**
     * تولید request body
     * @return string
     */
    private function GenerateRequestBody()
    {
        $RequiredParameters = $this->GetRequiredParameters();
        $BodyData = [];
        
        foreach ($RequiredParameters as $Param) {
            $ParamName = $Param['name'];
            $ParamType = $Param['type'];
            
            $BodyData[$ParamName] = $this->GenerateExampleValue($ParamType, $ParamName);
        }
        
        return json_encode($BodyData, JSON_PRETTY_PRINT);
    }
    
    /**
     * تولید query parameters
     * @return array
     */
    private function GenerateQueryParameters()
    {
        $OptionalParameters = $this->GetOptionalParameters();
        $QueryParams = [];
        
        foreach ($OptionalParameters as $Param) {
            $ParamName = $Param['name'];
            $ParamType = $Param['type'];
            
            // فقط پارامترهای ساده را به query params اضافه کن
            if (!in_array($ParamType, ['array', 'object'])) {
                $QueryParams[$ParamName] = $this->GenerateExampleValue($ParamType, $ParamName);
            }
        }
        
        return $QueryParams;
    }
    
    /**
     * دریافت default headers
     * @return array
     */
    private function GetDefaultHeaders()
    {
        $Headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        
        // اگر نیاز به authentication دارد، header مربوطه را اضافه کن
        if ($this->RequiresAuthentication()) {
            $Headers['Authorization'] = 'Bearer YOUR_TOKEN_HERE';
        }
        
        return $Headers;
    }
    
    /**
     * تولید مقدار مثال بر اساس نوع
     * @param string $Type
     * @param string $Name
     * @return mixed
     */
    private function GenerateExampleValue($Type, $Name)
    {
        $NameLower = strtolower($Name);
        
        // تولید مقدار بر اساس نام پارامتر
        if (strpos($NameLower, 'id') !== false) {
            return 123;
        }
        
        if (strpos($NameLower, 'email') !== false) {
            return 'example@example.com';
        }
        
        if (strpos($NameLower, 'url') !== false) {
            return 'https://example.com';
        }
        
        if (strpos($NameLower, 'date') !== false) {
            return date('Y-m-d');
        }
        
        if (strpos($NameLower, 'time') !== false) {
            return date('H:i:s');
        }
        
        if (strpos($NameLower, 'slug') !== false) {
            return 'example-slug';
        }
        
        if (strpos($NameLower, 'password') !== false) {
            return 'password123';
        }
        
        if (strpos($NameLower, 'token') !== false) {
            return 'token123';
        }
        
        // تولید مقدار بر اساس نوع
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
     * دریافت schema برای validation
     * @return array
     */
    public function GetValidationSchema()
    {
        $Schema = [
            'type' => 'object',
            'properties' => [],
            'required' => []
        ];
        
        $QueryParameters = $this->GetQueryParameters();
        
        foreach ($QueryParameters as $Param) {
            $PropertySchema = [
                'type' => $Param['type']
            ];
            
            if (isset($Param['description'])) {
                $PropertySchema['description'] = $Param['description'];
            }
            
            if (isset($Param['minimum'])) {
                $PropertySchema['minimum'] = $Param['minimum'];
            }
            
            if (isset($Param['maximum'])) {
                $PropertySchema['maximum'] = $Param['maximum'];
            }
            
            if (isset($Param['minLength'])) {
                $PropertySchema['minLength'] = $Param['minLength'];
            }
            
            if (isset($Param['maxLength'])) {
                $PropertySchema['maxLength'] = $Param['maxLength'];
            }
            
            if (isset($Param['enum'])) {
                $PropertySchema['enum'] = $Param['enum'];
            }
            
            $Schema['properties'][$Param['name']] = $PropertySchema;
            
            if ($Param['required']) {
                $Schema['required'][] = $Param['name'];
            }
        }
        
        return $Schema;
    }
    
    /**
     * دریافت OpenAPI specification
     * @return array
     */
    public function GetOpenApiSpec()
    {
        $Spec = [
            'operationId' => $this->GenerateOperationId(),
            'summary' => $this->GetSummary(),
            'description' => $this->GetDescription(),
            'parameters' => [],
            'requestBody' => null,
            'responses' => [
                '200' => [
                    'description' => 'Successful response'
                ]
            ],
            'security' => []
        ];
        
        // اضافه کردن parameters
        $QueryParameters = $this->GetQueryParameters();
        foreach ($QueryParameters as $Param) {
            $Spec['parameters'][] = [
                'name' => $Param['name'],
                'in' => 'query',
                'required' => $Param['required'],
                'schema' => [
                    'type' => $Param['type']
                ],
                'description' => $Param['description'] ?? ''
            ];
        }
        
        // اضافه کردن request body برای POST/PUT/PATCH
        if (in_array($this->Method, ['POST', 'PUT', 'PATCH'])) {
            $Spec['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => $this->GetValidationSchema()
                    ]
                ]
            ];
        }
        
        // اضافه کردن security
        if ($this->RequiresAuthentication()) {
            $Spec['security'] = [
                ['bearerAuth' => []]
            ];
        }
        
        return $Spec;
    }
    
    /**
     * تولید operation ID
     * @return string
     */
    private function GenerateOperationId()
    {
        $OperationId = strtolower($this->Method) . '_' . str_replace(['/', '-', '{', '}'], '_', $this->EndpointName);
        $OperationId = preg_replace('/[^a-z0-9_]/', '', $OperationId);
        
        return $OperationId;
    }
    
    /**
     * دریافت summary
     * @return string
     */
    private function GetSummary()
    {
        $MethodLower = strtolower($this->Method);
        $EndpointName = str_replace('/', ' ', $this->EndpointName);
        
        return ucfirst($MethodLower) . ' ' . $EndpointName;
    }
    
    /**
     * دریافت description
     * @return string
     */
    private function GetDescription()
    {
        return 'API endpoint for ' . $this->Method . ' ' . $this->EndpointName;
    }
    
    /**
     * تبدیل به آرایه
     * @return array
     */
    public function ToArray()
    {
        return [
            'name' => $this->EndpointName,
            'method' => $this->Method,
            'data' => $this->EndpointData,
            'is_public' => $this->IsPublic(),
            'requires_auth' => $this->RequiresAuthentication(),
            'query_parameters' => $this->GetQueryParameters(),
            'required_parameters' => $this->GetRequiredParameters(),
            'optional_parameters' => $this->GetOptionalParameters(),
            'test_data' => $this->GenerateTestData(),
            'validation_schema' => $this->GetValidationSchema(),
            'openapi_spec' => $this->GetOpenApiSpec()
        ];
    }
}
