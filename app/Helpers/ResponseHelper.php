<?php

use Illuminate\Http\JsonResponse;

if (! function_exists('responseJSON')) {
    function responseJSON($message = '', $result = [], $httpCode = 200, $responseCode = null): JsonResponse
    {
        $responseCode = $responseCode ?? ($httpCode >= 400 ? 'ERROR' : 'SUCCESS');

        return response()->json([
            'code' => $responseCode,
            'results' => $result,
            'message' => $message,
        ], $httpCode);
    }
}
