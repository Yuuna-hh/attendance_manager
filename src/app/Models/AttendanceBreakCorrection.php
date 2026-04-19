<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreakCorrection extends Model
{
    protected $table = 'attendance_correction_request_breaks';
    
    protected $fillable = [
        'correction_request_id',
        'requested_break_start',
        'requested_break_end',
    ];

    protected $casts = [
        'requested_break_start' => 'datetime',
        'requested_break_end' => 'datetime',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(AttendanceCorrection::class, 'correction_request_id');
    }
}