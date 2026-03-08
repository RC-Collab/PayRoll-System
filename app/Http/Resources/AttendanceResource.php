<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'status' => $this->status,
            'check_in' => $this->check_in?->format('H:i:s'),
            'check_out' => $this->check_out?->format('H:i:s'),
            'total_hours' => $this->total_hours,
            'duration' => $this->duration,
            'is_late' => $this->is_late,
            'late_minutes' => $this->late_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'overtime_hours' => $this->overtime, // convenience field
            'regular_hours' => $this->regular_hours,
            'notes' => $this->notes,
        ];
    }
}
