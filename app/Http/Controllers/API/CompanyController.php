<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ValidateCreateCompanyRequest;
use App\Http\Requests\ValidateUpdateCompanyRequest;
use App\Http\Requests\ValidateDeleteCompanyRequest;
use App\Http\Requests\ValidateLoadCompanyRequest;
use App\Repositories\CompanyRepository;

class CompanyController extends Controller
{
    public function list(Request $request, CompanyRepository $cpr): JsonResponse
    {
        return response()->json($cpr->list($request));
    }

    public function company(ValidateLoadCompanyRequest $request, CompanyRepository $cpr, string $companyId): JsonResponse
    {
        return response()->json($cpr->getCompany($companyId));
    }

    public function create(ValidateCreateCompanyRequest $request, CompanyRepository $cpr): JsonResponse
    {
        $company = null;
        DB::transaction(function () use ($request, $cpr, &$company) {
            $company = $cpr->createCompany($request->all());
        });

        return response()->json([
            'company' => $company,
            'message' => 'Компания была создана успешно',
            'status' => 'success'
        ]);
    }

    public function update(ValidateUpdateCompanyRequest $request, CompanyRepository $cpr, $companyId): JsonResponse
    {
        $company = null;
        DB::transaction(function () use ($request, $cpr, $companyId, &$company) {
            $company = $cpr->updateCompany($companyId, $request->all());
        });
        return response()->json([
            'company' => $company,
            'message' => 'Компания была обновлена успешно',
            'status' => 'success'
        ]);
    }

    public function deactivate(ValidateDeleteCompanyRequest $request, CompanyRepository $cpr, $companyId): JsonResponse
    {
        $company = $cpr->findById($companyId);
        $this->authorize('delete-company', $company);

        DB::transaction(function () use ($company) {
            $company->delete();
        });
        return response()->json([
            'message' => 'Компания была удалена успешно',
            'status' => 'success'
        ]);
    }
}

