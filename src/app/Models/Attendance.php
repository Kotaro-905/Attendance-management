<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function getBreakDurationAttribute(): ?string
    {
        if (!$this->break_start_at || !$this->break_end_at) {
            return null;
        }

        $start = Carbon::createFromFormat('H:i:s', $this->break_start_at);
        $end   = Carbon::createFromFormat('H:i:s', $this->break_end_at);

        $minutes = $start->diffInMinutes($end);

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);  // 1:00 みたいな形
    }

    // 合計勤務時間
    public function getTotalDurationAttribute(): ?string
    {
        if (!$this->clock_in_at || !$this->clock_out_at) {
            return null;
        }

        $in  = Carbon::createFromFormat('H:i:s', $this->clock_in_at);
        $out = Carbon::createFromFormat('H:i:s', $this->clock_out_at);

        $workMinutes = $in->diffInMinutes($out); // 総拘束時間

        // 休憩があれば引く
        if ($this->break_start_at && $this->break_end_at) {
            $bStart = Carbon::createFromFormat('H:i:s', $this->break_start_at);
            $bEnd   = Carbon::createFromFormat('H:i:s', $this->break_end_at);
            $workMinutes -= $bStart->diffInMinutes($bEnd);
        }

        $h = intdiv($workMinutes, 60);
        $m = $workMinutes % 60;

        return sprintf('%d:%02d', $h, $m); // 8:00 みたいな形
    }
}
