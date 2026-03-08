@extends('layouts.app')

@section('title', 'Deduction Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Deduction Management</h1>
        <div>
            <a href="{{ route('salary.deductions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Deduction
            </a>
            <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Salary
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">All Deductions</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Default</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction->name }}</td>
                            <td>{{ $deduction->code }}</td>
                            <td>{{ ucfirst($deduction->type) }}</td>
                            <td>{{ $deduction->default_value }}</td>
                            <td>{{ $deduction->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <a href="{{ route('salary.deductions.edit', $deduction) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('salary.deductions.destroy', $deduction) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this deduction?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No deductions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection