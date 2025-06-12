<?php

namespace App\Exceptions;

use Exception;

class CodeException extends Exception
{
    public $message;
    public $code;
    public $data;
    public $statusCode;

    public function __construct($code, $message, $data = [], $statusCode = 200, Exception $previous = NULL)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ], $this->statusCode);
    }
}
