<?php


namespace App\Services\Bitrix\Notifications;


class BitrixReportable
{
    public string $domain;
    public string $error;

    public function __construct(string $domain, string $error = '')
    {
        $this->domain = $domain;
        $this->error = $error;
    }
}
