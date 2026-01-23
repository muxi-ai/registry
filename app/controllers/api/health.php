<?php

/**
 * Health Check API Endpoint
 * 
 * Provides system health status for monitoring and load balancers
 */
class ApiHealth extends TinyController
{
    /**
     * GET /api/health
     * 
     * Returns health status of the registry
     */
    public function get($request, $response)
    {
        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'checks' => []
        ];

        // Check database connectivity
        try {
            tiny::db()->query('SELECT 1')->fetch();
            $health['checks']['database'] = 'connected';
        } catch (Exception $e) {
            $health['status'] = 'degraded';
            $health['checks']['database'] = 'error';
            tiny::log('Health check: Database connection failed', ['error' => $e->getMessage()]);
        }

        // Check GitHub API reachability (optional, don't fail if unavailable)
        try {
            $github = tiny::github(null, 'MUXI-Registry-Health');
            // Quick API call to check connectivity
            $response = tiny::http()->get('https://api.github.com/rate_limit', [
                'headers' => [
                    'Accept: application/vnd.github+json',
                    'User-Agent: MUXI-Registry-Health'
                ],
                'timeout' => 2
            ]);
            
            if (isset($response->json) && isset($response->json->rate)) {
                $health['checks']['github_api'] = 'reachable';
            } else {
                $health['checks']['github_api'] = 'unknown';
            }
        } catch (Exception $e) {
            // GitHub API issues shouldn't mark system as unhealthy
            $health['checks']['github_api'] = 'unreachable';
            tiny::log('Health check: GitHub API unreachable', ['error' => $e->getMessage()]);
        }

        // Check disk space (if available)
        $tempDir = sys_get_temp_dir();
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            $free = disk_free_space($tempDir);
            $total = disk_total_space($tempDir);
            
            if ($free !== false && $total !== false) {
                $percentFree = round(($free / $total) * 100, 1);
                $health['checks']['disk_space'] = [
                    'free_percent' => $percentFree,
                    'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                    'total_gb' => round($total / 1024 / 1024 / 1024, 2)
                ];
                
                // Warn if less than 10% free
                if ($percentFree < 10) {
                    $health['status'] = 'degraded';
                    tiny::log('Health check: Low disk space', ['percent_free' => $percentFree]);
                }
            }
        }

        // Return appropriate HTTP status code
        $statusCode = ($health['status'] === 'ok') ? 200 : 503;
        
        return $response->sendJSON($health, $statusCode);
    }
}
