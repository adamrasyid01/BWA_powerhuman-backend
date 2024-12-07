<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    //
    public function fetch(Request $request)
    {

        // Filter
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit', 10);

        $employeeQuery = Employee::query();

        // powerhuman.com/api/companies?id=1&limit=10
        // Single Data
        if ($id) {
            $employee = $employeeQuery->with(['team', 'role'])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, 'Data employee berhasil diambil');
            }
            return ResponseFormatter::error('Data employee tidak ada', 404);
        }
        // $company = Company::with(['users']);
         
        // Get Multiple Data
        $employees = $employeeQuery;

        // Filter by name 
        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }
        if($email){
            $employees->where('email', $email);
        }
        if($age){
            $employees->where('age', $age);
        }
        if($phone){
            $employees->where('phone', 'like', '%' . $phone . '%');
        }
        if($team_id){
            $employees->where('team_id', $team_id);
        }
        if($role_id){
            $employees->where('role_id', $role_id);
        }
        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Data list employees berhasil diambil'
        );
    }
    public function create(CreateEmployeeRequest $request)
    {

        try {
            // Upload icon
            if ($request->hasfile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // Create Employee
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id
            ]);

            if (!$employee) {
                return ResponseFormatter::error([
                    'message' => 'Gagal membuat Employee'
                ], '500');
            }

            return ResponseFormatter::success($employee, 'Employee berhasil dibuat');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), '500');
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        // dd($request->all());
        try {
            // Get Company
            $employee = Employee::find($id);

            // Check if company doesn't exist
            if (!$employee) {
                return ResponseFormatter::error([
                    'message' => 'Data employee tidak ada'
                ], '404');
            }

            // Update Logo
            if ($request->hasfile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }


            // Update Employee
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => isset($path) ? $path : $employee->photo,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);
            return ResponseFormatter::success($employee, 'Employee berhasil diupdate');
        } catch (Exception $e) {
            //throw $th;
            return ResponseFormatter::error([
                $e->getMessage()
            ], '500');
        }
    }

    public function destroy($id)
    {
        try {
            // Get Employee
            $employee = Employee::find($id);

            // TODO: Check if employee is owned by user

            // Check if employee exist
            if (!$employee) {
                return ResponseFormatter::error([
                    'message' => 'Data employee tidak ada'
                ], '404');
            }

            // Delete Employee
            $employee->delete();

            return ResponseFormatter::success($employee, 'Employee berhasil dihapus');
        } catch (Exception $e) {
            //throw $th;
            return ResponseFormatter::error([
                $e->getMessage()
            ], '500');
        }
    }
}
