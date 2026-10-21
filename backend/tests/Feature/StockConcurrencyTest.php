<?php

namespace Tests\Feature;

use App\CommerceService;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StockConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_buyers_cannot_reserve_the_last_unit(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl is required for the concurrent PostgreSQL test.');
        }
        config(['commerce.enabled' => true, 'commerce.taxes_configured' => true, 'commerce.countries' => ['BJ']]);
        $product = Product::factory()->create(['status' => 'published']);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 1]);
        $buyers = [];
        for ($i = 0; $i < 2; $i++) {
            $user = User::factory()->create();
            $address = Address::factory()->create(['user_id' => $user->id]);
            CartItem::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);
            $buyers[] = [$user->id, $address->id];
        }
        DB::disconnect();
        $children = [];
        foreach ($buyers as [$userId,$addressId]) {
            $file = tempnam(sys_get_temp_dir(), 'nova-stock-');
            $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
            $pid = pcntl_fork();
            if ($pid === 0) {
                fclose($pair[0]);
                fread($pair[1], 1);
                try {
                    app(CommerceService::class)->createOrder(User::findOrFail($userId), Address::findOrFail($addressId), (string) Str::uuid());
                    $status = 201;
                } catch (HttpException $error) {
                    $status = $error->getStatusCode();
                } catch (\Throwable) {
                    $status = 500;
                }file_put_contents($file, (string) $status);
                DB::disconnect();
                exit(0);
            }
            $this->assertGreaterThan(0, $pid);
            fclose($pair[1]);
            $children[] = [$pid, $pair[0], $file];
        }
        foreach ($children as [$pid,$socket,$file]) {
            fwrite($socket, '1');
            fclose($socket);
        }
        $statuses = [];
        foreach ($children as [$pid,$socket,$file]) {
            pcntl_waitpid($pid, $status);
            $statuses[] = (int) file_get_contents($file);
            unlink($file);
        }
        sort($statuses);
        $this->assertSame([201, 409], $statuses);
        $this->assertSame(0, $variant->fresh()->stock);
        $this->assertSame(1, Order::count());
    }
}
