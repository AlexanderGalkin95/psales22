<?php


namespace App\Services\AmoCRM\Notifications;


class AmoCRMReportable
{
    public string $domain;
    public string $error;

    public function __construct(string $domain, string $error = '')
    {
        $this->domain = $domain;
        $this->error = $error;
    }
}
