<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t): void {
            $t->boolean('is_admin')->default(false);
            $t->string('locale', 2)->default('fr');
        });
        DB::statement('CREATE UNIQUE INDEX users_email_casefold ON users (lower(email))');
        Schema::create('addresses', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('label', 120);
            $t->string('name', 120);
            $t->string('street', 240);
            $t->string('city', 120);
            $t->string('postal', 30);
            $t->string('country', 2);
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });
        DB::statement('CREATE UNIQUE INDEX one_default_address_per_user ON addresses (user_id) WHERE is_default = true');
        Schema::create('categories', function (Blueprint $t): void {
            $t->id();
            $t->string('slug', 100)->unique();
            $t->json('name');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->string('slug', 100)->unique();
            $t->json('name');
            $t->json('description');
            $t->json('specifications')->nullable();
            $t->string('brand', 100);
            $t->string('status', 20)->default('draft')->index();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('sku', 100)->unique();
            $t->json('options')->nullable();
            $t->bigInteger('price');
            $t->bigInteger('compare_at_price')->nullable();
            $t->string('currency', 3);
            $t->integer('stock')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        DB::statement('ALTER TABLE product_variants ADD CONSTRAINT valid_variant_values CHECK (price >= 0 AND price <= 100000000 AND stock >= 0 AND (compare_at_price IS NULL OR compare_at_price > price))');
        Schema::create('product_images', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('path')->unique();
            $t->json('alt');
            $t->integer('position')->default(0);
            $t->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->integer('delta');
            $t->integer('balance');
            $t->string('reason', 240);
            $t->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->integer('quantity');
            $t->timestamps();
            $t->unique(['user_id', 'product_variant_id']);
        });
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT valid_cart_quantity CHECK (quantity BETWEEN 1 AND 99)');
        Schema::create('favorites', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
        });
        Schema::create('orders', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->uuid('reference')->unique();
            $t->uuid('idempotency_key');
            $t->string('request_hash', 64);
            $t->string('status', 30)->default('awaiting_payment')->index();
            $t->string('currency', 3);
            $t->bigInteger('subtotal');
            $t->bigInteger('shipping');
            $t->bigInteger('total');
            $t->text('address_snapshot');
            $t->string('locale', 2);
            $t->timestamp('expires_at')->index();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('shipped_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->string('carrier', 100)->nullable();
            $t->string('tracking_number', 120)->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'idempotency_key']);
        });
        Schema::create('order_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $t->string('sku', 100);
            $t->json('name');
            $t->json('options')->nullable();
            $t->integer('quantity');
            $t->bigInteger('unit_price');
            $t->bigInteger('total');
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $t->string('session_id')->nullable()->unique();
            $t->text('checkout_url')->nullable();
            $t->string('payment_intent')->nullable()->unique();
            $t->string('status', 30)->default('pending');
            $t->bigInteger('amount');
            $t->string('currency', 3);
            $t->timestamps();
        });
        Schema::create('stripe_events', function (Blueprint $t): void {
            $t->string('id')->primary();
            $t->string('type');
            $t->timestamp('processed_at');
        });
        Schema::create('return_requests', function (Blueprint $t): void {
            $t->id();
            $t->uuid('reference')->unique();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('order_item_id')->unique()->constrained()->restrictOnDelete();
            $t->integer('quantity');
            $t->text('reason');
            $t->string('status', 30)->default('requested');
            $t->string('decision_reason', 500)->nullable();
            $t->string('refund_id')->nullable()->unique();
            $t->boolean('restocked')->default(false);
            $t->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $t): void {
            $t->id();
            $t->string('name', 100);
            $t->string('email', 254);
            $t->string('subject', 100);
            $t->text('message');
            $t->string('locale', 2);
            $t->string('status', 20)->default('new');
            $t->timestamps();
        });
        Schema::create('newsletter_subscriptions', function (Blueprint $t): void {
            $t->id();
            $t->string('email', 254)->unique();
            $t->string('locale', 2);
            $t->string('token_hash', 64)->unique();
            $t->timestamp('token_expires_at');
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('commerce_settings', function (Blueprint $t): void {
            $t->string('key')->primary();
            $t->json('value');
            $t->timestamps();
        });
        Schema::create('audit_entries', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 100);
            $t->string('subject_type', 100);
            $t->string('subject_id', 100);
            $t->json('metadata')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['commerce_settings', 'audit_entries', 'newsletter_subscriptions', 'contact_messages', 'return_requests', 'stripe_events', 'payments', 'order_items', 'orders', 'favorites', 'cart_items', 'stock_movements', 'product_images', 'product_variants', 'products', 'categories', 'addresses'] as $table) {
            Schema::dropIfExists($table);
        }Schema::table('users', function (Blueprint $t): void {
            $t->dropColumn(['is_admin', 'locale']);
        });
    }
};
