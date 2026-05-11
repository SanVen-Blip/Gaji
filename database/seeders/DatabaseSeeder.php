<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Karyawan;
use App\Models\Gaji;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $karyawanData = [
            ['nik' => 'EMP001', 'nama' => 'Budi Santoso',    'jabatan' => 'Software Engineer',  'departemen' => 'IT',        'email' => 'budi@example.com',    'telepon' => '081234567890', 'tanggal_masuk' => '2020-01-15', 'status' => 'aktif'],
            ['nik' => 'EMP002', 'nama' => 'Siti Rahayu',     'jabatan' => 'HR Manager',          'departemen' => 'HR',        'email' => 'siti@example.com',    'telepon' => '081234567891', 'tanggal_masuk' => '2019-03-10', 'status' => 'aktif'],
            ['nik' => 'EMP003', 'nama' => 'Ahmad Fauzi',     'jabatan' => 'Finance Analyst',     'departemen' => 'Finance',   'email' => 'ahmad@example.com',   'telepon' => '081234567892', 'tanggal_masuk' => '2021-06-01', 'status' => 'aktif'],
            ['nik' => 'EMP004', 'nama' => 'Dewi Lestari',    'jabatan' => 'Marketing Manager',   'departemen' => 'Marketing', 'email' => 'dewi@example.com',    'telepon' => '081234567893', 'tanggal_masuk' => '2018-09-20', 'status' => 'aktif'],
            ['nik' => 'EMP005', 'nama' => 'Rizky Pratama',   'jabatan' => 'Backend Developer',   'departemen' => 'IT',        'email' => 'rizky@example.com',   'telepon' => '081234567894', 'tanggal_masuk' => '2022-02-14', 'status' => 'aktif'],
            ['nik' => 'EMP006', 'nama' => 'Nurul Hidayah',   'jabatan' => 'UI/UX Designer',      'departemen' => 'IT',        'email' => 'nurul@example.com',   'telepon' => '081234567895', 'tanggal_masuk' => '2021-11-05', 'status' => 'aktif'],
            ['nik' => 'EMP007', 'nama' => 'Hendra Wijaya',   'jabatan' => 'Sales Executive',     'departemen' => 'Marketing', 'email' => 'hendra@example.com',  'telepon' => '081234567896', 'tanggal_masuk' => '2020-07-22', 'status' => 'aktif'],
            ['nik' => 'EMP008', 'nama' => 'Rina Susanti',    'jabatan' => 'Accountant',          'departemen' => 'Finance',   'email' => 'rina@example.com',    'telepon' => '081234567897', 'tanggal_masuk' => '2019-12-01', 'status' => 'nonaktif'],
        ];

        foreach ($karyawanData as $data) {
            Karyawan::create($data);
        }

        // Seed gaji 3 bulan terakhir
        $karyawanList = Karyawan::all();
        $gajiPokok = [8000000, 7500000, 6500000, 9000000, 7000000, 6800000, 6000000, 5500000];

        for ($i = 2; $i >= 0; $i--) {
            $tgl   = now()->subMonths($i);
            $bulan = (int) $tgl->format('n');
            $tahun = (int) $tgl->format('Y');

            foreach ($karyawanList as $idx => $k) {
                $pokok = $gajiPokok[$idx] ?? 5000000;
                Gaji::create([
                    'karyawan_id'         => $k->id,
                    'bulan'               => $bulan,
                    'tahun'               => $tahun,
                    'gaji_pokok'          => $pokok,
                    'tunjangan_transport' => 500000,
                    'tunjangan_makan'     => 400000,
                    'tunjangan_lainnya'   => rand(0, 300000),
                    'bonus'               => ($i === 0) ? rand(0, 1000000) : 0,
                    'potongan_bpjs'       => $pokok * 0.01,
                    'potongan_pajak'      => $pokok * 0.05,
                    'potongan_lainnya'    => 0,
                    'gaji_bersih'         => $pokok + 500000 + 400000 - ($pokok * 0.01) - ($pokok * 0.05),
                    'status_bayar'        => ($i > 0) ? 'sudah_bayar' : (rand(0, 1) ? 'sudah_bayar' : 'belum_bayar'),
                    'tanggal_bayar'       => ($i > 0) ? $tgl->format('Y-m-') . '25' : null,
                ]);
            }
        }
    }
}
