<?php

/**
 * Helper class for working with API Routes
 * Provides utility functions for route processing, URL generation, and data formatting
 */
class RouteHelper
{
    /**
     * Convert route pattern to complete URL
     * @param string $RoutePattern
     * @param array $Parameters
     * @return string
     */
    public static function BuildUrlFromPattern($RoutePattern, $Parameters = [])
    {
        $BaseUrl = rest_url();
        
        // Ensure BaseUrl doesn't end with / and RoutePattern doesn't start with /
        $BaseUrl = rtrim($BaseUrl, '/');
        $RoutePattern = ltrim($RoutePattern, '/');
        
        $FullPattern = $BaseUrl . '/' . $RoutePattern;
        
        // Replace parameters
        foreach ($Parameters as $Key => $Value) {
            $FullPattern = str_replace('{' . $Key . '}', $Value, $FullPattern);
            $FullPattern = str_replace('(?P<' . $Key . '>[^/]+)', $Value, $FullPattern);
        }
        
        return $FullPattern;
    }
    
    /**
     * Extract parameters from route pattern
     * @param string $RoutePattern
     * @return array
     */
    public static function ExtractParametersFromPattern($RoutePattern)
    {
        $Parameters = [];
        
        // Search for parameters in format {param} or (?P<param>[^/]+)
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
     * Get color class for HTTP method
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
     * Format route pattern for display
     * @param string $RoutePattern
     * @return string
     */
    public static function FormatRouteForDisplay($RoutePattern)
    {
        // Convert regex patterns to readable format
        $FormattedRoute = preg_replace('/\(\?P<([^>]+)>[^)]+\)/', '{$1}', $RoutePattern);
        $FormattedRoute = preg_replace('/\(\?\d*\)/', '', $FormattedRoute);
        $FormattedRoute = preg_replace('/[\[\]]/', '', $FormattedRoute);
        
        return $FormattedRoute;
    }
    
    /**
     * Check if route is public or private
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
        }
        
        return false;
    }
    
    /**
     * Generate example URL for route
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
            
            // Generate example value based on parameter name
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
        
        // Ensure we don't duplicate the namespace
        $FullPattern = $Namespace . '/' . ltrim($RoutePattern, '/');
        $ExampleUrl = self::BuildUrlFromPattern($FullPattern, $ExampleParameters);
        
        return $ExampleUrl;
    }
    
    /**
     * Extract query parameters from args
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
     * Group routes by namespace
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
        
        // Sort by namespace
        ksort($GroupedRoutes);
        
        return $GroupedRoutes;
    }
    
    /**
     * Extract namespace from route name
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
     * Generate HTML for displaying route
     * @param string $RouteName
     * @param array $RouteData
     * @return string
     */
    public static function GenerateRouteHtml($RouteName, $RouteData)
    {
        $Html = '<div class="api-route-card" data-route="' . esc_attr($RouteName) . '">';
        
        // Header with methods
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