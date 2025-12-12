<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\User;
use App\Models\CorrectionBreak;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'attendance_id',
    'user_id',
    'admin_id',
    'requested_work_date',
    'requested_clock_in_time',
    'requested_clock_out_time',
    'reason',
    'status',
    'decided_at',
];

    protected $casts = [
        'requested_work_date'   => 'date',
        'requested_clock_in_time'    => 'datetime:H:i',
        'requested_clock_out_time'   => 'datetime:H:i',
        'status'                => 'integer',
        'decided_at'            => 'datetime',
    ];

    // 対象の勤怠
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 申請した一般ユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 対応した管理者
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

     /** 休憩（子テーブル） */
    public function breaks()
    {
        return $this->hasMany(CorrectionBreak::class);
    }


}
