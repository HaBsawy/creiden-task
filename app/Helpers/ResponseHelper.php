<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Get general response format
     *
     * @param $data
     * @param string $msg
     * @param bool $isSuccess
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function make($data, $msg = '', $isSuccess = true, $statusCode = 200)
    {
        return response()->json([
            'msg'           => $msg,
            'isSuccess'     => $isSuccess,
            'statusCode'    => $statusCode,
            'payload'       => $data
        ], $statusCode);
    }

    /**
     * Get not found response
     *
     * @return JsonResponse
     */
    public static function notFound()
    {
        return self::make(null, 'Not Found', false, 404);
    }

    /**
     * Get not authenticated response
     *
     * @return JsonResponse
     */
    public static function notAuthenticated()
    {
        return self::make(null, 'Not Authenticated', false, 401);
    }

    /**
     * Get not access response
     *
     * @return JsonResponse
     */
    public static function notAccess()
    {
        return self::make(null, 'Not Access', false, 403);
    }

    /**
     * Get validation error response
     *
     * @param string $msg
     * @return JsonResponse
     */
    public static function validationError($msg)
    {
        return self::make(null, $msg, false, 422);
    }

    /**
     * Get went wrong response
     *
     * @return JsonResponse
     */
    public static function wentWrong()
    {
        return self::make(null, 'Something went wrong', false, 500);
    }
}
