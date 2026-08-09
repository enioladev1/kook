<x-mail.layout heading="Webhook delivery failing" preheader="{{ $endpointName }} has stopped receiving deliveries.">
    <p style="margin:0 0 16px;">
        Kook has exhausted all retry attempts for a webhook delivery on
        <strong>{{ $endpointName }}</strong>. No further retries will be
        attempted for this event.
    </p>

    <x-mail.table :rows="[
        'Project' => $projectName,
        'Endpoint' => $endpointName,
        'Destination' => $destinationUrl,
        'Event' => $eventName ?? 'n/a',
        'Failed at' => now()->toDayDateTimeString(),
    ]" />

    <p style="margin:0;">
        You can turn off these emails for this project from its settings tab.
    </p>
</x-mail.layout>
