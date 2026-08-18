<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials do not match our records.'),
            ]);
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Please contact support.'),
            ]);
        }

        if (!$user->isSuperAdmin() && $user->school && $user->school->status === 'suspended') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your organization account has been suspended. Please contact your administrator.'),
            ]);
        }

        $request->session()->regenerate();
        $user->recordLogin();

        $pendingJoinRoomCode = $request->session()->pull('pending_join_room_code');
        if ($pendingJoinRoomCode) {
            return redirect()->route('join.room', ['roomCode' => $pendingJoinRoomCode]);
        }

        return redirect()->to($this->redirectTo($user));
    }

    protected function redirectTo($user): string
    {
        return match ($user->role?->slug) {
            'super_admin' => route('super-admin.dashboard'),
            'school_owner', 'school_admin' => route('school.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'student' => route('student.dashboard'),
            'parent' => route('parent.dashboard'),
            default => route('no-role'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
