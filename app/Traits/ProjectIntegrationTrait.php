<?php

namespace App\Traits;


use App\Models\Integration;
use App\Models\RefIntegration;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ProjectIntegrationTrait
{
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id');
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(RefIntegration::class, 'project_type');
    }

    public function hasIntegration(): bool
    {
        return boolval($this->integration);
    }

    public function attachIntegration($domain)
    {
        $reference = $this->reference;

        $integrable = resolve($reference->type)
            ->where('domain', '=', $domain)
            ->first();

        $this->update([
            'integration_id' => $integrable ? $integrable->integration->id : null,
            'integration_status' => $integrable
                && $integrable->token && !$integrable->token->hasExpired(),
        ]);
    }

    public function detachIntegration()
    {
        $this->update(['integration_id' => null, 'integration_status' => false]);
    }
}
