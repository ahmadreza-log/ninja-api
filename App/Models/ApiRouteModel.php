<?php

/**
 * Model برای مدیریت Route های API
 */
class ApiRouteModel
{
    /**
     * نام route
     * @var string
     */
    private $RouteName;
    
    /**
     * داده‌های route
     * @var array
     */
    private $RouteData;
    
    /**
     * سازنده کلاس
     * @param string $RouteName
     * @param array $RouteData
     */
    public function __construct($RouteName, $RouteData = [])
    {
        $this->RouteName = $RouteName;
        $this->RouteData = $RouteData;
    }
    
    /**
     * دریافت نام route
     * @return string
     */
    public function GetRouteName()
    {
        return $this->RouteName;
    }
    
    /**
     * دریافت داده‌های route
     * @return array
     */
    public function GetRouteData()
    {
        return $this->RouteData;
    }
    
    /**
     * دریافت methods موجود
     * @return array
     */
    public function GetMethods()
    {
        return $this->RouteData['methods'] ?? [];
    }
    
    /**
     * بررسی وجود method خاص
     * @param string $Method
     * @return bool
     */
    public function HasMethod($Method)
    {
        return isset($this->RouteData['methods'][$Method]);
    }
    
    /**
     * دریافت اطلاعات method خاص
     * @param string $Method
     * @return array|null
     */
    public function GetMethodData($Method)
    {
        return $this->RouteData['methods'][$Method] ?? null;
    }
    
    /**
     * دریافت parameters
     * @return array
     */
    public function GetParameters()
    {
        return $this->RouteData['parameters'] ?? [];
    }
    
    /**
     * دریافت query parameters
     * @param string $Method
     * @return array
     */
    public function GetQueryParameters($Method = null)
    {
        if ($Method && isset($this->RouteData['methods'][$Method])) {
            return $this->RouteData['methods'][$Method]['query_parameters'] ?? [];
        }
        
        // اگر method مشخص نشده، همه query parameters را برگردان
        $AllQueryParameters = [];
        foreach ($this->RouteData['methods'] ?? [] as $MethodData) {
            $AllQueryParameters = array_merge($AllQueryParameters, $MethodData['query_parameters'] ?? []);
        }
        
        return $AllQueryParameters;
    }
    
    /**
     * دریافت namespace
     * @return string
     */
    public function GetNamespace()
    {
        return $this->RouteData['namespace'] ?? '';
    }
    
    /**
     * دریافت description
     * @return string
     */
    public function GetDescription()
    {
        return $this->RouteData['description'] ?? '';
    }
    
    /**
     * بررسی عمومی بودن route
     * @return bool
     */
    public function IsPublic()
    {
        return $this->RouteData['is_public'] ?? false;
    }
    
    /**
     * دریافت example URL
     * @return string
     */
    public function GetExampleUrl()
    {
        return $this->RouteData['example_url'] ?? '';
    }
    
    /**
     * تولید URL کامل با پارامترها
     * @param array $Parameters
     * @return string
     */
    public function BuildUrl($Parameters = [])
    {
        $BaseUrl = rest_url();
        $Url = $BaseUrl . $this->RouteName;
        
        // جایگزینی پارامترها
        foreach ($Parameters as $Key => $Value) {
            $Url = str_replace('{' . $Key . '}', $Value, $Url);
            $Url = str_replace('(?P<' . $Key . '>[^/]+)', $Value, $Url);
        }
        
        return $Url;
    }
    
    /**
     * تولید داده‌های تست
     * @param string $Method
     * @return array
     */
    public function GenerateTestData($Method = 'GET')
    {
        $TestData = [
            'url' => $this->GetExampleUrl(),
            'method' => $Method,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => '',
            'query_params' => []
        ];
        
        // تنظیم method
        if (!$this->HasMethod($Method)) {
            $AvailableMethods = array_keys($this->GetMethods());
            if (!empty($AvailableMethods)) {
                $TestData['method'] = $AvailableMethods[0];
            }
        }
        
        // تولید body برای POST/PUT/PATCH
        if (in_array($TestData['method'], ['POST', 'PUT', 'PATCH'])) {
            $TestData['body'] = $this->GenerateRequestBody($TestData['method']);
        }
        
        // تولید query parameters
        $TestData['query_params'] = $this->GenerateQueryParameters($TestData['method']);
        
        return $TestData;
    }
    
    /**
     * تولید request body
     * @param string $Method
     * @return string
     */
    private function GenerateRequestBody($Method)
    {
        $MethodData = $this->GetMethodData($Method);
        if (!$MethodData) {
            return '';
        }
        
        $BodyData = [];
        $QueryParameters = $MethodData['query_parameters'] ?? [];
        
        foreach ($QueryParameters as $Param) {
            if (!$Param['required']) {
                continue;
            }
            
            $ParamName = $Param['name'];
            $ParamType = $Param['type'] ?? 'string';
            
            // تولید مقدار مثال
            $BodyData[$ParamName] = $this->GenerateExampleValue($ParamType, $ParamName);
        }
        
        return json_encode($BodyData, JSON_PRETTY_PRINT);
    }
    
    /**
     * تولید query parameters
     * @param string $Method
     * @return array
     */
    private function GenerateQueryParameters($Method)
    {
        $MethodData = $this->GetMethodData($Method);
        if (!$MethodData) {
            return [];
        }
        
        $QueryParams = [];
        $QueryParameters = $MethodData['query_parameters'] ?? [];
        
        foreach ($QueryParameters as $Param) {
            if (!$Param['required']) {
                continue;
            }
            
            $ParamName = $Param['name'];
            $ParamType = $Param['type'] ?? 'string';
            
            $QueryParams[$ParamName] = $this->GenerateExampleValue($ParamType, $ParamName);
        }
        
        return $QueryParams;
    }
    
    /**
     * تولید مقدار مثال بر اساس نوع
     * @param string $Type
     * @param string $Name
     * @return mixed
     */
    private function GenerateExampleValue($Type, $Name)
    {
        // تولید مقدار بر اساس نام پارامتر
        $NameLower = strtolower($Name);
        
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
     * دریافت لیست methods موجود
     * @return array
     */
    public function GetAvailableMethods()
    {
        return array_keys($this->GetMethods());
    }
    
    /**
     * بررسی نیاز به authentication
     * @param string $Method
     * @return bool
     */
    public function RequiresAuthentication($Method)
    {
        $MethodData = $this->GetMethodData($Method);
        if (!$MethodData) {
            return true;
        }
        
        $PermissionCallback = $MethodData['permission_callback'] ?? null;
        
        return $PermissionCallback !== '__return_true' && $PermissionCallback !== null;
    }
    
    /**
     * دریافت callback function
     * @param string $Method
     * @return callable|null
     */
    public function GetCallback($Method)
    {
        $MethodData = $this->GetMethodData($Method);
        return $MethodData['callback'] ?? null;
    }
    
    /**
     * دریافت permission callback
     * @param string $Method
     * @return callable|null
     */
    public function GetPermissionCallback($Method)
    {
        $MethodData = $this->GetMethodData($Method);
        return $MethodData['permission_callback'] ?? null;
    }
    
    /**
     * تبدیل به آرایه
     * @return array
     */
    public function ToArray()
    {
        return [
            'name' => $this->RouteName,
            'data' => $this->RouteData,
            'methods' => $this->GetAvailableMethods(),
            'is_public' => $this->IsPublic(),
            'namespace' => $this->GetNamespace(),
            'description' => $this->GetDescription(),
            'example_url' => $this->GetExampleUrl()
        ];
    }
}
