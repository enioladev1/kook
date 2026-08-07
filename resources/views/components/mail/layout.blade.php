@props(['heading', 'preheader' => null])
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $heading }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body, table, td { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #F4F5F7; }
        a { color: #FF7A33; }

        @media screen and (max-width: 600px) {
            .kook-container { width: 100% !important; }
            .kook-px { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#F4F5F7;">
    @if ($preheader)
        <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#F4F5F7;">
            {{ $preheader }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        </div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F5F7;">
        <tr>
            <td align="center" style="padding: 40px 16px;">

                <table role="presentation" class="kook-container" width="560" cellpadding="0" cellspacing="0" style="width:560px; max-width:560px;">

                    {{-- Header band --}}
                    <tr>
                        <td align="center" bgcolor="#101216" style="background-color:#101216; background-image:linear-gradient(135deg, #1C2129, #101216); border-radius: 12px 12px 0 0; padding: 22px 24px;">
                            <img
                                src="{{ asset('branding/logo.png') }}"
                                width="83"
                                height="28"
                                alt="Kook"
                                style="display:block; width:83px; height:28px; border:0;"
                            >
                        </td>
                    </tr>

                    {{-- Card body --}}
                    <tr>
                        <td class="kook-px" bgcolor="#FFFFFF" style="background-color:#FFFFFF; padding: 36px 40px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; color:#0F1115; font-size:15px; line-height:1.6;">
                            <h1 style="margin:0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:20px; line-height:1.3; font-weight:700; color:#0F1115;">
                                {{ $heading }}
                            </h1>

                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Card bottom rounding --}}
                    <tr>
                        <td bgcolor="#FFFFFF" style="background-color:#FFFFFF; border-radius: 0 0 12px 12px; line-height:1px; font-size:1px;">&nbsp;</td>
                    </tr>

                    {{-- Signature hairline --}}
                    <tr>
                        <td style="padding: 28px 0 16px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="40" bgcolor="#FF7A33" style="background-color:#FF7A33; height:2px; line-height:2px; font-size:1px;">&nbsp;</td>
                                    <td style="line-height:1px; font-size:1px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td class="kook-px" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                            Sent by Kook &middot; self-hosted webhook infrastructure
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
