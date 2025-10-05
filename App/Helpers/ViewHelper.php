<?php

/**
 * Helper class for managing Views
 * Provides utility functions for view rendering, data formatting, and HTML generation
 */
class ViewHelper
{
    /**
     * Path to Views folder
     * @var string
     */
    private $ViewsPath;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ViewsPath = NINJA_API_EXPLORER_PLUGIN_PATH . 'App/Views/';
    }
    
    /**
     * Render a view with data
     * @param string $ViewName
     * @param array $Data
     * @return void
     */
    public function Render($ViewName, $Data = [])
    {
        $ViewFile = $this->ViewsPath . $ViewName . '.php';
        
        // Extract variables from $Data array
        extract($Data);
        
        // Start output buffering
        ob_start();
        
        // Load view file
        include $ViewFile;
        
        // Get content and clean buffer
        $Content = ob_get_clean();
        
        // Display content
        echo $Content;
    }
    
    /**
     * Get view content without displaying it
     * @param string $ViewName
     * @param array $Data
     * @return string
     */
    public function GetContent($ViewName, $Data = [])
    {
        $ViewFile = $this->ViewsPath . $ViewName . '.php';
        
        if (!file_exists($ViewFile)) {
            return '';
        }
        
        // Extract variables from $Data array
        extract($Data);
        
        // Start output buffering
        ob_start();
        
        // Load view file
        include $ViewFile;
        
        // Get content and clean buffer
        $Content = ob_get_clean();
        
        return $Content;
    }
    
    /**
     * Check if view exists
     * @param string $ViewName
     * @return bool
     */
    public function ViewExists($ViewName)
    {
        $ViewFile = $this->ViewsPath . $ViewName . '.php';
        return file_exists($ViewFile);
    }
    
    /**
     * Render a partial (small section of view)
     * @param string $PartialName
     * @param array $Data
     * @return void
     */
    public function RenderPartial($PartialName, $Data = [])
    {
        $PartialFile = $this->ViewsPath . 'partials/' . $PartialName . '.php';
        
        // Extract variables from $Data array
        extract($Data);
        
        // Start output buffering
        ob_start();
        
        // Load partial file
        include $PartialFile;
        
        // Get content and clean buffer
        $Content = ob_get_clean();
        
        // Display content
        echo $Content;
    }
    
    /**
     * Get partial content
     * @param string $PartialName
     * @param array $Data
     * @return string
     */
    public function GetPartialContent($PartialName, $Data = [])
    {
        $PartialFile = $this->ViewsPath . 'partials/' . $PartialName . '.php';
        
        if (!file_exists($PartialFile)) {
            return '';
        }
        
        // Extract variables from $Data array
        extract($Data);
        
        // Start output buffering
        ob_start();
        
        // Load partial file
        include $PartialFile;
        
        // Get content and clean buffer
        $Content = ob_get_clean();
        
        return $Content;
    }
    
    /**
     * Escape HTML for security
     * @param string $String
     * @return string
     */
    public function EscapeHtml($String)
    {
        return htmlspecialchars($String, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Escape HTML attribute for security
     * @param string $String
     * @return string
     */
    public function EscapeAttr($String)
    {
        return esc_attr($String);
    }
    
    /**
     * Format JSON for display
     * @param mixed $Data
     * @return string
     */
    public function FormatJson($Data)
    {
        return json_encode($Data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Generate admin page URL
     * @param string $Page
     * @param array $Args
     * @return string
     */
    public function GetAdminUrl($Page, $Args = [])
    {
        $DefaultArgs = [
            'page' => $Page
        ];
        
        $Args = array_merge($DefaultArgs, $Args);
        
        return admin_url('admin.php?' . http_build_query($Args));
    }
    
    /**
     * Generate nonce field
     * @param string $Action
     * @param string $Name
     * @return string
     */
    public function NonceField($Action, $Name = '_wpnonce')
    {
        return wp_nonce_field($Action, $Name, true, false);
    }
    
    /**
     * Generate nonce for AJAX
     * @param string $Action
     * @return string
     */
    public function AjaxNonce($Action)
    {
        return wp_create_nonce($Action);
    }
    
    /**
     * Format time for display
     * @param int $Timestamp
     * @return string
     */
    public function FormatTime($Timestamp)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $Timestamp);
    }
    
    /**
     * Format duration
     * @param int $Seconds
     * @return string
     */
    public function FormatDuration($Seconds)
    {
        if ($Seconds < 60) {
            return $Seconds . ' ' . __('seconds', 'ninja-api-explorer');
        } elseif ($Seconds < 3600) {
            $Minutes = floor($Seconds / 60);
            return $Minutes . ' ' . __('minutes', 'ninja-api-explorer');
        } else {
            $Hours = floor($Seconds / 3600);
            return $Hours . ' ' . __('hours', 'ninja-api-explorer');
        }
    }
    
    /**
     * Generate CSS class based on status
     * @param string $Status
     * @return string
     */
    public function GetStatusClass($Status)
    {
        $StatusClasses = [
            'success' => 'status-success',
            'error' => 'status-error',
            'warning' => 'status-warning',
            'info' => 'status-info',
            'pending' => 'status-pending',
            'processing' => 'status-processing'
        ];
        
        return $StatusClasses[$Status] ?? 'status-default';
    }
    
    /**
     * Generate color based on method type
     * @param string $Method
     * @return string
     */
    public function GetMethodColor($Method)
    {
        $MethodColors = [
            'GET' => '#28a745',
            'POST' => '#007bff',
            'PUT' => '#ffc107',
            'PATCH' => '#17a2b8',
            'DELETE' => '#dc3545',
            'OPTIONS' => '#6c757d',
            'HEAD' => '#6c757d'
        ];
        
        return $MethodColors[strtoupper($Method)] ?? '#6c757d';
    }
    
    /**
     * Generate HTML for displaying JSON
     * @param mixed $Data
     * @param bool $Collapsible
     * @return string
     */
    public function JsonHtml($Data, $Collapsible = false)
    {
        $JsonString = $this->FormatJson($Data);
        $Html = '<pre class="json-display';
        
        if ($Collapsible) {
            $Html .= ' json-collapsible" data-collapsed="true">';
        } else {
            $Html .= '">';
        }
        
        $Html .= $this->EscapeHtml($JsonString) . '</pre>';
        
        return $Html;
    }
}