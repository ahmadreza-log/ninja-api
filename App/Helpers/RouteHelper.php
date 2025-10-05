<?php

/**
 * کلاس کمکی برای کار با Route ها
 */
class RouteHelper
{
    /**
     * تبدیل route pattern به URL کامل
     * @param string $RoutePattern
     * @param array $Parameters
     * @return string
     */
    public static function BuildUrlFromPattern($RoutePattern, $Parameters = [])
    {
        $BaseUrl = rest_url();
        $FullPattern = $BaseUrl . $RoutePattern;
        
        // جایگزینی پارامترها
        foreach ($Parameters as $Key => $Value) {
            $FullPattern = str_replace('{' . $Key . '}', $Value, $FullPattern);
            $FullPattern = str_replace('(?P<' . $Key . '>[^/]+)', $Value, $FullPattern);
        }
        
        return $FullPattern;
    }
    
    /**
     * استخراج پارامترها از route pattern
     * @param string $RoutePattern
     * @return array
     */
    public static function ExtractParametersFromPattern($RoutePattern)
    {
        $Parameters = [];
        
        // جستجوی پارامترهای {param} یا (?P<param>[^/]+)
        preg_match_all('/\{([^}]+)\}|\(\?P<([^>]+)>[^)]+\)/', $RoutePattern, $Matches);
        
        if (!empty($Matches[1])) {
            foreach ($Matches[1] as $Parameter) {
                if (!empty($Parameter)) {
                    $Parameters[] = [
                        'name' => $Parameter,
                        'type' => 'string',
                        'required' => true,
                        'description' => sprintf(__('Parameter: %s', 'ninja-api-explorer'), $Parameter)
                    ];
                }
            }
        }
        
        if (!empty($Matches[2])) {
            foreach ($Matches[2] as $Parameter) {
                if (!empty($Parameter)) {
                    $Parameters[] = [
                        'name' => $Parameter,
                        'type' => 'string',
                        'required' => true,
                        'description' => sprintf(__('Parameter: %s', 'ninja-api-explorer'), $Parameter)
                    ];
                }
            }
        }
        
        return $Parameters;
    }
    
    /**
     * تشخیص نوع HTTP Method
     * @param string $Method
     * @return string
     */
    public static function GetMethodColor($Method)
    {
        $MethodColors = [
            'GET' => 'success',
            'POST' => 'primary',
            'PUT' => 'warning',
            'PATCH' => 'info',
            'DELETE' => 'danger',
            'OPTIONS' => 'secondary'
        ];
        
        return $MethodColors[$Method] ?? 'secondary';
    }
    
    /**
     * فرمت کردن route pattern برای نمایش
     * @param string $RoutePattern
     * @return string
     */
    public static function FormatRouteForDisplay($RoutePattern)
    {
        // تبدیل regex patterns به فرمت قابل خواندن
        $FormattedRoute = preg_replace('/\(\?P<([^>]+)>[^)]+\)/', '{$1}', $RoutePattern);
        $FormattedRoute = preg_replace('/\(\?\d*\)/', '', $FormattedRoute);
        $FormattedRoute = preg_replace('/[\[\]]/', '', $FormattedRoute);
        
        return $FormattedRoute;
    }
    
    /**
     * بررسی اینکه آیا route عمومی است یا خصوصی
     * @param array $RouteData
     * @return bool
     */
    public static function IsPublicRoute($RouteData)
    {
        if (!isset($RouteData['methods'])) {
            return false;
        }
        
        foreach ($RouteData['methods'] as $Method => $MethodData) {
            if (isset($MethodData['permission_callback'])) {
                $Callback = $MethodData['permission_callback'];
                
                // اگر permission_callback برابر '__return_true' باشد، عمومی است
                if ($Callback === '__return_true' || 
                    (is_string($Callback) && $Callback === '__return_true')) {
                    return true;
                }
                
                // اگر permission_callback برابر null باشد، عمومی است
                if ($Callback === null) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * تولید مثال URL برای route
     * @param string $RoutePattern
     * @param string $Namespace
     * @return string
     */
    public static function GenerateExampleUrl($RoutePattern, $Namespace)
    {
        $Parameters = self::ExtractParametersFromPattern($RoutePattern);
        $ExampleParameters = [];
        
        foreach ($Parameters as $Parameter) {
            $Name = $Parameter['name'];
            
            // تولید مقدار مثال بر اساس نام پارامتر
            if (strpos($Name, 'id') !== false) {
                $ExampleParameters[$Name] = '123';
            } elseif (strpos($Name, 'slug') !== false) {
                $ExampleParameters[$Name] = 'example-slug';
            } elseif (strpos($Name, 'type') !== false) {
                $ExampleParameters[$Name] = 'post';
            } else {
                $ExampleParameters[$Name] = 'example';
            }
        }
        
        $ExampleUrl = self::BuildUrlFromPattern($Namespace . $RoutePattern, $ExampleParameters);
        
        return $ExampleUrl;
    }
    
    /**
     * استخراج query parameters از args
     * @param array $Args
     * @return array
     */
    public static function ExtractQueryParameters($Args)
    {
        $QueryParameters = [];
        
        if (isset($Args['args']) && is_array($Args['args'])) {
            foreach ($Args['args'] as $ParamName => $ParamConfig) {
                if (is_array($ParamConfig)) {
                    $QueryParameters[] = [
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
        }
        
        return $QueryParameters;
    }
    
    /**
     * گروه‌بندی routes بر اساس namespace
     * @param array $Routes
     * @return array
     */
    public static function GroupRoutesByNamespace($Routes)
    {
        $GroupedRoutes = [];
        
        foreach ($Routes as $RouteName => $RouteData) {
            $Namespace = self::ExtractNamespaceFromRouteName($RouteName);
            
            if (!isset($GroupedRoutes[$Namespace])) {
                $GroupedRoutes[$Namespace] = [
                    'namespace' => $Namespace,
                    'routes' => []
                ];
            }
            
            $GroupedRoutes[$Namespace]['routes'][$RouteName] = $RouteData;
        }
        
        // مرتب‌سازی بر اساس namespace
        ksort($GroupedRoutes);
        
        return $GroupedRoutes;
    }
    
    /**
     * استخراج namespace از نام route
     * @param string $RouteName
     * @return string
     */
    private static function ExtractNamespaceFromRouteName($RouteName)
    {
        $Parts = explode('/', trim($RouteName, '/'));
        
        if (count($Parts) >= 2) {
            return $Parts[0] . '/' . $Parts[1];
        }
        
        return 'unknown';
    }
    
    /**
     * تولید HTML برای نمایش route
     * @param string $RouteName
     * @param array $RouteData
     * @return string
     */
    public static function GenerateRouteHtml($RouteName, $RouteData)
    {
        $Html = '<div class="api-route-card" data-route="' . esc_attr($RouteName) . '">';
        
        // Header با method ها
        $Html .= '<div class="route-header">';
        
        if (isset($RouteData['methods'])) {
            foreach ($RouteData['methods'] as $Method => $MethodData) {
                $Color = self::GetMethodColor($Method);
                $Html .= '<span class="method-badge method-' . $Color . '">' . strtoupper($Method) . '</span>';
            }
        }
        
        $FormattedRoute = self::FormatRouteForDisplay($RouteName);
        $Html .= '<span class="route-path">' . esc_html($FormattedRoute) . '</span>';
        
        $Html .= '</div>';
        
        // Description
        if (isset($RouteData['description'])) {
            $Html .= '<div class="route-description">' . esc_html($RouteData['description']) . '</div>';
        }
        
        $Html .= '</div>';
        
        return $Html;
    }
}
