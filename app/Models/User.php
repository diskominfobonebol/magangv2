<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'instansi_bidang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }

    public function pendaftaranMagang()
    {
        return $this->hasMany(PendaftaranMagang::class, 'user_id');
    }

    // Accessor / Mutator agar kompatibel dengan kode yang menggunakan 'nama'
    public function getNamaAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    public function isAdminMaster(): bool
    {
        return $this->role_id === 1;
    }

    public function isKasubag(): bool
    {
        return $this->role_id === 2;
    }

    public function isPegawai(): bool
    {
        return $this->role_id === 3;
    }

    public function isBendahara(): bool
    {
        return $this->role_id === 4;
    }

    public function isMahasiswa(): bool
    {
        return $this->role_id === 5;
    }
}
