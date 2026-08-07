<?php

namespace Database\Seeders;

use App\Enums\ProviderKey;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'key' => ProviderKey::Stripe,
                'name' => 'Stripe',
                'docs_url' => 'https://docs.stripe.com/webhooks',
            ],
            [
                'key' => ProviderKey::Paystack,
                'name' => 'Paystack',
                'docs_url' => 'https://paystack.com/docs/payments/webhooks',
            ],
            [
                'key' => ProviderKey::Flutterwave,
                'name' => 'Flutterwave',
                'docs_url' => 'https://developer.flutterwave.com/docs/webhooks',
            ],
            [
                'key' => ProviderKey::GitHub,
                'name' => 'GitHub',
                'docs_url' => 'https://docs.github.com/webhooks',
            ],
            [
                'key' => ProviderKey::Shopify,
                'name' => 'Shopify',
                'docs_url' => 'https://shopify.dev/docs/apps/build/webhooks',
            ],
            [
                'key' => ProviderKey::GenericHmac,
                'name' => 'Generic HMAC',
                'docs_url' => null,
            ],
        ];

        foreach ($providers as $provider) {
            Provider::query()->updateOrCreate(
                ['key' => $provider['key']],
                ['name' => $provider['name'], 'docs_url' => $provider['docs_url'], 'is_active' => true],
            );
        }
    }
}
