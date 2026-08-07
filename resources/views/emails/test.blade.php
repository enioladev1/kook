<x-mail.layout heading="Test email" preheader="Your Kook email settings are working.">
    <p style="margin:0 0 16px;">
        This is a test email from Kook. If you're reading this, the email
        credentials you saved are working correctly.
    </p>

    <x-mail.table :rows="[
        'Sent at' => now()->toDayDateTimeString(),
    ]" />
</x-mail.layout>
