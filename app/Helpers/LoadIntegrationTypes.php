<?php

namespace App\Helpers;

use App\Models\RefIntegration;
use Illuminate\Support\Collection;

class LoadIntegrationTypes
{
    private static $instance;

    public Collection $types;

    public function __construct()
    {
        $this->types = RefIntegration::all();
        static::$instance = $this;
    }

    public function __clone()
    {
        //
    }

    public function getTypes(): Collection
    {
        return $this->types;
    }

    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}