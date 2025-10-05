<?php

/**
 * کلاس کمکی برای مدیریت View ها
 */
class ViewHelper
{
    /**
     * مسیر پوشه Views
     * @var string
     */
    private $ViewsPath;
    
    /**
     * سازنده کلاس
     */
    public function __construct()
    {
        $this->ViewsPath = NINJA_API_EXPLORER_APP_PATH . 'Views/';
    }
    
    /**
     * رندر کردن یک View
     * @param string $ViewName
     * @param array $Data
     * @return void
     */
    public function Render($ViewName, $Data = [])
    {
        $ViewFile = $this->ViewsPath . $ViewName . '.php';
        
        if (!file_exists($ViewFile)) {
            wp_die(sprintf(__('View file not found: %s', 'ninja-api-explorer'), $ViewName));
        }
        
        // استخراج متغیرها از آرایه $Data
        extract($Data);
        
        // شروع output buffering
        ob_start();
        
        // بارگذاری فایل view
        include $ViewFile;
        
        // دریافت محتوا و پاک کردن buffer
        $Content = ob_get_clean();
        
        // نمایش محتوا
        echo $Content;
    }
    
    /**
     * دریافت محتوای یک View بدون نمایش
     * @param string $ViewName
     * @param array $Data
     * @return string
     */
    public function GetContent($ViewName, $Data = [])
    {
        $ViewFile = $this->ViewsPath . $ViewName . '.php';
        
        if (!file_exists($ViewFile)) {
            return sprintf(__('View file not found: %s', 'ninja-api-explorer'), $ViewName);
        }
        
        // استخراج متغیرها از آرایه $Data
        extract($Data);
        
        // شروع output buffering
        ob_start();
        
        // بارگذاری فایل view
        include $ViewFile;
        
        // دریافت محتوا و پاک کردن buffer
        $Content = ob_get_clean();
        
        return $Content;
    }
    
    /**
     * بررسی وجود View
     * @param string $ViewName
     * @return bool
     */
    public function ViewExists($ViewName)
    {
        $ViewFile = $this->ViewsPath . $ViewName . '.php';
        return file_exists($ViewFile);
    }
    
    /**
     * رندر کردن partial (بخش کوچکی از view)
     * @param string $PartialName
     * @param array $Data
     * @return void
     */
    public function RenderPartial($PartialName, $Data = [])
    {
        $PartialPath = $this->ViewsPath . 'partials/' . $PartialName . '.php';
        
        if (!file_exists($PartialPath)) {
            return;
        }
        
        // استخراج متغیرها از آرایه $Data
        extract($Data);
        
        // شروع output buffering
        ob_start();
        
        // بارگذاری فایل partial
        include $PartialPath;
        
        // دریافت محتوا و پاک کردن buffer
        $Content = ob_get_clean();
        
        // نمایش محتوا
        echo $Content;
    }
    
    /**
     * دریافت محتوای partial
     * @param string $PartialName
     * @param array $Data
     * @return string
     */
    public function GetPartialContent($PartialName, $Data = [])
    {
        $PartialPath = $this->ViewsPath . 'partials/' . $PartialName . '.php';
        
        if (!file_exists($PartialPath)) {
            return '';
        }
        
        // استخراج متغیرها از آرایه $Data
        extract($Data);
        
        // شروع output buffering
        ob_start();
        
        // بارگذاری فایل partial
        include $PartialPath;
        
        // دریافت محتوا و پاک کردن buffer
        $Content = ob_get_clean();
        
        return $Content;
    }
    
    /**
     * فرار کردن HTML برای امنیت
     * @param string $String
     * @return string
     */
    public function Escape($String)
    {
        return esc_html($String);
    }
    
    /**
     * فرار کردن attribute برای امنیت
     * @param string $String
     * @return string
     */
    public function EscapeAttr($String)
    {
        return esc_attr($String);
    }
    
    /**
     * فرمت کردن JSON برای نمایش
     * @param mixed $Data
     * @param int $Options
     * @return string
     */
    public function JsonEncode($Data, $Options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        return json_encode($Data, $Options);
    }
    
    /**
     * تولید URL برای صفحه ادمین
     * @param string $Page
     * @param array $Parameters
     * @return string
     */
    public function AdminUrl($Page, $Parameters = [])
    {
        $Url = admin_url('admin.php?page=' . $Page);
        
        if (!empty($Parameters)) {
            $Url .= '&' . http_build_query($Parameters);
        }
        
        return $Url;
    }
    
    /**
     * تولید nonce field
     * @param string $Action
     * @param string $Name
     * @return string
     */
    public function NonceField($Action, $Name = '_wpnonce')
    {
        return wp_nonce_field($Action, $Name, true, false);
    }
    
    /**
     * تولید nonce برای Ajax
     * @param string $Action
     * @return string
     */
    public function AjaxNonce($Action)
    {
        return wp_create_nonce($Action);
    }
    
    /**
     * فرمت کردن زمان
     * @param string $DateTime
     * @param string $Format
     * @return string
     */
    public function FormatDateTime($DateTime, $Format = 'Y/m/d H:i:s')
    {
        if (empty($DateTime)) {
            return '';
        }
        
        $Timestamp = strtotime($DateTime);
        return date($Format, $Timestamp);
    }
    
    /**
     * فرمت کردن مدت زمان
     * @param int $Seconds
     * @return string
     */
    public function FormatDuration($Seconds)
    {
        if ($Seconds < 1) {
            return round($Seconds * 1000) . 'ms';
        }
        
        if ($Seconds < 60) {
            return round($Seconds, 2) . 's';
        }
        
        $Minutes = floor($Seconds / 60);
        $RemainingSeconds = $Seconds % 60;
        
        if ($Minutes < 60) {
            return $Minutes . 'm ' . round($RemainingSeconds) . 's';
        }
        
        $Hours = floor($Minutes / 60);
        $RemainingMinutes = $Minutes % 60;
        
        return $Hours . 'h ' . $RemainingMinutes . 'm';
    }
    
    /**
     * تولید کلاس CSS بر اساس وضعیت
     * @param int $StatusCode
     * @return string
     */
    public function GetStatusClass($StatusCode)
    {
        if ($StatusCode >= 200 && $StatusCode < 300) {
            return 'success';
        } elseif ($StatusCode >= 300 && $StatusCode < 400) {
            return 'info';
        } elseif ($StatusCode >= 400 && $StatusCode < 500) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
    
    /**
     * تولید رنگ بر اساس نوع method
     * @param string $Method
     * @return string
     */
    public function GetMethodColor($Method)
    {
        $Colors = [
            'GET' => '#28a745',
            'POST' => '#007cba',
            'PUT' => '#ffc107',
            'PATCH' => '#17a2b8',
            'DELETE' => '#dc3545',
            'OPTIONS' => '#6c757d'
        ];
        
        return $Colors[strtoupper($Method)] ?? '#6c757d';
    }
    
    /**
     * تولید HTML برای نمایش JSON
     * @param mixed $Data
     * @param string $Class
     * @return string
     */
    public function JsonDisplay($Data, $Class = 'json-display')
    {
        $JsonString = $this->JsonEncode($Data);
        $JsonString = htmlspecialchars($JsonString, ENT_QUOTES, 'UTF-8');
        
        return '<pre class="' . esc_attr($Class) . '"><code>' . $JsonString . '</code></pre>';
    }
}
