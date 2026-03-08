<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'contact_during_leave' => $this->contact_during_leave,
            'medical_certificate' => $this->medical_certificate,
            'approved_by' => $this->approver?->name,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'approval_remarks' => $this->approval_remarks,
            'rejected_by' => $this->rejected_by,
            'rejected_at' => $this->rejected_at?->format('Y-m-d H:i:s'),
            'remarks' => $this->remarks,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
