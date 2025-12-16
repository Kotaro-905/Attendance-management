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

    protected $casts = [
        'break_no'              => 'integer',
        'requested_break_start' => 'string',
        'requested_break_end'   => 'string',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class, 'correction_request_id');
    }
}
