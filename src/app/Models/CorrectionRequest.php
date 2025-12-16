<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'requested_work_date' => 'date',
        'status'              => 'integer',
        'decided_at'          => 'datetime',
        // timeカラムは無理にdatetime castしない（blade側で substr 等で整形）
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function breaks()
    {
        return $this->hasMany(CorrectionBreak::class);
    }
}
