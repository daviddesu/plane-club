<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AdobeLightroomService
{
    public static function getAlbums($user)
    {
        // Ensure the access token is valid
        if (now()->greaterThanOrEqualTo($user->adobe_token_expires_in)) {
            AdobeService::refreshAccessToken($user);
        }

        $response = Http::withToken($user->adobe_access_token)
            ->get('https://lr.adobe.io/v2/albums');

        return $response->json();
    }

    public static function getAlbumAssets($user, $albumId)
    {
        if (now()->greaterThanOrEqualTo($user->adobe_token_expires_in)) {
            AdobeService::refreshAccessToken($user);
        }

        $response = Http::withToken($user->adobe_access_token)
            ->get("https://lr.adobe.io/v2/albums/{$albumId}/assets");

        return $response->json();
    }

    public static function getAssetDownloadUrl($user, $assetId)
    {
        if (now()->greaterThanOrEqualTo($user->adobe_token_expires_in)) {
            AdobeService::refreshAccessToken($user);
        }

        $response = Http::withToken($user->adobe_access_token)
            ->get("https://lr.adobe.io/v2/assets/{$assetId}/renditions");

        // Parse the response to get the download URL
        return $response->json();
    }
}
