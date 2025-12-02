<?php

namespace App\Http\Controllers;

use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PoliController extends Controller
{
    public function index()
    {
        $polis = Poli::all();

        return response()->json([
            'message' => 'Poli list retrieved successfully',
            'data' => $polis
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_poli' => 'required|string|max:255',
            'kode_poli' => 'required|string|max:10|unique:polis,kode_poli',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $poli = Poli::create([
            'nama_poli' => $request->nama_poli,
            'kode_poli' => strtoupper($request->kode_poli),
            'slug' => Str::slug($request->nama_poli),
        ]);

        return response()->json([
            'message' => 'Poli created successfully',
            'data' => $poli
        ], 201);
    }

    public function show($id)
    {
        $poli = Poli::findOrFail($id);

        return response()->json([
            'message' => 'Poli retrieved successfully',
            'data' => $poli
        ]);
    }

    public function update(Request $request, $id)
    {
        $poli = Poli::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_poli' => 'required|string|max:255',
            'kode_poli' => 'required|string|max:10|unique:polis,kode_poli,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $poli->update([
            'nama_poli' => $request->nama_poli,
            'kode_poli' => strtoupper($request->kode_poli),
            'slug' => Str::slug($request->nama_poli),
        ]);

        return response()->json([
            'message' => 'Poli updated successfully',
            'data' => $poli
        ]);
    }

    public function destroy($id)
    {
        $poli = Poli::findOrFail($id);
        
        if ($poli->queues()->exists()) {
            return response()->json([
                'message' => 'Cannot delete poli with existing queues'
            ], 403);
        }

        $poli->delete();

        return response()->json([
            'message' => 'Poli deleted successfully',
            'data' => null
        ]);
    }
}
