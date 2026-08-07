<?php

namespace App\Enums;

enum WebhookEndpointMode: string
{
    case Relay = 'relay';
    case Managed = 'managed';
}
