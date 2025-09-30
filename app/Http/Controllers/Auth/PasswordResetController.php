<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm(Request $request)
    {
        $userType = $request->get('type', 'voter');
        return view('auth.passwords.email', compact('userType'));
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userType = $request->get('type', 'voter');
        $broker = $this->getBroker($userType);

        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        $userType = $request->get('type', 'voter');
        return view('auth.passwords.reset', compact('token', 'userType'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $userType = $request->get('type', 'voter');
        $broker = $this->getBroker($userType);

        $status = Password::broker($broker)->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route($this->getLoginRoute($userType))->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    private function getBroker($userType)
    {
        return match($userType) {
            'admin' => 'admins',
            'candidate' => 'candidates',
            'observer' => 'observers',
            default => 'users',
        };
    }

    private function getLoginRoute($userType)
    {
        return match($userType) {
            'admin' => 'admin.login',
            'candidate' => 'candidate.login',
            'observer' => 'observer.login',
            default => 'voter.login',
        };
    }
}