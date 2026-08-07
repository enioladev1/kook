@props(['url'])
<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 8px 0 24px;">
    <tr>
        <td bgcolor="#FF7A33" style="background-color:#FF7A33; border-radius:8px;">
            <a href="{{ $url }}"
               style="display:inline-block; padding:12px 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:15px; font-weight:700; color:#101216; text-decoration:none; border-radius:8px;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
