<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Qualification;
use App\Models\Experience;
use App\Models\EmergencyContact;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EmployeeProfileController extends Controller
{
    /**
     * Get complete employee profile with all related data
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee profile not found'
                ], 404);
            }

            // Load all relationships
            $employee->load([
                'departments',
                'salaryStructure',
                'qualifications' => function($q) {
                    $q->orderBy('year', 'desc');
                },
                'experiences' => function($q) {
                    $q->orderBy('start_date', 'desc');
                },
                'documents' => function($q) {
                    $q->where('is_verified', true);
                },
                'emergencyContacts'
            ]);

            // Get reporting to information
            $reportingTo = null;
            if ($employee->reports_to) {
                $manager = Employee::find($employee->reports_to);
                if ($manager) {
                    $reportingTo = [
                        'id' => $manager->id,
                        'name' => $manager->first_name . ' ' . $manager->last_name,
                        'designation' => $manager->designation,
                    ];
                }
            }

            // Format department data
            $departments = [];
            if ($employee->departments && $employee->departments->count() > 0) {
                $departments = $employee->departments->map(function($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'role' => $dept->pivot->role ?? null,
                    ];
                })->toArray();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'personal' => [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'first_name' => $employee->first_name,
                        'middle_name' => $employee->middle_name,
                        'last_name' => $employee->last_name,
                        'full_name' => $employee->first_name . 
                            ($employee->middle_name ? ' ' . $employee->middle_name : '') . 
                            ' ' . $employee->last_name,
                        'email' => $employee->email,
                        'mobile_number' => $employee->mobile_number,
                        'alternate_phone' => $employee->alternate_phone,
                        'date_of_birth' => $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : null,
                        'gender' => $employee->gender,
                        'marital_status' => $employee->marital_status,
                        'blood_group' => $employee->blood_group,
                        'nationality' => $employee->nationality,
                        'religion' => $employee->religion,
                        'citizenship_number' => $employee->citizenship_number,
                        'citizenship_issue_date' => $employee->citizenship_issue_date ? $employee->citizenship_issue_date->format('Y-m-d') : null,
                        'citizenship_issued_district' => $employee->citizenship_issued_district,
                        'pan_number' => $employee->pan_number,
                        'profile_image' => $employee->profile_image ? url('storage/' . $employee->profile_image) : null,
                    ],
                    
                    'address' => [
                        // web profile uses current_address and ward/municipality/district
                        'current_address' => $employee->current_address ?? $employee->present_address,
                        'permanent_address' => $employee->permanent_address,
                        'municipality' => $employee->municipality,
                        'ward_number' => $employee->ward_number,
                        'district' => $employee->district,
                        'city' => $employee->city,
                        'state' => $employee->state,
                        'country' => $employee->country,
                        'postal_code' => $employee->postal_code,
                    ],
                    
                    'emergency_contacts' => $employee->emergencyContacts->map(function($contact) {
                        return [
                            'id' => $contact->id,
                            'name' => $contact->name,
                            'relationship' => $contact->relationship,
                            'phone' => $contact->phone,
                            'phone2' => $contact->phone2,
                            'email' => $contact->email,
                            'address' => $contact->address,
                            'is_primary' => (bool)$contact->is_primary,
                        ];
                    }),
                    
                    'qualifications' => $employee->qualifications->map(function($qual) {
                        return [
                            'id' => $qual->id,
                            'degree' => $qual->degree,
                            'institution' => $qual->institution,
                            'board' => $qual->board,
                            'year' => (int)$qual->year,
                            'percentage' => (float)$qual->percentage,
                            'grade' => $qual->grade,
                            'specialization' => $qual->specialization,
                            'start_date' => $qual->start_date ? $qual->start_date->format('Y-m-d') : null,
                            'end_date' => $qual->end_date ? $qual->end_date->format('Y-m-d') : null,
                            'is_pursuing' => (bool)$qual->is_pursuing,
                            'certificate_url' => $qual->certificate_path ? url('storage/' . $qual->certificate_path) : null,
                        ];
                    }),
                    
                    'experiences' => $employee->experiences->map(function($exp) {
                        return [
                            'id' => $exp->id,
                            'company' => $exp->company,
                            'position' => $exp->position,
                            'location' => $exp->location,
                            'start_date' => $exp->start_date->format('Y-m-d'),
                            'end_date' => $exp->end_date ? $exp->end_date->format('Y-m-d') : null,
                            'is_current' => (bool)$exp->is_current,
                            'description' => $exp->description,
                            'achievements' => $exp->achievements,
                            'certificate_url' => $exp->certificate_path ? url('storage/' . $exp->certificate_path) : null,
                        ];
                    }),
                    
                    'documents' => $employee->documents->map(function($doc) {
                        return [
                            'id' => $doc->id,
                            'type' => $doc->type,
                            'document_number' => $doc->document_number,
                            'issue_date' => $doc->issue_date ? $doc->issue_date->format('Y-m-d') : null,
                            'expiry_date' => $doc->expiry_date ? $doc->expiry_date->format('Y-m-d') : null,
                            'issue_place' => $doc->issue_place,
                            'file_url' => url('storage/' . $doc->file_path),
                            'is_verified' => (bool)$doc->is_verified,
                        ];
                    }),
                    
                    'employment' => [
                        'designation' => $employee->designation,
                        'department' => $departments,
                        'employee_type' => $employee->employee_type,
                        'employment_status' => $employee->employment_status,
                        'joining_date' => $employee->joining_date ? $employee->joining_date->format('Y-m-d') : null,
                        'confirmation_date' => $employee->confirmation_date ? $employee->confirmation_date->format('Y-m-d') : null,
                        'probation_end_date' => $employee->probation_end_date ? $employee->probation_end_date->format('Y-m-d') : null,
                        'contract_end_date' => $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : null,
                        'work_shift' => $employee->work_shift,
                        'reporting_to' => $reportingTo,
                        // additional summary from employee record
                        'qualification' => $employee->qualification,
                        'institution_name' => $employee->institution_name,
                        'experience_years' => $employee->experience_years,
                    ],
                    
                    'salary' => $employee->salaryStructure ? [
                        'basic_salary' => (float)($employee->salaryStructure->basic_salary ?? 0),
                        'overtime_rate' => (float)($employee->salaryStructure->overtime_rate ?? 0),
                        'bank_name' => $employee->bank_name,
                        'branch_name' => $employee->branch_name,
                        'account_number' => $employee->account_number ? '****' . substr($employee->account_number, -4) : null,
                        'account_holder' => $employee->account_holder_name,
                        'ifsc_code' => $employee->ifsc_code,
                        'pan_number' => $employee->pan_number,
                        'uan_number' => $employee->uan_number,
                        'esi_number' => $employee->esi_number,
                    ] : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update personal information
     */
    public function updatePersonal(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'mobile_number' => 'sometimes|string|max:20',
                'alternative_number' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'marital_status' => 'nullable|in:single,married,divorced,widowed',
                'blood_group' => 'nullable|string|max:10',
                'nationality' => 'nullable|string|max:100',
                'religion' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee->update($request->only([
                'first_name', 'middle_name', 'last_name', 'mobile_number',
                'alternative_number', 'date_of_birth', 'gender', 
                'marital_status', 'blood_group', 'nationality', 'religion'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Personal information updated successfully',
                'data' => [
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'last_name' => $employee->last_name,
                    'mobile_number' => $employee->mobile_number,
                    'alternative_number' => $employee->alternative_number,
                    'date_of_birth' => $employee->date_of_birth,
                    'gender' => $employee->gender,
                    'marital_status' => $employee->marital_status,
                    'blood_group' => $employee->blood_group,
                    'nationality' => $employee->nationality,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update personal information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update address information
     */
    public function updateAddress(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $validator = Validator::make($request->all(), [
                'present_address' => 'nullable|string',
                'permanent_address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee->update($request->only([
                'present_address', 'permanent_address', 'city',
                'state', 'country', 'postal_code'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Address updated successfully',
                'data' => [
                    'present_address' => $employee->present_address,
                    'permanent_address' => $employee->permanent_address,
                    'city' => $employee->city,
                    'state' => $employee->state,
                    'country' => $employee->country,
                    'postal_code' => $employee->postal_code,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profile picture
     */
    public function updateProfileImage(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old image
            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }

            // Upload new image
            $path = $request->file('profile_image')->store('employees/profile', 'public');
            $employee->profile_image = $path;
            $employee->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile image updated successfully',
                'data' => [
                    'profile_image_url' => url('storage/' . $path)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add/Update emergency contact
     */
    public function upsertEmergencyContact(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $validator = Validator::make($request->all(), [
                'id' => 'nullable|exists:emergency_contacts,id',
                'name' => 'required|string|max:255',
                'relationship' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'phone2' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'is_primary' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->except('id');
            
            // If setting as primary, unset other primary contacts
            if ($request->is_primary) {
                $employee->emergencyContacts()->update(['is_primary' => false]);
            }

            if ($request->id) {
                $contact = EmergencyContact::where('employee_id', $employee->id)
                    ->where('id', $request->id)
                    ->first();
                    
                if (!$contact) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Contact not found'
                    ], 404);
                }
                
                $contact->update($data);
                $message = 'Contact updated successfully';
            } else {
                $data['employee_id'] = $employee->id;
                $contact = EmergencyContact::create($data);
                $message = 'Contact added successfully';
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $contact
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save emergency contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete emergency contact
     */
    public function deleteEmergencyContact(Request $request, $id)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $contact = EmergencyContact::where('employee_id', $employee->id)
                ->where('id', $id)
                ->first();
            
            if (!$contact) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Contact not found'
                ], 404);
            }

            $contact->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Contact deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add/Update qualification
     */
    public function upsertQualification(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $validator = Validator::make($request->all(), [
                'id' => 'nullable|exists:qualifications,id',
                'degree' => 'required|string|max:255',
                'institution' => 'required|string|max:255',
                'board' => 'nullable|string|max:255',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
                'percentage' => 'nullable|numeric|min:0|max:100',
                'grade' => 'nullable|string|max:50',
                'specialization' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'is_pursuing' => 'boolean',
                'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->except(['id', 'certificate']);

            if ($request->hasFile('certificate')) {
                $path = $request->file('certificate')->store('employees/qualifications', 'public');
                $data['certificate_path'] = $path;
            }

            if ($request->id) {
                $qualification = Qualification::where('employee_id', $employee->id)
                    ->where('id', $request->id)
                    ->first();
                    
                if (!$qualification) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Qualification not found'
                    ], 404);
                }
                
                // Delete old certificate if new one uploaded
                if ($request->hasFile('certificate') && $qualification->certificate_path) {
                    Storage::disk('public')->delete($qualification->certificate_path);
                }
                
                $qualification->update($data);
                $message = 'Qualification updated successfully';
            } else {
                $data['employee_id'] = $employee->id;
                $qualification = Qualification::create($data);
                $message = 'Qualification added successfully';
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $qualification
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete qualification
     */
    public function deleteQualification(Request $request, $id)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $qualification = Qualification::where('employee_id', $employee->id)
                ->where('id', $id)
                ->first();
            
            if (!$qualification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Qualification not found'
                ], 404);
            }

            // Delete certificate file
            if ($qualification->certificate_path) {
                Storage::disk('public')->delete($qualification->certificate_path);
            }

            $qualification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Qualification deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add/Update work experience
     */
    public function upsertExperience(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $validator = Validator::make($request->all(), [
                'id' => 'nullable|exists:experiences,id',
                'company' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'is_current' => 'boolean',
                'description' => 'nullable|string',
                'achievements' => 'nullable|string',
                'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->except(['id', 'certificate']);
            
            // If is_current is true, set end_date to null
            if ($request->is_current) {
                $data['end_date'] = null;
            }

            if ($request->hasFile('certificate')) {
                $path = $request->file('certificate')->store('employees/experiences', 'public');
                $data['certificate_path'] = $path;
            }

            if ($request->id) {
                $experience = Experience::where('employee_id', $employee->id)
                    ->where('id', $request->id)
                    ->first();
                    
                if (!$experience) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Experience not found'
                    ], 404);
                }
                
                // Delete old certificate if new one uploaded
                if ($request->hasFile('certificate') && $experience->certificate_path) {
                    Storage::disk('public')->delete($experience->certificate_path);
                }
                
                $experience->update($data);
                $message = 'Experience updated successfully';
            } else {
                $data['employee_id'] = $employee->id;
                $experience = Experience::create($data);
                $message = 'Experience added successfully';
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $experience
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save experience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete work experience
     */
    public function deleteExperience(Request $request, $id)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $experience = Experience::where('employee_id', $employee->id)
                ->where('id', $id)
                ->first();
            
            if (!$experience) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Experience not found'
                ], 404);
            }

            if ($experience->certificate_path) {
                Storage::disk('public')->delete($experience->certificate_path);
            }

            $experience->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Experience deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete experience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all emergency contacts
     */
    public function getEmergencyContacts(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $contacts = $employee->emergencyContacts()->orderBy('is_primary', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $contacts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get emergency contacts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all qualifications
     */
    public function getQualifications(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $qualifications = $employee->qualifications()->orderBy('year', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $qualifications
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get qualifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all experiences
     */
    public function getExperiences(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            $experiences = $employee->experiences()->orderBy('start_date', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $experiences
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get experiences',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
