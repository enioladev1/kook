Webhook delivery failing

Kook has exhausted all retry attempts for a webhook delivery on {{ $endpointName }}. No further retries will be attempted for this event.

Project: {{ $projectName }}
Endpoint: {{ $endpointName }}
Destination: {{ $destinationUrl }}
Event: {{ $eventName ?? 'n/a' }}
Failed at: {{ now()->toDayDateTimeString() }}

You can turn off these emails for this project from its settings tab.

--
Sent by Kook · self-hosted webhook infrastructure
