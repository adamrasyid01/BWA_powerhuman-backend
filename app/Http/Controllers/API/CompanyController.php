<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    //
    public function all(Request $request){

        // Filter
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
      
        // powerhuman.com/api/companies?id=1&limit=10
        if($id){
            $company = Company::with(['users'])->find($id);

            if($company){
                return ResponseFormatter::success($company, 'Data company berhasil diambil');
            }
            return ResponseFormatter::error('Data company tidak ada',404);
        }
        $company= Company::with(['users']);

        // Filter by name 
        if($name){
            $company->where('name','like','%'.$name.'%');
        }
        return ResponseFormatter::success(
            $company->paginate($limit),
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
            $user = User::find(Auth ::user()->id);
            $user->companies()->attach($company->id);

            $company->load('users');

            return ResponseFormatter::success($company, 'Company berhasil dibuat');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), '500');
        }
    }
}
