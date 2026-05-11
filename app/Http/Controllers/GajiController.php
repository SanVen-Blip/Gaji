<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class GajiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Gaji::with('karyawan');

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }
        if ($request->filled('status_bayar')) {
            $query->where('status_bayar', $request->status_bayar);
        }

        $gaji = $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        $result = $gaji->map(function ($item) {
            return [
                'id'                  => $item->id,
                'karyawan_id'         => $item->karyawan_id,
                'nama_karyawan'       => $item->karyawan->nama ?? '-',
                'jabatan'             => $item->karyawan->jabatan ?? '-',
                'departemen'          => $item->karyawan->departemen ?? '-',
                'bulan'               => $item->bulan,
                'nama_bulan'          => Gaji::getNamaBulan($item->bulan),
                'tahun'               => $item->tahun,
                'gaji_pokok'          => (float) $item->gaji_pokok,
                'tunjangan_transport' => (float) $item->tunjangan_transport,
                'tunjangan_makan'     => (float) $item->tunjangan_makan,
                'tunjangan_lainnya'   => (float) $item->tunjangan_lainnya,
                'bonus'               => (float) $item->bonus,
                'potongan_bpjs'       => (float) $item->potongan_bpjs,
                'potongan_pajak'      => (float) $item->potongan_pajak,
                'potongan_lainnya'    => (float) $item->potongan_lainnya,
                'gaji_bersih'         => (float) $item->gaji_bersih,
                'status_bayar'        => $item->status_bayar,
                'tanggal_bayar'       => $item->tanggal_bayar?->format('Y-m-d'),
                'keterangan'          => $item->keterangan,
            ];
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'karyawan_id'         => 'required|exists:karyawan,id',
                'bulan'               => 'required|integer|min:1|max:12',
                'tahun'               => 'required|integer|min:2000|max:2100',
                'gaji_pokok'          => 'required|numeric|min:0',
                'tunjangan_transport' => 'nullable|numeric|min:0',
                'tunjangan_makan'     => 'nullable|numeric|min:0',
                'tunjangan_lainnya'   => 'nullable|numeric|min:0',
                'bonus'               => 'nullable|numeric|min:0',
                'potongan_bpjs'       => 'nullable|numeric|min:0',
                'potongan_pajak'      => 'nullable|numeric|min:0',
                'potongan_lainnya'    => 'nullable|numeric|min:0',
                'status_bayar'        => 'required|in:belum_bayar,sudah_bayar',
                'tanggal_bayar'       => 'nullable|date',
                'keterangan'          => 'nullable|string|max:500',
            ]);

            // Cek duplikat
            $exists = Gaji::where('karyawan_id', $validated['karyawan_id'])
                ->where('bulan', $validated['bulan'])
                ->where('tahun', $validated['tahun'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji untuk karyawan ini pada bulan dan tahun tersebut sudah ada.',
                ], 422);
            }

            // Hitung gaji bersih
            $totalTunjangan = ($validated['tunjangan_transport'] ?? 0)
                + ($validated['tunjangan_makan'] ?? 0)
                + ($validated['tunjangan_lainnya'] ?? 0)
                + ($validated['bonus'] ?? 0);
            $totalPotongan = ($validated['potongan_bpjs'] ?? 0)
                + ($validated['potongan_pajak'] ?? 0)
                + ($validated['potongan_lainnya'] ?? 0);
            $validated['gaji_bersih'] = $validated['gaji_pokok'] + $totalTunjangan - $totalPotongan;

            $gaji = Gaji::create($validated);
            $gaji->load('karyawan');

            return response()->json(['success' => true, 'message' => 'Data gaji berhasil ditambahkan.', 'data' => $gaji], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $gaji = Gaji::with('karyawan')->find($id);
        if (!$gaji) {
            return response()->json(['success' => false, 'message' => 'Data gaji tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => $gaji]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $gaji = Gaji::find($id);
        if (!$gaji) {
            return response()->json(['success' => false, 'message' => 'Data gaji tidak ditemukan.'], 404);
        }

        try {
            $validated = $request->validate([
                'karyawan_id'         => 'required|exists:karyawan,id',
                'bulan'               => 'required|integer|min:1|max:12',
                'tahun'               => 'required|integer|min:2000|max:2100',
                'gaji_pokok'          => 'required|numeric|min:0',
                'tunjangan_transport' => 'nullable|numeric|min:0',
                'tunjangan_makan'     => 'nullable|numeric|min:0',
                'tunjangan_lainnya'   => 'nullable|numeric|min:0',
                'bonus'               => 'nullable|numeric|min:0',
                'potongan_bpjs'       => 'nullable|numeric|min:0',
                'potongan_pajak'      => 'nullable|numeric|min:0',
                'potongan_lainnya'    => 'nullable|numeric|min:0',
                'status_bayar'        => 'required|in:belum_bayar,sudah_bayar',
                'tanggal_bayar'       => 'nullable|date',
                'keterangan'          => 'nullable|string|max:500',
            ]);

            // Cek duplikat (kecuali record ini sendiri)
            $exists = Gaji::where('karyawan_id', $validated['karyawan_id'])
                ->where('bulan', $validated['bulan'])
                ->where('tahun', $validated['tahun'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji untuk karyawan ini pada bulan dan tahun tersebut sudah ada.',
                ], 422);
            }

            // Hitung ulang gaji bersih
            $totalTunjangan = ($validated['tunjangan_transport'] ?? 0)
                + ($validated['tunjangan_makan'] ?? 0)
                + ($validated['tunjangan_lainnya'] ?? 0)
                + ($validated['bonus'] ?? 0);
            $totalPotongan = ($validated['potongan_bpjs'] ?? 0)
                + ($validated['potongan_pajak'] ?? 0)
                + ($validated['potongan_lainnya'] ?? 0);
            $validated['gaji_bersih'] = $validated['gaji_pokok'] + $totalTunjangan - $totalPotongan;

            $gaji->update($validated);
            return response()->json(['success' => true, 'message' => 'Data gaji berhasil diperbarui.', 'data' => $gaji]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $gaji = Gaji::find($id);
        if (!$gaji) {
            return response()->json(['success' => false, 'message' => 'Data gaji tidak ditemukan.'], 404);
        }
        $gaji->delete();
        return response()->json(['success' => true, 'message' => 'Data gaji berhasil dihapus.']);
    }

    public function dashboard(): JsonResponse
    {
        $tahunIni = date('Y');
        $bulanIni = (int) date('n');

        $totalKaryawan = Karyawan::where('status', 'aktif')->count();

        $totalGajiBulanIni = Gaji::where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->sum('gaji_bersih');

        $sudahBayar = Gaji::where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->where('status_bayar', 'sudah_bayar')
            ->count();

        $belumBayar = Gaji::where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->where('status_bayar', 'belum_bayar')
            ->count();

        // Statistik gaji per bulan (12 bulan terakhir)
        $statistikBulanan = [];
        for ($i = 11; $i >= 0; $i--) {
            $tgl   = now()->subMonths($i);
            $bln   = (int) $tgl->format('n');
            $thn   = (int) $tgl->format('Y');
            $total = Gaji::where('bulan', $bln)->where('tahun', $thn)->sum('gaji_bersih');
            $statistikBulanan[] = [
                'label' => Gaji::getNamaBulan($bln) . ' ' . $thn,
                'total' => (float) $total,
            ];
        }

        // Top 5 gaji tertinggi bulan ini
        $topGaji = Gaji::with('karyawan')
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->orderBy('gaji_bersih', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($g) => [
                'nama'       => $g->karyawan->nama ?? '-',
                'jabatan'    => $g->karyawan->jabatan ?? '-',
                'gaji_bersih' => (float) $g->gaji_bersih,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'total_karyawan'       => $totalKaryawan,
                'total_gaji_bulan_ini' => (float) $totalGajiBulanIni,
                'sudah_bayar'          => $sudahBayar,
                'belum_bayar'          => $belumBayar,
                'statistik_bulanan'    => $statistikBulanan,
                'top_gaji'             => $topGaji,
                'bulan_ini'            => Gaji::getNamaBulan($bulanIni) . ' ' . $tahunIni,
            ],
        ]);
    }
}
