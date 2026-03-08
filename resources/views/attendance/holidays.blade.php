{{-- resources/views/attendance/holidays.blade.php --}}
@extends('layouts.app')

@section('title', 'Holiday Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">🎉 Holiday Management</h1>
                <p class="text-slate-500">Manage holidays to exclude them from attendance calculations</p>
            </div>
            <div class="flex gap-3">
                <button onclick="showAddHolidayModal()" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    ➕ Add Holiday
                </button>
                <button onclick="showBulkUploadModal()" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    📤 Bulk Upload
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Year</label>
                <select id="yearFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Holiday Type</label>
                <select id="typeFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="national" {{ $type == 'national' ? 'selected' : '' }}>National</option>
                    <option value="regional" {{ $type == 'regional' ? 'selected' : '' }}>Regional</option>
                    <option value="company" {{ $type == 'company' ? 'selected' : '' }}>Company</option>
                    <option value="optional" {{ $type == 'optional' ? 'selected' : '' }}>Optional</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button onclick="applyHolidayFilters()" 
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    🔍 Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Holiday Calendar -->
    <div class="bg-white rounded-xl shadow overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-xl font-semibold text-slate-800">🗓️ Holiday Calendar {{ $year }}</h2>
            <p class="text-slate-500">All scheduled holidays for the selected year</p>
        </div>
        
        @if(count($holidays) > 0)
            @foreach($holidays as $month => $monthHolidays)
            <div class="border-b border-slate-200 last:border-b-0">
                <div class="px-6 py-4 bg-slate-50">
                    <h3 class="font-semibold text-slate-700">{{ $month }} ({{ count($monthHolidays) }} holidays)</h3>
                </div>
                
                <div class="divide-y divide-slate-200">
                    @foreach($monthHolidays as $holiday)
                    <div class="px-6 py-4 hover:bg-slate-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center 
                                    {{ $holiday->type == 'national' ? 'bg-red-100' : 
                                       ($holiday->type == 'company' ? 'bg-blue-100' : 
                                       ($holiday->type == 'regional' ? 'bg-green-100' : 'bg-yellow-100')) }}">
                                    <span class="text-lg font-bold text-slate-800">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('d') }}
                                    </span>
                                    <span class="text-xs text-slate-600">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('D') }}
                                    </span>
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-slate-800">{{ $holiday->name }}</h4>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $holiday->type == 'national' ? 'bg-red-100 text-red-800' : 
                                               ($holiday->type == 'company' ? 'bg-blue-100 text-blue-800' : 
                                               ($holiday->type == 'regional' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                            {{ ucfirst($holiday->type) }}
                                        </span>
                                        @if($holiday->is_recurring)
                                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                            🔁 Recurring
                                        </span>
                                        @endif
                                        @if(!$holiday->is_active)
                                        <span class="px-2 py-1 text-xs bg-slate-100 text-slate-800 rounded-full">
                                            Inactive
                                        </span>
                                        @endif
                                    </div>
                                    @if($holiday->description)
                                    <p class="text-sm text-slate-600 mt-2">{{ $holiday->description }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="editHoliday({{ $holiday->id }})" 
                                        class="px-3 py-1 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">
                                    ✏️ Edit
                                </button>
                                <button onclick="deleteHoliday({{ $holiday->id }})" 
                                        class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @else
        <div class="p-12 text-center">
            <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                🎉
            </div>
            <h3 class="text-lg font-semibold text-slate-700 mb-2">No Holidays Found</h3>
            <p class="text-slate-500">No holidays scheduled for {{ $year }}</p>
        </div>
        @endif
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-100 rounded-lg">
                    🎌
                </div>
                <div>
                    <p class="text-sm text-slate-500">National Holidays</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">
                        {{ collect($holidays)->flatten()->where('type', 'national')->where('is_active', true)->count() }}
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-100 rounded-lg">
                    🏢
                </div>
                <div>
                    <p class="text-sm text-slate-500">Company Holidays</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">
                        {{ collect($holidays)->flatten()->where('type', 'company')->where('is_active', true)->count() }}
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-100 rounded-lg">
                    🔁
                </div>
                <div>
                    <p class="text-sm text-slate-500">Recurring Holidays</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">
                        {{ collect($holidays)->flatten()->where('is_recurring', true)->where('is_active', true)->count() }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div id="addHolidayModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-xl font-semibold text-slate-800">➕ Add New Holiday</h3>
        </div>
        
        <div class="p-6">
            <form id="holidayForm">
                @csrf
                <input type="hidden" id="holiday_id" name="id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Holiday Name *</label>
                        <input type="text" name="name" required 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date *</label>
                        <input type="date" name="date" required 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Type *</label>
                        <select name="type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            <option value="national">National Holiday</option>
                            <option value="regional">Regional Holiday</option>
                            <option value="company">Company Holiday</option>
                            <option value="optional">Optional Holiday</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-2 border border-slate-300 rounded-lg"></textarea>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_recurring" value="1" 
                                   class="mr-2 rounded border-slate-300">
                            <span class="text-sm">Recurring (every year)</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="mr-2 rounded border-slate-300">
                            <span class="text-sm">Active</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeHolidayModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Upload Modal -->
<div id="bulkUploadModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-xl font-semibold text-slate-800">📤 Bulk Upload Holidays</h3>
        </div>
        
        <div class="p-6">
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-medium text-blue-800 mb-2">📋 CSV Format Required:</h4>
                <p class="text-sm text-blue-600 mb-2">Columns: Date, Title, Type, Description, Recurring (0/1)</p>
                <p class="text-sm text-blue-600">Example: 2024-01-26,Republic Day,national,National Holiday,1</p>
                <a href="/templates/holidays-template.csv" 
                   class="inline-block mt-3 text-sm text-indigo-600 hover:text-indigo-800">
                    📥 Download Template
                </a>
            </div>
            
            <form id="bulkUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Year *</label>
                        <select name="year" required class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">CSV File *</label>
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" required 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeBulkUploadModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter management
function applyHolidayFilters() {
    const year = document.getElementById('yearFilter').value;
    const type = document.getElementById('typeFilter').value;
    
    let url = '{{ route("attendance.holidays") }}?year=' + year;
    if (type !== 'all') url += '&type=' + type;
    
    window.location.href = url;
}

// Holiday modal functions
function showAddHolidayModal() {
    document.getElementById('holiday_id').value = '';
    document.getElementById('holidayForm').reset();
    document.getElementById('addHolidayModal').classList.remove('hidden');
    document.getElementById('addHolidayModal').classList.add('flex');
}

function editHoliday(id) {
    fetch(`/attendance/holidays/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('holiday_id').value = data.id;
            document.querySelector('input[name="title"]').value = data.name;
            document.querySelector('input[name="date"]').value = data.date;
            document.querySelector('select[name="type"]').value = data.type;
            document.querySelector('textarea[name="description"]').value = data.description || '';
            document.querySelector('input[name="is_recurring"]').checked = data.is_recurring;
            document.querySelector('input[name="is_active"]').checked = data.is_active;
            
            document.getElementById('addHolidayModal').classList.remove('hidden');
            document.getElementById('addHolidayModal').classList.add('flex');
        });
}

function closeHolidayModal() {
    document.getElementById('addHolidayModal').classList.add('hidden');
    document.getElementById('addHolidayModal').classList.remove('flex');
}

function showBulkUploadModal() {
    document.getElementById('bulkUploadModal').classList.remove('hidden');
    document.getElementById('bulkUploadModal').classList.add('flex');
}

function closeBulkUploadModal() {
    document.getElementById('bulkUploadModal').classList.add('hidden');
    document.getElementById('bulkUploadModal').classList.remove('flex');
}

// Handle holiday form submission
document.getElementById('holidayForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const holidayId = document.getElementById('holiday_id').value;
    const url = holidayId ? '/attendance/holidays/update' : '/attendance/holidays/store';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeHolidayModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save holiday');
    });
});

// Handle bulk upload
document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/attendance/holidays/bulk-upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            if (data.errors && data.errors.length > 0) {
                alert('Some errors occurred:\n' + data.errors.join('\n'));
            }
            closeBulkUploadModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to upload holidays');
    });
});

// Delete holiday
function deleteHoliday(id) {
    if (confirm('Are you sure you want to delete this holiday?')) {
        fetch(`/attendance/holidays/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete holiday');
        });
    }
}
</script>
@endpush