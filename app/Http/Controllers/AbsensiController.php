<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Gaji;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    /**
     * Ambil data absensi dengan filter bulan/tahun/karyawan
     */
    public function index(Request $request): JsonResponse
    {
        $query = Absensi::with('karyawan');

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
        } elseif ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensi = $query->orderBy('tanggal', 'desc')->get();

        $result = $absensi->map(fn($a) => [
            'id'             => $a->id,
            'karyawan_id'    => $a->karyawan_id,
            'nama_karyawan'  => $a->karyawan->nama ?? '-',
            'jabatan'        => $a->karyawan->jabatan ?? '-',
            'departemen'     => $a->karyawan->departemen ?? '-',
            'tanggal'        => $a->tanggal->format('Y-m-d'),
            'tanggal_label'  => $a->tanggal->translatedFormat('d F Y'),
            'hari'           => $a->tanggal->translatedFormat('l'),
            'status'         => $a->status,
            'jam_masuk'      => $a->jam_masuk,
            'jam_keluar'     => $a->jam_keluar,
            'potongan'       => (float) $a->potongan,
            'keterangan'     => $a->keterangan,
        ]);

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Simpan absensi baru + otomatis update potongan di tabel gaji
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'karyawan_id' => 'required|exists:karyawan,id',
                'tanggal'     => 'required|date',
                'status'      => 'required|in:hadir,alpha,izin,sakit',
                'jam_masuk'   => 'nullable|date_format:H:i',
                'jam_keluar'  => 'nullable|date_format:H:i',
                'keterangan'  => 'nullable|string|max:255',
            ]);

            // Cek duplikat
            $exists = Absensi::where('karyawan_id', $validated['karyawan_id'])
                ->where('tanggal', $validated['tanggal'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data absensi karyawan ini pada tanggal tersebut sudah ada.',
                ], 422);
            }

            $validated['potongan'] = Absensi::hitungPotongan($validated['status']);

            DB::transaction(function () use ($validated) {
                Absensi::create($validated);
                $this->syncPotonganGaji($validated['karyawan_id'], $validated['tanggal']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan.' . ($validated['potongan'] > 0 ? ' Potongan Rp ' . number_format($validated['potongan'], 0, ',', '.') . ' diterapkan ke gaji.' : ''),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Detail satu absensi
     */
    public function show(int $id): JsonResponse
    {
        $absensi = Absensi::with('karyawan')->find($id);
        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => $absensi]);
    }

    /**
     * Update absensi + recalculate potongan gaji
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $absensi = Absensi::find($id);
        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        try {
            $validated = $request->validate([
                'karyawan_id' => 'required|exists:karyawan,id',
                'tanggal'     => 'required|date',
                'status'      => 'required|in:hadir,alpha,izin,sakit',
                'jam_masuk'   => 'nullable|date_format:H:i',
                'jam_keluar'  => 'nullable|date_format:H:i',
                'keterangan'  => 'nullable|string|max:255',
            ]);

            // Cek duplikat (kecuali diri sendiri)
            $exists = Absensi::where('karyawan_id', $validated['karyawan_id'])
                ->where('tanggal', $validated['tanggal'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data absensi karyawan ini pada tanggal tersebut sudah ada.',
                ], 422);
            }

            $validated['potongan'] = Absensi::hitungPotongan($validated['status']);

            DB::transaction(function () use ($absensi, $validated) {
                $absensi->update($validated);
                $this->syncPotonganGaji($validated['karyawan_id'], $validated['tanggal']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil diperbarui.' . ($validated['potongan'] > 0 ? ' Potongan Rp ' . number_format($validated['potongan'], 0, ',', '.') . ' diterapkan ke gaji.' : ''),
            ]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Hapus absensi + recalculate potongan gaji
     */
    public function destroy(int $id): JsonResponse
    {
        $absensi = Absensi::find($id);
        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $karyawanId = $absensi->karyawan_id;
        $tanggal    = $absensi->tanggal->format('Y-m-d');

        DB::transaction(function () use ($absensi, $karyawanId, $tanggal) {
            $absensi->delete();
            $this->syncPotonganGaji($karyawanId, $tanggal);
        });

        return response()->json(['success' => true, 'message' => 'Absensi berhasil dihapus.']);
    }

    /**
     * Rekap absensi per karyawan dalam satu bulan
     */
    public function rekap(Request $request): JsonResponse
    {
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));

        $karyawanList = Karyawan::where('status', 'aktif')->orderBy('nama')->get();

        $result = $karyawanList->map(function ($k) use ($bulan, $tahun) {
            $absensi = Absensi::where('karyawan_id', $k->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            $hadir  = $absensi->where('status', 'hadir')->count();
            $alpha  = $absensi->where('status', 'alpha')->count();
            $izin   = $absensi->where('status', 'izin')->count();
            $sakit  = $absensi->where('status', 'sakit')->count();
            $totalPotongan = $absensi->sum('potongan');

            return [
                'karyawan_id'   => $k->id,
                'nama'          => $k->nama,
                'jabatan'       => $k->jabatan,
                'departemen'    => $k->departemen,
                'hadir'         => $hadir,
                'alpha'         => $alpha,
                'izin'          => $izin,
                'sakit'         => $sakit,
                'total_hadir'   => $hadir + $izin + $sakit,
                'total_potongan'=> (float) $totalPotongan,
            ];
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Sinkronisasi potongan absensi ke tabel gaji secara otomatis.
     * Hitung total potongan alpha bulan tersebut, update kolom potongan_lainnya
     * dan recalculate gaji_bersih.
     */
    private function syncPotonganGaji(int $karyawanId, string $tanggal): void
    {
        $tgl   = \Carbon\Carbon::parse($tanggal);
        $bulan = (int) $tgl->format('n');
        $tahun = (int) $tgl->format('Y');

        // Total potongan alpha bulan ini untuk karyawan ini
        $totalPotonganAbsensi = Absensi::where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('potongan');

        // Cari data gaji bulan ini
        $gaji = Gaji::where('karyawan_id', $karyawanId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        if (!$gaji) return; // Belum ada data gaji, skip

        // Update potongan_lainnya dengan total potongan absensi
        $gaji->potongan_lainnya = $totalPotonganAbsensi;

        // Recalculate gaji bersih
        $totalTunjangan = $gaji->tunjangan_transport + $gaji->tunjangan_makan
            + $gaji->tunjangan_lainnya + $gaji->bonus;
        $totalPotongan  = $gaji->potongan_bpjs + $gaji->potongan_pajak + $gaji->potongan_lainnya;
        $gaji->gaji_bersih = $gaji->gaji_pokok + $totalTunjangan - $totalPotongan;

        $gaji->save();
    }
}
