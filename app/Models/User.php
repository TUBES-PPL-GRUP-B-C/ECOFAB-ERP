<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    /**
     * Cek apakah pengguna termasuk dalam kelompok Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Cek apakah pengguna termasuk dalam kelompok Operasional (hr, pembelian, gudang, produksi).
     */
    public function isOperasional(): bool
    {
        return $this->hasAnyRole(['hr', 'pembelian', 'gudang', 'produksi']);
    }

    /**
     * Cek apakah pengguna termasuk dalam kelompok Komersial (penjualan).
     */
    public function isKomersial(): bool
    {
        return $this->hasRole('penjualan');
    }

    /**
     * Dapatkan nama kelompok peran untuk tampilan antarmuka.
     */
    public function getKelompokPeranAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'Admin Pabrik';
        }

        if ($this->isOperasional()) {
            return 'Staf Operasional';
        }

        if ($this->isKomersial()) {
            return 'Staf Komersial';
        }

        return 'Pengguna Internal';
    }

    /**
     * Dapatkan nama label peran yang ramah dibaca pengguna.
     */
    public function getRoleLabelAttribute(): string
    {
        $role = $this->roles->first()?->name;

        return match ($role) {
            'admin' => 'Admin Sistem',
            'hr' => 'Staf HR & Organisasi',
            'pembelian' => 'Staf Pengadaan & Pembelian',
            'gudang' => 'Staf Gudang & Material',
            'produksi' => 'Staf Produksi Sirkular',
            'penjualan' => 'Staf Penjualan & Komersial',
            default => 'Staf Internal',
        };
    }
}
