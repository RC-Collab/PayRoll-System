<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    // display the holiday management page with filters
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $type = $request->get('type', 'all');

        $query = Holiday::where('organization_id', 1);
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        $query->whereYear('date', $year)->orderBy('date');

        $holidays = $query->get()->groupBy(function ($h) {
            return Carbon::parse($h->date)->format('F');
        });

        return view('attendance.holidays', compact('holidays', 'year', 'type'));
    }

    // return one holiday as JSON for editing
    public function show($id)
    {
        $holiday = Holiday::find($id);
        if (! $holiday) {
            return response()->json(['success' => false, 'message' => 'Holiday not found'], 404);
        }

        return response()->json($holiday);
    }

    // create a new holiday
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            // use the same set of types as the attendance settings UI
            'type' => 'required|in:national,regional,company,optional',
            'description' => 'nullable|string',
            'is_recurring' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['organization_id'] = 1;
        $validated['is_recurring'] = $request->has('is_recurring');
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $holiday = Holiday::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Holiday added successfully',
            'data' => $holiday,
        ], 201);
    }

    // update existing holiday
    public function update(Request $request)
    {
        $id = $request->input('id');
        $holiday = Holiday::find($id);
        if (! $holiday) {
            return response()->json(['success' => false, 'message' => 'Holiday not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'type' => 'required|in:national,regional,company,optional',
            'description' => 'nullable|string',
            'is_recurring' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_recurring'] = $request->has('is_recurring');
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $holiday->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Holiday updated successfully',
            'data' => $holiday,
        ]);
    }

    // delete holiday
    public function destroy($id)
    {
        $holiday = Holiday::find($id);
        if (! $holiday) {
            return response()->json(['success' => false, 'message' => 'Holiday not found'], 404);
        }

        $holiday->delete();

        return response()->json(['success' => true, 'message' => 'Holiday deleted successfully']);
    }

    // bulk upload holidays from CSV
    public function bulkUpload(Request $request)
    {
        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return response()->json(['success' => false, 'message' => 'Invalid file'], 400);
        }

        $errors = [];
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        // expected columns: date,name,type,description,is_recurring,is_active
        while (($row = fgetcsv($handle)) !== false) {
            $rowData = array_combine($header, $row);
            $validator = Validator::make($rowData, [
                'name' => 'required|string|max:255',
                'date' => 'required|date|unique:holidays,date',
                'type' => 'required|in:national,regional,company,optional',
                'description' => 'nullable|string',
                'is_recurring' => 'sometimes|boolean',
                'is_active' => 'sometimes|boolean',
            ]);
            if ($validator->fails()) {
                $errors[] = implode('; ', $validator->errors()->all());
                continue;
            }

            $data = $validator->validated();
            $data['organization_id'] = 1;
            $data['is_recurring'] = isset($rowData['is_recurring']) ? (bool)$rowData['is_recurring'] : false;
            $data['is_active'] = isset($rowData['is_active']) ? (bool)$rowData['is_active'] : true;
            Holiday::create($data);
        }
        fclose($handle);

        return response()->json(['success' => true, 'message' => 'Bulk upload completed', 'errors' => $errors]);
    }
}
