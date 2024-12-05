<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Exception;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Exceptions\ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use App\Models\AuthenticatedUser;

class GoogleController extends Controller
{
    public function redirect() {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle() {

        $google_user = Socialite::driver('google')->stateless()->user();
        $user = AuthenticatedUser::where('google_id', $google_user->getId())->first();
      
        if (!$user) {

            $new_user = AuthenticatedUser::create([
                'name' => $google_user->getName(),
                'username' => $this->generateUsername($google_user->getName()), 
                'email' => $google_user->getEmail(),
                'password' => bcrypt('default_password'), 
                'reputation' => 0, 
                'is_suspended' => false, 
                'creation_date' => now(), 
                'birth_date' => now(), 
                'description' => null, 
                'is_admin' => false, 
                'image_id' => null,
                'google_id' => $google_user->getId(),
            ]);

            Auth::login($new_user);
       
        } else {
            Auth::login($user);
        }

        // After login, redirect to homepage
        return redirect()->intended('home');
    }

    private function generateUsername($name)
    {
        
        $base_username = preg_replace('/\s+/', '_', strtolower($name));
        $username = $base_username;
        $counter = 1;

        while (AuthenticatedUser::where('username', $username)->exists()) {
            $username = $base_username . '_' . $counter;
            $counter++;
        }

        return $username;
    }

}
