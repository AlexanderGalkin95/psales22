<?php

namespace App\Repositories;

use App\Models\Integration;
use App\Models\Project;
use App\Models\RefIntegration;
use App\Notifications\AmoCRMConnectionErrorNotification;
use App\Repositories\Eloquent\BaseRepository;
use App\Services\AmoCRM\Helpers\NotificationReports;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use App\Traits\QueryListTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    use QueryListTrait;

    public function list($request)
    {
        $limit  = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);
        $mapping = [
            'name'  => 'companies.name',
            'status'    => 'companies.status',
            'niche'    => 'companies.niche',
            'projects_count'    => 'companies.projects_count',
            'relation:company_admin'  => 'name',
        ];

        $query = $this->model->with(['links', 'managers', 'projects'])
            ->when(
                $request->has('$filter'),
                $this->applyFilterClosure($request, $mapping)
            )
            ->when(
                $request->has('$orderBy'),
                $this->applyOrderByClosure($request, $mapping)
            )
            ->when(
                $request->has('search'),
                function ($q) use ($request) {
                    return $q->where(function ($q) use ($request) {
                        $q->where('companies.name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('companies.niche', 'ilike', '%' . $request->search . '%')
                            ->orWhereHas('company_admin', function ($qq) use ($request) {
                                $qq->where('name', 'ilike', '%' . $request->search . '%');
                            });
                    });
                }
            );

        $total = $this->getCount($query);
        $companies = $query->skip($offset)
            ->take($limit)
            ->get();

        return [
            'total' => $total,
            'companies' => $companies
        ];
    }

    public function getCompany($companyId)
    {
        $company = $this->findById(
            $companyId,
            null,
            [
                'links',
                'managers',
                'projects' => function ($query) {
                    $query->withCount('callSettingsSalesManagers as sales_managers_count');
                }
            ]
        );
        $company->managers->transform(function ($item) {
            return $item->user_id;
        });

        return $company;
    }

    public function createCompany($payload)
    {
        $company = $this->create($payload);
        $this->saveLinks($company, $payload['links']);
        $this->saveManagers($company, $payload['managers']);

        return $company->fresh(['links', 'managers']);
    }

    public function updateCompany($companyId, $payload)
    {
        $company = $this->updateById($companyId, $payload);
        $this->saveLinks($company, $payload['links']);
        $this->saveManagers($company, $payload['managers']);

        return $company->fresh(['links', 'managers']);
    }

    public function deleteById(int $id): bool
    {
        return false;
    }

    public function saveLinks(Company $company, array $links)
    {
        $this->clearLinks($company);

        return $company->links()->saveMany(
            $company->links()->makeMany($links)
        );
    }

    public function saveManagers(Company $company, array $managers)
    {
        $this->clearManagers($company);

        return $company->managers()->saveMany(
            $company->managers()->makeMany($managers)
        );
    }

    public function clearLinks(Company $company)
    {
        return $company->links()->delete();
    }

    public function clearManagers(Company $company)
    {
        return $company->managers()->delete();
    }

    public function model(): string
    {
        return Company::class;
    }
}