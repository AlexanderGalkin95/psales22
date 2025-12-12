<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ReportException extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    private $message;

    private $date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($message, $date)
    {
        $this->connection = 'notification';
        $this->message = $message;
        $this->date = $date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Уведомление об ошибке')
            ->markdown('mail.report_exception', [
                'message' => $this->message,
                'date' => $this->date,
            ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // Send user notification of failure, etc...
    }
}
