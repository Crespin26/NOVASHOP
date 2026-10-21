<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceResource;
use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function update(Request $request): UserResource
    {
        $user = $request->user();
        if ($request->has('email')) {
            $request->merge(['email' => strtolower((string) $request->input('email'))]);
        }
        $data = $request->validate(['name' => 'sometimes|required|string|max:100', 'email' => 'sometimes|required|email|max:254|unique:users,email,'.$user->id, 'locale' => 'sometimes|in:fr,en']);
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $request->validate(['current_password' => 'required|current_password:web']);
            $data['email'] = strtolower($data['email']);
            $data['email_verified_at'] = null;
        }
        $user->forceFill($data)->save();
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return new UserResource($user);
    }

    public function password(Request $request): JsonResponse
    {
        $data = $request->validate(['current_password' => 'required|current_password:web', 'password' => ['required', 'confirmed', Password::min(12)->max(128)]]);
        $request->user()->forceFill(['password' => Hash::make($data['password']), 'remember_token' => Str::random(60)])->save();
        DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
        $request->session()->regenerate();

        return response()->json(['data' => ['message' => 'Password updated.']]);
    }

    public function addresses(Request $request): AnonymousResourceCollection
    {
        return CommerceResource::collection(Address::where('user_id', $request->user()->id)->orderByDesc('is_default')->orderBy('id')->paginate(20));
    }

    public function saveAddress(Request $request, ?Address $address = null): CommerceResource
    {
        abort_if($address && $address->user_id !== $request->user()->id, 404);
        $data = $request->validate(['label' => 'required|string|max:120', 'name' => 'required|string|max:120', 'street' => 'required|string|max:240', 'city' => 'required|string|max:120', 'postal' => 'required|string|max:30', 'country' => ['required', 'string', Rule::in(config('markets.countries'))], 'is_default' => 'sometimes|boolean']);

        return DB::transaction(function () use ($request, $address, $data): CommerceResource {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $count = Address::where('user_id', $request->user()->id)->count();
            abort_if(! $address && $count >= 20, 422, 'Address limit reached.');
            $makeDefault = ($data['is_default'] ?? false) || $count === 0;
            if ($makeDefault) {
                Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
            }
            $address ??= new Address;
            $address->fill($data);
            $address->user_id = $request->user()->id;
            $address->is_default = $makeDefault || ($address->is_default && ! array_key_exists('is_default', $data));
            $address->save();
            if (! Address::where('user_id', $request->user()->id)->where('is_default', true)->exists()) {
                $address->update(['is_default' => true]);
            }

            return new CommerceResource($address->refresh());
        });
    }

    public function deleteAddress(Request $request, Address $address): JsonResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        DB::transaction(function () use ($address, $request): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $address->delete();
            if ($address->is_default) {
                Address::where('user_id', $request->user()->id)->orderBy('id')->first()?->update(['is_default' => true]);
            }
        });

        return response()->json(null, 204);
    }

    public function requestDeletion(Request $request): JsonResponse
    {
        $request->validate(['current_password' => 'required|current_password:web']);
        ContactMessage::create(['name' => $request->user()->name, 'email' => $request->user()->email, 'subject' => 'account-deletion', 'message' => 'Account deletion request for user '.$request->user()->id, 'locale' => $request->user()->locale]);

        return response()->json(['data' => ['message' => 'Deletion request received. Account remains active until reviewed.']], 202);
    }
}
