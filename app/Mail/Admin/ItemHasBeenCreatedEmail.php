<?php

namespace App\Mail\Admin;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ItemHasBeenCreatedEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param string[] $receiver
     * @param Item $item
     */
    public function __construct(
        public array $receiver,
        public Item $item
    ) {
    }

    public function build(): self
    {
        return $this
            ->to($this->receiver['webhook'], $this->receiver['name'])
            ->subject('New item has been created on the roadmap')
            ->markdown('emails.admin.item-has-been-created');
    }
}
