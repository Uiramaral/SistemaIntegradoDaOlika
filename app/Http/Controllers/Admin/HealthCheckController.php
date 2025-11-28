<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    protected $healthCheckService;

    public function __construct(HealthCheckService $healthCheckService)
    {
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Página de health check
     */
    public function index()
    {
        $health = $this->healthCheckService->checkSystemHealth();
        
        return view('admin.health', compact('health'));
    }

    /**
     * API: Health check JSON
     */
    public function api()
    {
        $response = $this->healthCheckService->getHealthCheckResponse();
        
        return response()->json($response['data'], $response['status_code']);
    }
}
