<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function checkAuth(Request $request)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            return response()->json(['message' => 'Authenticated'], 200);
        } else {
            return response()->json(['message' => 'Not Authenticated'], 401);
        }
    }
}
