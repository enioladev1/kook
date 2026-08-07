<?php

namespace App\Enums;

enum ProviderKey: string
{
    case Stripe = 'stripe';
    case Paystack = 'paystack';
    case Flutterwave = 'flutterwave';
    case GitHub = 'github';
    case Shopify = 'shopify';
    case GenericHmac = 'generic_hmac';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::Paystack => 'Paystack',
            self::Flutterwave => 'Flutterwave',
            self::GitHub => 'GitHub',
            self::Shopify => 'Shopify',
            self::GenericHmac => 'Generic HMAC',
        };
    }
}
