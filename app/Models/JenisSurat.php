<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $fillable = ['kode', 'nama_jenis'];

    public function surats()
    {
        return $this->hasMany(Surat::class);
    }
}
