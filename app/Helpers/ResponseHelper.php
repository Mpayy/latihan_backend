<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data = null, $message = 'Succses', $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'Error', $errors = null, $status = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}