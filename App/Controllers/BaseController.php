<?php

/**
 * Base class for all Controllers
 */
abstract class BaseController
{
    /**
     * View Helper instance
     * @var ViewHelper
     */
    protected $ViewHelper;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ViewHelper = new ViewHelper();
    }
    
    /**
     * Check user permissions
     * @param string $Capability
     * @return bool
     */
    protected function CheckPermission($Capability = 'manage_options')
    {
        return current_user_can($Capability);
    }
    
    /**
     * Verify nonce for security
     * @param string $Action
     * @param string $Nonce
     * @return bool
     */
    protected function VerifyNonce($Action, $Nonce)
    {
        return wp_verify_nonce($Nonce, $Action);
    }
    
    /**
     * Send JSON success response
     * @param mixed $Data
     * @param int $StatusCode
     * @return void
     */
    protected function SendJsonSuccess($Data = null, $StatusCode = 200)
    {
        wp_send_json_success($Data, $StatusCode);
    }
    
    /**
     * Send JSON error response
     * @param string $Message
     * @param int $StatusCode
     * @param mixed $Data
     * @return void
     */
    protected function SendJsonError($Message, $StatusCode = 400, $Data = null)
    {
        wp_send_json_error([
            'message' => $Message,
            'data' => $Data
        ], $StatusCode);
    }
    
    /**
     * Render view
     * @param string $ViewName
     * @param array $Data
     * @return void
     */
    protected function RenderView($ViewName, $Data = [])
    {
        $this->ViewHelper->Render($ViewName, $Data);
    }
    
    /**
     * Get view content
     * @param string $ViewName
     * @param array $Data
     * @return string
     */
    protected function GetViewContent($ViewName, $Data = [])
    {
        return $this->ViewHelper->GetContent($ViewName, $Data);
    }
    
    /**
     * Render partial
     * @param string $PartialName
     * @param array $Data
     * @return void
     */
    protected function RenderPartial($PartialName, $Data = [])
    {
        $this->ViewHelper->RenderPartial($PartialName, $Data);
    }
    
    /**
     * Get partial content
     * @param string $PartialName
     * @param array $Data
     * @return string
     */
    protected function GetPartialContent($PartialName, $Data = [])
    {
        return $this->ViewHelper->GetPartialContent($PartialName, $Data);
    }
    
    /**
     * Escape HTML
     * @param string $String
     * @return string
     */
    protected function Escape($String)
    {
        return $this->ViewHelper->Escape($String);
    }
    
    /**
     * Escape attribute
     * @param string $String
     * @return string
     */
    protected function EscapeAttr($String)
    {
        return $this->ViewHelper->EscapeAttr($String);
    }
    
    /**
     * Format JSON
     * @param mixed $Data
     * @return string
     */
    protected function JsonEncode($Data)
    {
        return $this->ViewHelper->JsonEncode($Data);
    }
    
    /**
     * Get plugin settings
     * @return array
     */
    protected function GetPluginSettings()
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
     * Save plugin settings
     * @param array $Settings
     * @return bool
     */
    protected function SavePluginSettings($Settings)
    {
        return update_option('ninja_api_explorer_settings', $Settings);
    }
    
    /**
     * Log event
     * @param string $Message
     * @param string $Level
     * @param array $Context
     * @return void
     */
    protected function Log($Message, $Level = 'info', $Context = [])
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[Ninja API Explorer] %s: %s', strtoupper($Level), $Message));
            
            if (!empty($Context)) {
                error_log('[Ninja API Explorer] Context: ' . json_encode($Context));
            }
        }
    }
    
    /**
     * Get current user information
     * @return array
     */
    protected function GetCurrentUser()
    {
        $User = wp_get_current_user();
        
        return [
            'id' => $User->ID,
            'login' => $User->user_login,
            'email' => $User->user_email,
            'display_name' => $User->display_name,
            'capabilities' => $User->allcaps
        ];
    }
    
    /**
     * Check if user is admin
     * @return bool
     */
    protected function IsAdmin()
    {
        return current_user_can('manage_options');
    }
    
    /**
     * Get user IP address
     * @return string
     */
    protected function GetUserIpAddress()
    {
        $IpKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($IpKeys as $Key) {
            if (array_key_exists($Key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$Key]) as $Ip) {
                    $Ip = trim($Ip);
                    
                    if (filter_var($Ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $Ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get user agent
     * @return string
     */
    protected function GetUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Generate admin URL
     * @param string $Page
     * @param array $Parameters
     * @return string
     */
    protected function GetAdminUrl($Page, $Parameters = [])
    {
        return $this->ViewHelper->AdminUrl($Page, $Parameters);
    }
    
    /**
     * Generate nonce field
     * @param string $Action
     * @param string $Name
     * @return string
     */
    protected function GetNonceField($Action, $Name = '_wpnonce')
    {
        return $this->ViewHelper->NonceField($Action, $Name);
    }
    
    /**
     * Generate AJAX nonce
     * @param string $Action
     * @return string
     */
    protected function GetAjaxNonce($Action)
    {
        return $this->ViewHelper->AjaxNonce($Action);
    }
    
    /**
     * Format date time
     * @param string $DateTime
     * @param string $Format
     * @return string
     */
    protected function FormatDateTime($DateTime, $Format = 'Y/m/d H:i:s')
    {
        return $this->ViewHelper->FormatDateTime($DateTime, $Format);
    }
    
    /**
     * Format duration
     * @param int $Seconds
     * @return string
     */
    protected function FormatDuration($Seconds)
    {
        return $this->ViewHelper->FormatDuration($Seconds);
    }
    
    /**
     * Get status class
     * @param int $StatusCode
     * @return string
     */
    protected function GetStatusClass($StatusCode)
    {
        return $this->ViewHelper->GetStatusClass($StatusCode);
    }
    
    /**
     * Get method color
     * @param string $Method
     * @return string
     */
    protected function GetMethodColor($Method)
    {
        return $this->ViewHelper->GetMethodColor($Method);
    }
    
    /**
     * Display JSON
     * @param mixed $Data
     * @param string $Class
     * @return string
     */
    protected function DisplayJson($Data, $Class = 'json-display')
    {
        return $this->ViewHelper->JsonDisplay($Data, $Class);
    }
    
    /**
     * Sanitize input
     * @param mixed $Input
     * @param string $Type
     * @return mixed
     */
    protected function SanitizeInput($Input, $Type = 'text')
    {
        switch ($Type) {
            case 'email':
                return sanitize_email($Input);
            case 'url':
                return esc_url_raw($Input);
            case 'int':
                return intval($Input);
            case 'float':
                return floatval($Input);
            case 'bool':
                return (bool) $Input;
            case 'textarea':
                return sanitize_textarea_field($Input);
            case 'key':
                return sanitize_key($Input);
            default:
                return sanitize_text_field($Input);
        }
    }
    
    /**
     * Validate input
     * @param mixed $Input
     * @param string $Type
     * @param array $Options
     * @return bool
     */
    protected function ValidateInput($Input, $Type, $Options = [])
    {
        switch ($Type) {
            case 'email':
                return is_email($Input);
            case 'url':
                return filter_var($Input, FILTER_VALIDATE_URL) !== false;
            case 'int':
                return is_numeric($Input) && is_int($Input + 0);
            case 'float':
                return is_numeric($Input) && is_float($Input + 0);
            case 'required':
                return !empty($Input);
            case 'min_length':
                return strlen($Input) >= ($Options['min'] ?? 0);
            case 'max_length':
                return strlen($Input) <= ($Options['max'] ?? PHP_INT_MAX);
            case 'min':
                return $Input >= ($Options['min'] ?? PHP_INT_MIN);
            case 'max':
                return $Input <= ($Options['max'] ?? PHP_INT_MAX);
            default:
                return !empty($Input);
        }
    }
}