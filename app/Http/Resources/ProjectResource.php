<?php

namespace App\Http\Resources;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'rating_id' => $this->rating_id,
            'integration_id' => $this->integration_id,
            'integration_domain' => $this->integration_domain,

            'project_type' => $this->project_type,

            'pm_id' => $this->pm_id,
            'pm_name' => $this->pm?->name,

            'senior_id' => $this->senior_id,
            'senior_name' => $this->senior?->name,

            'name' => $this->name,
            'reference_name' => $this->getReferenceName(),
            'permissible_error' => $this->permissible_error,

            'assessors' => $this->assessors->map(function (User $user) {
                return ['id' => $user->id, 'label' => $user->name, 'value' => $user->id];
            }),

            'status' => $this->integration_status ? 'Да' : 'Нет',
            'total_time_limit' => $this->total_time_limit,

            'date_start' => $this->date_start,
            'created_at' => $this->created_at,
        ];
    }
}
