<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class ExtensionBlockedException extends \Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'extension_is_blocked',
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
