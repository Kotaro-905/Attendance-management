<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_GENERAL = 0;
    const ROLE_ADMIN   = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role'              => 'integer',
    ];

    // --- リレーション ---

    // 自分の勤怠
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // 自分が出した修正申請
    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    // 管理者として対応した修正申請
    public function handledCorrectionRequests()
    {
        return $this->hasMany(CorrectionRequest::class, 'admin_id');
    }

    // 役割判定
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGeneral(): bool
    {
        return $this->role === self::ROLE_GENERAL;
    }
}
