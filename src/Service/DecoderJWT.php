<?php

namespace App\Service;

class DecoderJWT
{
    public static function decode($token)
    {
        $split = explode('.', $token);
        $payload = json_decode(base64_decode($split[1]), true);
        return [
            'exp' => $payload['exp'],
            'username' => $payload['username'],
            'roles' => $payload['roles']
        ];
    }

}