<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AdobeService
{
    public static function refreshAccessToken($user)
    {
        $response = Http::asForm()->post('https://ims-na1.adobelogin.com/ims/token/v3', [
            'client_id' => env('ADOBE_CLIENT_ID'),
            'client_secret' => env('ADOBE_CLIENT_SECRET'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->adobe_refresh_token,
        ]);

        $tokenData = $response->json();

        $user->adobe_access_token = $tokenData['access_token'];
        $user->adobe_refresh_token = $tokenData['refresh_token'];
        $user->adobe_token_expires_in = now()->addSeconds($tokenData['expires_in']);
        $user->save();
    }
}
