<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateroleRequest;
use App\Http\Requests\UpdateroleRequest;
use App\Models\Role;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    //
    public function fetch(Request $request)
    {

        // Filter
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $roleQuery = role::query();

        // powerhuman.com/api/companies?id=1&limit=10
        // Single Data
        if ($id) {
            $role = $roleQuery->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Data role berhasil diambil');
            }
            return ResponseFormatter::error('Data company tidak ada', 404);
        }
        // $company = Company::with(['users']);
        // Get Multiple Data
        $roles = $roleQuery->where('company_id', $request->company_id);

        // Filter by name 
        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Data list roles berhasil diambil'
        );
    }
    public function create(CreateroleRequest $request)
    {

        try {
           

            // Create role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            if (!$role) {
                return ResponseFormatter::error([
                    'message' => 'Gagal membuat role'
                ], '500');
            }

            return ResponseFormatter::success($role, 'role berhasil dibuat');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), '500');
        }
    }

    public function update(UpdateroleRequest $request, $id)
    {
        // dd($request->all());
        try {
            // Get Company
            $role = Role::find($id);

            // Check if company doesn't exist
            if (!$role) {
                return ResponseFormatter::error([
                    'message' => 'Data role tidak ada'
                ], '404');
            }

            


            // Update role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);
            return ResponseFormatter::success($role, 'role berhasil diupdate');
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
            // Get role
            $role = Role::find($id);

            // TODO: Check if role is owned by user

            // Check if role exist
            if (!$role) {
                return ResponseFormatter::error([
                    'message' => 'Data role tidak ada'
                ], '404');
            }

            // Delete role
            $role->delete();

            return ResponseFormatter::success($role, 'role berhasil dihapus');
        } catch (Exception $e) {
            //throw $th;
            return ResponseFormatter::error([
                $e->getMessage()
            ], '500');
        }
    }
}
