<x-mail.layout heading="Reset your password" preheader="This link expires in {{ $expiresInMinutes }} minutes.">
    <p style="margin:0 0 16px;">
        A password reset was requested for your Kook admin account. Click
        below to choose a new password.
    </p>

    <x-mail.button :url="$resetUrl">Reset password</x-mail.button>

    <p style="margin:16px 0 0; color:#6B7280; font-size:13px;">
        This link expires in {{ $expiresInMinutes }} minutes. If you didn't
        request this, you can safely ignore this email.
    </p>
</x-mail.layout>
