<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'break_no',
        'requested_break_start',
        'requested_break_end',
    ];

    /**
     * 親の修正申請
     */
    public function correctionRequest()
    {
        return $this->belongsTo(\App\Models\CorrectionRequest::class, 'correction_request_id');
    }
}
