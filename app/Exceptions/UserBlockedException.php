<?php

namespace App\Exceptions;

use League\OAuth2\Server\Exception\OAuthServerException;

class UserBlockedException extends OAuthServerException
{
    public function render()
    {
        return response()->json([
            'status' => 'error',
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ], $this->getHttpStatusCode());
    }
}
