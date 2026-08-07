<?php

namespace App\Services\Webhooks\Verifiers;

use App\Enums\ProviderKey;

class ProviderVerifierFactory
{
    public function make(ProviderKey $key): ProviderVerifierInterface
    {
        return match ($key) {
            ProviderKey::Stripe => new StripeVerifier,
            ProviderKey::Paystack => new PaystackVerifier,
            ProviderKey::Flutterwave => new FlutterwaveVerifier,
            ProviderKey::GitHub => new GitHubVerifier,
            ProviderKey::Shopify => new ShopifyVerifier,
            ProviderKey::GenericHmac => new GenericHmacVerifier,
        };
    }
}
