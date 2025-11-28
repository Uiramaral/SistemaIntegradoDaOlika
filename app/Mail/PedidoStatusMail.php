<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pedido $pedido, public string $oldStatus, public string $newStatus) {}

    public function build()
    {
        return $this->subject('Seu pedido #'.$this->pedido->id.' — '.ucfirst($this->newStatus))
            ->view('emails.pedido.status');
    }
}
