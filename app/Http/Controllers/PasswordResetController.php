<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;

class PasswordResetController extends Controller
{
    public function showResetForm()
    {
        return view('auth.recover_pass'); 
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $token = Str::random(60);
        $resetLink = url('/reset-password/' . $token); 
        

        Mail::to($request->email)->send(new PasswordResetMail($resetLink));

        return back()->with('success', 'Password reset link sent!');
    }
}

