<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gaji extends Model
{
    use HasFactory;

    protected $table = 'gaji';

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'tunjangan_transport',
        'tunjangan_makan',
        'tunjangan_lainnya',
        'bonus',
        'potongan_bpjs',
        'potongan_pajak',
        'potongan_lainnya',
        'gaji_bersih',
        'status_bayar',
        'tanggal_bayar',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar'       => 'date',
        'gaji_pokok'          => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_makan'     => 'decimal:2',
        'tunjangan_lainnya'   => 'decimal:2',
        'bonus'               => 'decimal:2',
        'potongan_bpjs'       => 'decimal:2',
        'potongan_pajak'      => 'decimal:2',
        'potongan_lainnya'    => 'decimal:2',
        'gaji_bersih'         => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public static function getNamaBulan(int $bulan): string
    {
        $bulanArr = [
            1  => 'Januari',   2  => 'Februari', 3  => 'Maret',
            4  => 'April',     5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',      8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',   11 => 'November',  12 => 'Desember',
        ];
        return $bulanArr[$bulan] ?? '-';
    }

    public function hitungGajiBersih(): float
    {
        $totalTunjangan = $this->tunjangan_transport + $this->tunjangan_makan
            + $this->tunjangan_lainnya + $this->bonus;
        $totalPotongan  = $this->potongan_bpjs + $this->potongan_pajak + $this->potongan_lainnya;
        return (float) ($this->gaji_pokok + $totalTunjangan - $totalPotongan);
    }
}
