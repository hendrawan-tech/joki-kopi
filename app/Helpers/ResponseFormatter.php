<?php

namespace App\Helpers;

class ResponseFormatter
{
    public static function success($data = null, $code = 200)
    {
        $response = [
            'meta' => [
                'code' => $code,
                'status' => true,
                'message' => 'success',
            ],
            'data' => $data,
        ];
        return response()->json($response);
    }

    public static function error($data = null, $message = 'Failed', $code = 400)
    {
        $response = [
            'meta' => [
                'code' => $code,
                'status' => false,
                'message' => $message,
            ],
            'data' => $data,
        ];

        return response()->json($response);
    }
}
