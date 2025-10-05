<?php

/**
 * API test controller
 */
class ApiTestController extends BaseController
{
    /**
     * ApiService instance
     * @var ApiService
     */
    private $ApiService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ApiService = new ApiService();
    }
    
    /**
     * Handle API test
     */
    public function HandleApiTest()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        // Get request parameters
        $Url = $this->SanitizeInput($_POST['url'] ?? '', 'url');
        $Method = strtoupper($this->SanitizeInput($_POST['method'] ?? 'GET', 'text'));
        $Headers = $this->SanitizeHeaders($_POST['headers'] ?? []);
        $Body = $this->SanitizeInput($_POST['body'] ?? '', 'textarea');
        $Timeout = intval($_POST['timeout'] ?? 30);
        
        // Validate inputs
        $Validation = $this->ValidateTestRequest($Url, $Method, $Timeout);
        
        if (!$Validation['valid']) {
            $this->SendJsonError($Validation['message'], 400);
        }
        
        // Execute API test
        $Result = $this->ApiService->TestEndpoint($Url, $Method, $Headers, $Body, $Timeout);
        
        // Log request
        $this->LogApiRequest($Url, $Method, $Headers, $Body, $Result);
        
        // Send result
        $this->SendJsonSuccess($Result);
    }
    
    /**
     * Validate test request
     * @param string $Url
     * @param string $Method
     * @param int $Timeout
     * @return array
     */
    private function ValidateTestRequest($Url, $Method, $Timeout)
    {
        // Check URL
        if (empty($Url)) {
            return [
                'valid' => false,
                'message' => __('URL is required', 'ninja-api-explorer')
            ];
        }
        
        if (!filter_var($Url, FILTER_VALIDATE_URL)) {
            return [
                'valid' => false,
                'message' => __('Invalid URL format', 'ninja-api-explorer')
            ];
        }
        
        // Check method
        $ValidMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        if (!in_array($Method, $ValidMethods)) {
            return [
                'valid' => false,
                'message' => __('Invalid HTTP method', 'ninja-api-explorer')
            ];
        }
        
        // Check timeout
        if ($Timeout < 1 || $Timeout > 300) {
            return [
                'valid' => false,
                'message' => __('Timeout must be between 1 and 300 seconds', 'ninja-api-explorer')
            ];
        }
        
        return [
            'valid' => true,
            'message' => ''
        ];
    }
    
    /**
     * Sanitize headers
     * @param array $Headers
     * @return array
     */
    private function SanitizeHeaders($Headers)
    {
        $SanitizedHeaders = [];
        
        if (is_array($Headers)) {
            foreach ($Headers as $Key => $Value) {
                $CleanKey = $this->SanitizeInput($Key, 'text');
                $CleanValue = $this->SanitizeInput($Value, 'text');
                
                if (!empty($CleanKey) && !empty($CleanValue)) {
                    $SanitizedHeaders[$CleanKey] = $CleanValue;
                }
            }
        }
        
        return $SanitizedHeaders;
    }
    
    /**
     * Log API request
     * @param string $Url
     * @param string $Method
     * @param array $Headers
     * @param string $Body
     * @param array $Result
     * @return void
     */
    private function LogApiRequest($Url, $Method, $Headers, $Body, $Result)
    {
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        $CurrentUser = $this->GetCurrentUser();
        
        $LogData = [
            'endpoint' => $Url,
            'method' => $Method,
            'status_code' => $Result['status_code'] ?? 0,
            'response_time' => $Result['response_time'] ?? 0,
            'user_id' => $CurrentUser['id'],
            'ip_address' => $this->GetUserIpAddress(),
            'user_agent' => $this->GetUserAgent(),
            'request_data' => json_encode([
                'headers' => $Headers,
                'body' => $Body
            ]),
            'response_data' => json_encode($Result),
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert($TableName, $LogData);
    }
    
    /**
     * Get test history
     */
    public function GetTestHistory()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        $Limit = intval($_POST['limit'] ?? 20);
        $Offset = intval($_POST['offset'] ?? 0);
        
        $Results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $TableName ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $Limit,
            $Offset
        ), ARRAY_A);
        
        // Process results
        foreach ($Results as &$Result) {
            $Result['request_data'] = json_decode($Result['request_data'], true);
            $Result['response_data'] = json_decode($Result['response_data'], true);
            $Result['created_at_formatted'] = $this->FormatDateTime($Result['created_at']);
        }
        
        $this->SendJsonSuccess($Results);
    }
    
    /**
     * Get test statistics
     */
    public function GetTestStats()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        
        $Stats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'average_response_time' => 0,
            'most_used_endpoints' => [],
            'status_code_distribution' => [],
            'requests_by_hour' => []
        ];
        
        // Total requests
        $Stats['total_requests'] = $wpdb->get_var("SELECT COUNT(*) FROM $TableName");
        
        // Successful requests
        $Stats['successful_requests'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $TableName WHERE status_code >= 200 AND status_code < 300"
        );
        
        // Failed requests
        $Stats['failed_requests'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $TableName WHERE status_code >= 400"
        );
        
        // Average response time
        $AvgResponseTime = $wpdb->get_var("SELECT AVG(response_time) FROM $TableName");
        $Stats['average_response_time'] = round($AvgResponseTime ?? 0, 2);
        
        // Most popular endpoints
        $PopularEndpoints = $wpdb->get_results(
            "SELECT endpoint, COUNT(*) as count FROM $TableName 
             GROUP BY endpoint ORDER BY count DESC LIMIT 10",
            ARRAY_A
        );
        $Stats['most_used_endpoints'] = $PopularEndpoints;
        
        // Status code distribution
        $StatusCodeDistribution = $wpdb->get_results(
            "SELECT status_code, COUNT(*) as count FROM $TableName 
             GROUP BY status_code ORDER BY status_code",
            ARRAY_A
        );
        $Stats['status_code_distribution'] = $StatusCodeDistribution;
        
        // Requests by hour (last 24 hours)
        $RequestsByHour = $wpdb->get_results(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count FROM $TableName 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
             GROUP BY HOUR(created_at) ORDER BY hour",
            ARRAY_A
        );
        $Stats['requests_by_hour'] = $RequestsByHour;
        
        $this->SendJsonSuccess($Stats);
    }
    
    /**
     * Clear test history
     */
    public function ClearTestHistory()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        
        // Delete records older than specified days in settings
        $Settings = $this->GetPluginSettings();
        $RetentionDays = $Settings['log_retention_days'] ?? 30;
        
        $Deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $TableName WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $RetentionDays
        ));
        
        if ($Deleted !== false) {
            $this->SendJsonSuccess([
                'message' => sprintf(
                    __('Cleared %d old log entries', 'ninja-api-explorer'),
                    $Deleted
                ),
                'deleted_count' => $Deleted
            ]);
        } else {
            $this->SendJsonError(__('Failed to clear test history', 'ninja-api-explorer'));
        }
    }
    
    /**
     * Get test details
     */
    public function GetTestDetails()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        $TestId = intval($_POST['test_id'] ?? 0);
        
        if (!$TestId) {
            $this->SendJsonError(__('Test ID is required', 'ninja-api-explorer'), 400);
        }
        
        global $wpdb;
        
        $TableName = $wpdb->prefix . 'ninja_api_explorer_logs';
        
        $Test = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $TableName WHERE id = %d",
            $TestId
        ), ARRAY_A);
        
        if (!$Test) {
            $this->SendJsonError(__('Test not found', 'ninja-api-explorer'), 404);
        }
        
        // Process data
        $Test['request_data'] = json_decode($Test['request_data'], true);
        $Test['response_data'] = json_decode($Test['response_data'], true);
        $Test['created_at_formatted'] = $this->FormatDateTime($Test['created_at']);
        
        $this->SendJsonSuccess($Test);
    }
    
    /**
     * Bulk test endpoints
     */
    public function BulkTestEndpoints()
    {
        if (!$this->CheckPermission()) {
            $this->SendJsonError(__('Insufficient permissions', 'ninja-api-explorer'), 403);
        }
        
        $Endpoints = $_POST['endpoints'] ?? [];
        
        if (empty($Endpoints) || !is_array($Endpoints)) {
            $this->SendJsonError(__('Endpoints array is required', 'ninja-api-explorer'), 400);
        }
        
        $Results = [];
        $SuccessCount = 0;
        $FailureCount = 0;
        
        foreach ($Endpoints as $Endpoint) {
            $Url = $this->SanitizeInput($Endpoint['url'] ?? '', 'url');
            $Method = strtoupper($this->SanitizeInput($Endpoint['method'] ?? 'GET', 'text'));
            $Headers = $this->SanitizeHeaders($Endpoint['headers'] ?? []);
            $Body = $this->SanitizeInput($Endpoint['body'] ?? '', 'textarea');
            $Timeout = intval($Endpoint['timeout'] ?? 30);
            
            $Result = $this->ApiService->TestEndpoint($Url, $Method, $Headers, $Body, $Timeout);
            
            $Results[] = [
                'url' => $Url,
                'method' => $Method,
                'result' => $Result
            ];
            
            if ($Result['success']) {
                $SuccessCount++;
            } else {
                $FailureCount++;
            }
            
            // Log request
            $this->LogApiRequest($Url, $Method, $Headers, $Body, $Result);
            
            // Small delay between requests
            usleep(100000); // 0.1 seconds
        }
        
        $this->SendJsonSuccess([
            'results' => $Results,
            'summary' => [
                'total' => count($Endpoints),
                'successful' => $SuccessCount,
                'failed' => $FailureCount,
                'success_rate' => count($Endpoints) > 0 ? round(($SuccessCount / count($Endpoints)) * 100, 2) : 0
            ]
        ]);
    }
}