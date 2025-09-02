<?php

namespace App\Mail\Member;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $email;
    private $url;

    /**
     * Create a new message instance.
     */
    public function __construct(string $email, string $token)
    {
        $this->generateUrl($email, $token);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = config('mail.from');

        return new Envelope(
            from: new Address(
                $from['address'],
                $from['name'],
            ),
            subject: '【メンバー決済管理】本登録のURLが発行されました',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.member.prov_register',
            with: [
                'email' => $this->email,
                'url' => $this->url,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * 登録用URLを生成する
     *
     * @param string $email
     * @param string $token
     * @return void
     */
    private function generateUrl(string $email, string $token)
    {
        $this->email = $email;

        $baseUrl = config('app.frontend_url') . '/guest/register';
        $queryParams = http_build_query([
            'token' => $token,
            'email' => $email,
        ]);

        $this->url = "$baseUrl?$queryParams";
    }
}
