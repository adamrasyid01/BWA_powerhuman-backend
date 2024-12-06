<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    //
    public function fetch(Request $request)
    {

        // Filter
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });

        // powerhuman.com/api/companies?id=1&limit=10
        // Single Data
        if ($id) {
            $company = $companyQuery->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'Data company berhasil diambil');
            }
            return ResponseFormatter::error('Data company tidak ada', 404);
        }
        // $company = Company::with(['users']);
        // Get Multiple Data
        $companies = $companyQuery;

        // Filter by name 
        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Data list company berhasil diambil'
        );
    }

    public function create(CreateCompanyRequest $request)
    {

        try {
            // Upload Logo
            if ($request->hasfile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // Create Company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);

            if (!$company) {
                return ResponseFormatter::error([
                    'message' => 'Gagal membuat company'
                ], '500');
            }

            // Attach company to user
            $user = User::find(Auth::user()->id);
            $user->companies()->attach($company->id);

            $company->load('users');

            return ResponseFormatter::success($company, 'Company berhasil dibuat');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), '500');
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            // Get Company
            $company = Company::find($id);

            // Check if company doesn't exist
            if (!$company) {
                return ResponseFormatter::error([
                    'message' => 'Data company tidak ada'
                ], '404');
            }

            // Update Logo
            if ($request->hasfile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }


            // Update Company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo,
            ]);
            return ResponseFormatter::success($company, 'Company berhasil diupdate');
        } catch (Exception $e) {
            //throw $th;
            return ResponseFormatter::error([
                $e->getMessage()
            ], '500');
        }
    }
}
