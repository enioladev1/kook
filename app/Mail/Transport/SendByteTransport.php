<?php

namespace App\Mail\Transport;

use SendByte\SendByte;
use SendByte\SendByteException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;

/**
 * Wraps the official sendbyte/sendbyte-php SDK as a Symfony Mailer
 * transport, since SendByte has no Symfony Mailer bridge of its own.
 */
class SendByteTransport extends AbstractTransport
{
    public function __construct(private readonly SendByte $client)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();

        if (! $original instanceof Message) {
            throw new TransportException('SendByte: unsupported message type.');
        }

        $email = MessageConverter::toEmail($original);

        $from = $email->getFrom();
        $params = [
            'from' => $this->stringifyAddresses([$from[0]])[0],
            'to' => $this->stringifyAddresses($email->getTo()),
            'subject' => (string) $email->getSubject(),
            'html' => $email->getHtmlBody() ?? $email->getTextBody(),
        ];

        if ($email->getCc() !== []) {
            $params['cc'] = $this->stringifyAddresses($email->getCc());
        }

        if ($email->getBcc() !== []) {
            $params['bcc'] = $this->stringifyAddresses($email->getBcc());
        }

        if ($email->getReplyTo() !== []) {
            $params['reply_to'] = $this->stringifyAddresses($email->getReplyTo());
        }

        try {
            $this->client->sendEmail($params);
        } catch (SendByteException $e) {
            throw new TransportException("SendByte: {$e->getMessage()}", previous: $e);
        }
    }

    public function __toString(): string
    {
        return 'sendbyte';
    }
}
