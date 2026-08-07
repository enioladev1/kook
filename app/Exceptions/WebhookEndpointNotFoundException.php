<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown for both a nonexistent ingest token and a paused/disabled endpoint -
 * the two are deliberately indistinguishable to callers so a token cannot be
 * probed to learn whether it is merely inactive rather than invalid.
 */
class WebhookEndpointNotFoundException extends RuntimeException {}
