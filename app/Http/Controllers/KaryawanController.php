<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class KaryawanController extends Controller
{
    public function index(): JsonResponse
    {
        $karyawan = Karyawan::orderBy('nama')->get();
        return response()->json(['success' => true, 'data' => $karyawan]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nik'           => 'required|string|max:20|unique:karyawan,nik',
                'nama'          => 'required|string|max:100',
                'jabatan'       => 'required|string|max:100',
                'departemen'    => 'required|string|max:100',
                'email'         => 'required|email|unique:karyawan,email',
                'telepon'       => 'nullable|string|max:20',
                'tanggal_masuk' => 'required|date',
                'status'        => 'required|in:aktif,nonaktif',
            ]);

            $karyawan = Karyawan::create($validated);
            return response()->json(['success' => true, 'message' => 'Karyawan berhasil ditambahkan.', 'data' => $karyawan], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => $karyawan]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        try {
            $validated = $request->validate([
                'nik'           => 'required|string|max:20|unique:karyawan,nik,' . $id,
                'nama'          => 'required|string|max:100',
                'jabatan'       => 'required|string|max:100',
                'departemen'    => 'required|string|max:100',
                'email'         => 'required|email|unique:karyawan,email,' . $id,
                'telepon'       => 'nullable|string|max:20',
                'tanggal_masuk' => 'required|date',
                'status'        => 'required|in:aktif,nonaktif',
            ]);

            $karyawan->update($validated);
            return response()->json(['success' => true, 'message' => 'Data karyawan berhasil diperbarui.', 'data' => $karyawan]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }
        $karyawan->delete();
        return response()->json(['success' => true, 'message' => 'Karyawan berhasil dihapus.']);
    }
}
