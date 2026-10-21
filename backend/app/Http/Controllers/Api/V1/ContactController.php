<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscription;
use App\Notifications\CommerceNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:254', 'subject' => 'required|in:product,order,other', 'message' => 'required|string|max:2000', 'locale' => 'required|in:fr,en']);
        ContactMessage::create($data);

        return response()->json(['data' => ['message' => 'Message received.']], 201);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:254', 'locale' => 'required|in:fr,en']);
        $token = Str::random(64);
        $email = strtolower($data['email']);
        $subscription = NewsletterSubscription::firstOrNew(['email' => $email]);
        if (! $subscription->confirmed_at || $subscription->unsubscribed_at) {
            $subscription->fill(['locale' => $data['locale'], 'token_hash' => hash('sha256', $token), 'token_expires_at' => now()->addHour()])->save();
            $url = rtrim(config('app.url'), '/').'/api/v1/newsletter/confirm/'.$token;
            Notification::route('mail', $email)->notify((new CommerceNotification($data['locale'] === 'en' ? 'Confirm subscription' : 'Confirmer l’inscription', $data['locale'] === 'en' ? 'Confirm your newsletter subscription.' : 'Confirmez votre inscription à la newsletter.', $url))->locale($data['locale']));
        }

        return response()->json(['data' => ['message' => 'If needed, a confirmation email will be sent.']], 202);
    }

    public function confirm(string $token): JsonResponse
    {
        abort_unless(preg_match('/^[a-zA-Z0-9]{64}$/', $token), 404);
        $subscription = NewsletterSubscription::where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($subscription->token_expires_at->isPast(), 410);
        $subscription->update(['confirmed_at' => now(), 'unsubscribed_at' => null]);

        return response()->json(['data' => ['confirmed' => true]]);
    }

    public function unsubscribe(string $token): JsonResponse
    {
        abort_unless(preg_match('/^[a-zA-Z0-9]{64}$/', $token), 404);
        NewsletterSubscription::where('token_hash', hash('sha256', $token))->firstOrFail()->update(['unsubscribed_at' => now()]);

        return response()->json(['data' => ['unsubscribed' => true]]);
    }
}
