<?php

namespace TelexApm\TelexAPM\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class MetricsService
{

    public function collectMetrics($request, $response, $duration, $exception)
    {
        $metrics = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        if ($exception instanceof NotFoundHttpException) {
            $telex_msg = "";
            $this->notFoundExceptionNotification($telex_msg);
        }

        if ($exception instanceof \Exception){
            $telex_msg = "";
            $this->internalServerExceptionNotification($telex_msg);

        }

        $telex_msg = "";
        $this->otherUnhandleErrors($telex_msg);
    }

    public function TelexNotification($path, $event_name, $message, $status, $user_name = null){
        Http::get($path, [
            "event_name" => $event_name,
            "message" => $message,
            "status" => $status,
            "username" => $user_name,
        ]);

        return true;
    }

    public function notFoundExceptionNotification($telex_msg){
        try {
                        
            $webhookUrl = Config::get('myapm.404_errors');
            $app_name = Config::get('myapm.app_name');

            $this->TelexNotification($webhookUrl, "Page Not Found - 404", $telex_msg, "error",  $app_name);    

        } catch (Throwable $ex) {            
            Log::error($ex->getMessage());
        }
    }

    public function internalServerExceptionNotification($telex_msg){
        try {
            
            $webhookUrl = Config::get('myapm.500_errors');
            $app_name = Config::get('myapm.app_name');
        
            $this->TelexNotification($webhookUrl, "Internal Server Error - 500", $telex_msg, "error",  $app_name);    

        } catch (Throwable $ex) {            
            Log::error($ex->getMessage());
        }
    }

    public function otherUnhandleErrors($telex_msg){
        try {
            
            $webhookUrl = Config::get('myapm.DEFAULT_WEBHOOK_URL');
            $app_name = Config::get('myapm.app_name');
        
            $this->TelexNotification($webhookUrl, "Unhanlded Error - 500", $telex_msg, "error",  $app_name);    

        } catch (Throwable $ex) {            
            Log::error($ex->getMessage());
        }
    }
}
