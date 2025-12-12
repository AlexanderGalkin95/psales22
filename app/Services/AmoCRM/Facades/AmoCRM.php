<?php

namespace App\Services\AmoCRM\Facades;
use App\Services\AmoCRM\AmoCRMResponse;
use App\Services\AmoCRM\Exceptions\AmoCRMException;

/**
 * @throws AmoCRMException
 * @method static AmoCRMResponse requestToken(string $amo_crm_domain, array $params)
 * @method static AmoCRMResponse requestCalls(string $amo_crm_domain, array $params, array $headers)
 * @method static AmoCRMResponse requestPipelines(string $amo_crm_domain, array $params, array $headers)
 * @method static AmoCRMResponse requestSalesManagers(string $amo_crm_domain, array $params, array $headers)
 * @mixin \App\Services\AmoCRM\AmoCRM
 **/


class AmoCRM extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'amo';
    }
}
