<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirebaseAuthController extends Controller
{
    public function __construct(
        protected FirebaseAuth $firebase
    ) {
    }

    public function login(Request $request)
    {
        $request->validate([
            'idToken' => 'required'
        ]);

        try {

            $verifiedToken =
                $this->firebase
                    ->verifyIdToken($request->idToken);

            $uid =
                $verifiedToken->claims()->get('sub');

            $firebaseUser =
                $this->firebase->getUser($uid);

            $user =
                User::where(
                    'email',
                    $firebaseUser->email
                )->first();

            if (!$user) {

                $user = User::create([
                    'firebase_uid' => $uid,
                    'name' => $firebaseUser->displayName ?: explode('@', $firebaseUser->email)[0],
                    'email' => $firebaseUser->email,
                    'role' => 'user',
                    'password' => Hash::make(Str::random(40)),
                ]);

            } else {

                $user->update([
                    'firebase_uid' => $uid
                ]);

            }

            Auth::login($user);

            return response()->json([

                'success' => true,

                'redirect' =>
                    $user->role == 'admin'
                    ? route('admin.dashboard')
                    : url('/')

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 401);

        }

    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}