<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): UserResource
    {
        $request->merge(['email' => strtolower((string) $request->input('email'))]);
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email:rfc|max:254|unique:users,email', 'password' => ['required', 'confirmed', PasswordRule::min(12)->max(128)], 'locale' => 'sometimes|in:fr,en']);
        $data['email'] = Str::lower($data['email']);
        $user = User::create(collect($data)->only(['name', 'email', 'password'])->all());
        $user->forceFill(['locale' => $data['locale'] ?? 'fr'])->save();
        event(new Registered($user));
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return new UserResource($user);
    }

    public function login(Request $request): UserResource
    {
        $data = $request->validate(['email' => 'required|email|max:254', 'password' => 'required|string|max:128']);
        if (! Auth::guard('web')->attempt(['email' => Str::lower($data['email']), 'password' => $data['password']])) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        $request->session()->regenerate();

        return new UserResource($request->user('web'));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::forgetGuards();

        return response()->json(['data' => ['message' => 'Signed out.']]);
    }

    public function forgot(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:254']);
        Password::sendResetLink(['email' => Str::lower($data['email'])]);

        return response()->json(['data' => ['message' => 'If an account matches this address, a reset link will be sent.']]);
    }

    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:254', 'token' => 'required|string|max:256', 'password' => ['required', 'confirmed', PasswordRule::min(12)->max(128)]]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['token' => 'Invalid or expired reset link.']);
        }

        return response()->json(['data' => ['message' => 'Password reset.']]);
    }

    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        abort_unless($request->user()->id === $id && hash_equals(sha1($request->user()->getEmailForVerification()), $hash), 403);
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json(['data' => ['verified' => true]]);
    }

    public function resend(Request $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return response()->json(['data' => ['message' => 'Verification requested.']]);
    }
}
