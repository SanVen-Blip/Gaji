<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    // Potongan per hari tidak masuk (alpha)
    const POTONGAN_ALPHA = 50000;

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'status',
        'jam_masuk',
        'jam_keluar',
        'potongan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal'  => 'date',
        'potongan' => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Hitung potongan berdasarkan status absensi.
     * Alpha = -50.000, Izin/Sakit/Hadir = 0
     */
    public static function hitungPotongan(string $status): float
    {
        return $status === 'alpha' ? self::POTONGAN_ALPHA : 0;
    }
}
