<?php

namespace App\Enums;

enum EmailProvider: string
{
    case Resend = 'resend';
    case Postmark = 'postmark';
    case SendByte = 'sendbyte';
    case Smtp = 'smtp';
}
