<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;

trait FormRequestTrait
{
    protected ?string  $message = null;

    public function validationData(): array
    {
        if (method_exists($this->route(), 'parameters')) {
            $this->request->add($this->route()->parameters());
            $this->query->add($this->route()->parameters());

            return array_merge($this->route()->parameters(), $this->all());
        }

        return $this->all();
    }

    /**
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException($this->message);
    }
}