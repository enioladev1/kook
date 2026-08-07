<?php

namespace App\Enums;

enum WebhookEndpointStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Disabled = 'disabled';
}
