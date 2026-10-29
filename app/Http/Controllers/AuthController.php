<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function sendWelcomeEmail(Request $request)
    {
        $user = [
            'name' => 'Amelinda',
            'email' => 'amelinda@gmail.com'
        ];

        // Pengiriman Email
        Mail::to($user['email'])->send(new WelcomeMail($user));

        return response()->json(['message' => 'Email verifikasi/selamat datang berhasil dikirim!']);
    }
}