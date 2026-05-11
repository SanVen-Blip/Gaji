<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'nik',
        'nama',
        'jabatan',
        'departemen',
        'email',
        'telepon',
        'tanggal_masuk',
        'status',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    public function gaji()
    {
        return $this->hasMany(Gaji::class, 'karyawan_id');
    }

    public function gajiTerakhir()
    {
        return $this->hasOne(Gaji::class, 'karyawan_id')->latestOfMany();
    }
}
