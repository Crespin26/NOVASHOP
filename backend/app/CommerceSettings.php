<?php

namespace App;

use Illuminate\Support\Facades\DB;

class CommerceSettings
{
    public function values(): array
    {
        $base = collect(config('commerce'))->only(['enabled', 'currency', 'countries', 'taxes_configured', 'prices_include_tax', 'shipping', 'free_shipping_threshold', 'returns_days'])->all();
        $stored = DB::table('commerce_settings')->where('key', 'commercial')->value('value');

        return array_replace($base, $stored ? json_decode($stored, true, 32, JSON_THROW_ON_ERROR) : []);
    }

    public function apply(): void
    {
        foreach ($this->values() as $key => $value) {
            config(['commerce.'.$key => $value]);
        }
    }
}
