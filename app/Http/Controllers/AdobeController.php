<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdobeController extends Controller
{
    public function redirectToAdobe()
    {
        $query = http_build_query([
            'client_id' => env('ADOBE_CLIENT_ID'),
            'redirect_uri' => route('auth.adobe.callback'),
            'response_type' => 'code',
            'scope' => 'lr_partner_apis',
        ]);

        return redirect('https://ims-na1.adobelogin.com/ims/authorize/v2?' . $query);
    }

    public function handleAdobeCallback(Request $request)
    {
        $code = $request->get('code');

        // Exchange the authorization code for an access token
        $response = Http::asForm()->post('https://ims-na1.adobelogin.com/ims/token/v3', [
            'client_id' => env('ADOBE_CLIENT_ID'),
            'client_secret' => env('ADOBE_CLIENT_SECRET'),
            'redirect_uri' => route('auth.adobe.callback'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        $tokenData = $response->json();

        // Save the access token and refresh token in the user's session or database
        // For example, you might save it in the user's profile
        $user = auth()->user();
        $user->adobe_access_token = $tokenData['access_token'];
        $user->adobe_refresh_token = $tokenData['refresh_token'];
        $user->adobe_token_expires_in = now()->addSeconds($tokenData['expires_in']);
        $user->save();

        return redirect()->route('profile')->with('success', 'Adobe Lightroom connected successfully.');
    }
}
