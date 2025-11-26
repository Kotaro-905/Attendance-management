<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'break_start_at',
        'break_end_at',
        'clock_out_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'work_date'      => 'date',
        'clock_in_at'    => 'datetime:H:i',
        'break_start_at' => 'datetime:H:i',
        'break_end_at'   => 'datetime:H:i',
        'clock_out_at'   => 'datetime:H:i',
        'status'         => 'integer',
    ];

    // 打刻したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // この勤怠に対する修正申請
    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }
}
