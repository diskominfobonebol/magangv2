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
        'nama',
        'email',
        'nip',
        'password',
        'role',
        'role_id',
        'jabatan',
        'golongan',
        'urutan',
        'is_active',
        'instansi_bidang',
        'must_change_password',
        'password_changed_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'totp_last_used_step',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'must_change_password' => true,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'is_active' => 'boolean',
            'urutan' => 'integer',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'totp_last_used_step' => 'integer',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at) && !empty($this->two_factor_secret);
    }

    public function resetTwoFactor(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'totp_last_used_step' => null,
        ])->save();
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

    public function arsipDokumens()
    {
        return $this->hasMany(ArsipDokumen::class, 'user_id');
    }

    // Accessor / Mutator agar kompatibel dengan kode yang menggunakan 'nama'
    public function getNamaAttribute()
    {
        return $this->attributes['nama'] ?? ($this->attributes['name'] ?? null);
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama'] = $value;
        $this->attributes['name'] = $value;
    }

    public function getRoleAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return match ($this->role_id) {
            1 => 'admin',
            2 => 'kasubag',
            3 => 'pegawai',
            4 => 'bendahara_barang',
            5 => 'mahasiswa',
            default => 'pegawai',
        };
    }

    public function getNipAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->pegawai?->nip ?? null;
    }

    public function getJabatanAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->pegawai?->jabatan ?? null;
    }

    public function getGolonganAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->pegawai?->pangkat_golongan ?? null;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::getRoleLabel($this->role);
    }

    public static function getRoleLabel(?string $role): string
    {
        return match ($role) {
            'admin', 1, '1' => 'Admin Master',
            'bendahara_barang', 4, '4' => 'Bendahara Barang',
            'admin_kasubag', 'kasubag', 2, '2' => 'Admin Kasubag',
            'pegawai', 3, '3' => 'Pegawai',
            'mahasiswa', 5, '5' => 'Mahasiswa Magang',
            default => 'Pengguna',
        };
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
