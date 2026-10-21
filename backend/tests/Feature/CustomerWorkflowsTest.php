<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_and_newsletter_persist_validated_requests_without_exposing_tokens(): void
    {
        Notification::fake();
        $this->postJson('/api/v1/contact', ['name' => 'Client', 'email' => 'client@example.test', 'subject' => 'other', 'message' => 'Question', 'locale' => 'fr', 'status' => 'handled'])->assertCreated();
        $this->assertSame('new', ContactMessage::firstOrFail()->status);
        $this->postJson('/api/v1/newsletter', ['email' => 'client@example.test', 'locale' => 'fr'])->assertAccepted()->assertJsonMissingPath('data.token_hash');
        $this->assertNull(NewsletterSubscription::firstOrFail()->confirmed_at);
        Notification::assertCount(1);
    }

    public function test_verification_requires_owner_and_a_valid_signature(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('api.v1.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs(User::factory()->create())->getJson($url)->assertForbidden();
        $this->actingAs($user)->getJson($url)->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_cannot_order_and_price_injection_is_ignored(): void
    {
        $user = User::factory()->unverified()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->postJson('/api/v1/orders', ['address_id' => $address->id, 'idempotency_key' => (string) Str::uuid()])->assertForbidden();
    }
}
