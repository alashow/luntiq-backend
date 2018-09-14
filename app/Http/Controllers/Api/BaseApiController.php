<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    /**
     * @param array $data
     * @param int   $status
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function ok($data = null, $status = 200, $headers = [])
    {
        return $this->response('ok', $data, null, $status, $headers);
    }

    /**
     * @param array $error
     * @param int   $status
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function error($error = null, $status = 403, $headers = [])
    {
        return $this->response('error', null, $error, $status, $headers);
    }

    /**
     * Not found response.
     *
     * @return JsonResponse
     */
    protected function notFound()
    {
        return $this->error('Not Found', 404);
    }

    /**
     * @param string $status
     * @param null   $data
     * @param null   $error
     * @param int    $httpStatus
     * @param array  $headers
     *
     * @return JsonResponse
     */
    protected function response($status = 'ok', $data = null, $error = null, $httpStatus = 200, $headers = [])
    {
        $result = ['status' => $status];
        if (! is_null($data)) {
            $result = array_merge($result, ['data' => $data]);
        }
        if (! is_null($error)) {
            $result = array_merge($result, ['error' => $error]);
        }

        return response()->json($result, $httpStatus, $headers);
    }
}
