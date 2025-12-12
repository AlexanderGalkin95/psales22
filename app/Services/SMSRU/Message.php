<?php


namespace App\Services\SMSRU;


class Message
{
    protected ?string $to = '';

    protected string $text;

    protected ?string $from = '';


    public function to(?string $to): Message
    {
        $this->to = $to;

        return $this;
    }

    public function from(?string $from): Message
    {
        $this->from = $from;

        return $this;
    }

    public function text(string $text): Message
    {
        $this->text = $text;

        return $this;
    }

    public function geTo(): ?string
    {
        return $this->to;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }
}
