<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthenticatedUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.recover_pass');
    }


    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->email]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $token = Str::random(60);
        $resetLink = url('/reset-password/' . $token); 
        

        Mail::to($request->email)->send(new PasswordResetMail($resetLink));

        return back()->with('success', 'Password reset link sent!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = AuthenticatedUser::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No user found with this email address.']);
        }

        // Atualizar a senha do usuário
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Password has been reset successfully!');
    }

}

