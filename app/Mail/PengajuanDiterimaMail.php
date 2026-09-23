<?php

namespace App\Mail;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanDiterimaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Pengajuan $pengajuan) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Penerbitan Diterima - Penerbit Irfani',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pengajuan-diterima',
        );
    }
}
