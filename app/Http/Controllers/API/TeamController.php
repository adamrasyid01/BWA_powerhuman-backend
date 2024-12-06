<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    //
    public function fetch(Request $request)
    {

        // Filter
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::query();

        // powerhuman.com/api/companies?id=1&limit=10
        // Single Data
        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Data team berhasil diambil');
            }
            return ResponseFormatter::error('Data company tidak ada', 404);
        }
        // $company = Company::with(['users']);
        // Get Multiple Data
        $teams = $teamQuery->where('company_id', $request->company_id);

        // Filter by name 
        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Data list teams berhasil diambil'
        );
    }
    public function create(CreateTeamRequest $request)
    {

        try {
            // Upload icon
            if ($request->hasfile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Create Team
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if (!$team) {
                return ResponseFormatter::error([
                    'message' => 'Gagal membuat Team'
                ], '500');
            }

            return ResponseFormatter::success($team, 'Team berhasil dibuat');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), '500');
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        // dd($request->all());
        try {
            // Get Company
            $team = Team::find($id);

            // Check if company doesn't exist
            if (!$team) {
                return ResponseFormatter::error([
                    'message' => 'Data team tidak ada'
                ], '404');
            }

            // Update Logo
            if ($request->hasfile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }


            // Update Team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id
            ]);
            return ResponseFormatter::success($team, 'Team berhasil diupdate');
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
            // Get Team
            $team = Team::find($id);

            // TODO: Check if team is owned by user

            // Check if team exist
            if (!$team) {
                return ResponseFormatter::error([
                    'message' => 'Data team tidak ada'
                ], '404');
            }

            // Delete Team
            $team->delete();

            return ResponseFormatter::success($team, 'Team berhasil dihapus');
        } catch (Exception $e) {
            //throw $th;
            return ResponseFormatter::error([
                $e->getMessage()
            ], '500');
        }
    }
}
