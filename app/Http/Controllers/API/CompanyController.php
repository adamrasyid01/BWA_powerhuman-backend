<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

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
}
