<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;

use App\Models\Responsibility;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{
    //
    public function fetch(Request $request)
    {

        // Filter
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = responsibility::query();

        // powerhuman.com/api/companies?id=1&limit=10
        // Single Data
        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Data responsibility berhasil diambil');
            }
            return ResponseFormatter::error('Data responsibility tidak ada', 404);
        }
        // $company = Company::with(['users']);
        // Get Multiple Data
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        // Filter by name 
        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Data list responsibilities berhasil diambil'
        );
    }
    public function create(CreateResponsibilityRequest $request)
    {

        try {
           

            // Create responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id
            ]);

            if (!$responsibility) {
                return ResponseFormatter::error([
                    'message' => 'Gagal membuat responsibility'
                ], '500');
            }

            return ResponseFormatter::success($responsibility, 'responsibility berhasil dibuat');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), '500');
        }
    }

    public function destroy($id)
    {
        try {
            // Get responsibility
            $responsibility = Responsibility::find($id);

            // TODO: Check if responsibility is owned by user

            // Check if responsibility exist
            if (!$responsibility) {
                return ResponseFormatter::error([
                    'message' => 'Data responsibility tidak ada'
                ], '404');
            }

            // Delete responsibility
            $responsibility->delete();

            return ResponseFormatter::success($responsibility, 'responsibility berhasil dihapus');
        } catch (Exception $e) {
            //throw $th;
            return ResponseFormatter::error([
                $e->getMessage()
            ], '500');
        }
    }
}
