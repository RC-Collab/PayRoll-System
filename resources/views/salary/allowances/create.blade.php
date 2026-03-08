@extends('layouts.app')

@section('title', 'Create Allowance')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Create New Allowance</h1>
        <a href="{{ route('salary.allowances.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Allowances
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">New Allowance Details</h5>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                <h4>Feature Under Development</h4>
                <p class="text-muted mb-4">The allowance creation form is currently being built.</p>
                
                <div class="alert alert-warning d-inline-block">
                    <i class="fas fa-clock me-2"></i>
                    This feature will be available in the next update.
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('salary.allowances.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection