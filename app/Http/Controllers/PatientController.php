<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'message' => 'Patients retrieved successfully',
            'data' => $patients
        ]);
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);

        return response()->json([
            'message' => 'Patient retrieved successfully',
            'data' => $patient
        ]);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $patient->update($request->only(['nama', 'no_hp', 'alamat']));

        return response()->json([
            'message' => 'Patient updated successfully',
            'data' => $patient
        ]);
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        
        if ($patient->registrations()->exists()) {
            return response()->json([
                'message' => 'Cannot delete patient with existing registrations'
            ], 403);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient deleted successfully',
            'data' => null
        ]);
    }
}
