<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Signals the queue worker to retry - thrown after every failed delivery
 * attempt, including the final one, so Laravel's own retry/backoff/tries
 * machinery drives the schedule and moves it to failed_jobs once exhausted.
 */
class WebhookDeliveryFailedException extends RuntimeException {}
