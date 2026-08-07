@props(['rows'])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 8px 0 24px; border:1px solid #E5E7EB; border-radius:8px;">
    @foreach ($rows as $label => $value)
        <tr>
            <td style="padding:10px 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:13px; color:#6B7280; white-space:nowrap; {{ ! $loop->last ? 'border-bottom:1px solid #E5E7EB;' : '' }}">
                {{ $label }}
            </td>
            <td style="padding:10px 14px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:13px; color:#0F1115; text-align:right; word-break:break-all; {{ ! $loop->last ? 'border-bottom:1px solid #E5E7EB;' : '' }}">
                {{ $value }}
            </td>
        </tr>
    @endforeach
</table>
