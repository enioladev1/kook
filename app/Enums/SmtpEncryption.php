<?php

namespace App\Enums;

enum SmtpEncryption: string
{
    case Tls = 'tls';
    case Ssl = 'ssl';
    case None = 'none';
}
