@extends('layouts.app')

@section('title', 'Create Deduction')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Create New Deduction</h1>
        <a href="{{ route('salary.deductions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Deductions
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">New Deduction Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('salary.deductions.store') }}">
                @csrf
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Code</label>
                    <div class="col-sm-10">
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Type</label>
                    <div class="col-sm-10">
                        <select name="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="fixed" {{ old('type')=='fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percentage" {{ old('type')=='percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="formula" {{ old('type')=='formula' ? 'selected' : '' }}>Formula</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Default Value</label>
                    <div class="col-sm-10">
                        <input type="number" step="0.01" name="default_value" value="{{ old('default_value',0) }}" class="form-control @error('default_value') is-invalid @enderror">
                        @error('default_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Percentage</label>
                    <div class="col-sm-10">
                        <input type="number" step="0.01" name="percentage" value="{{ old('percentage') }}" class="form-control @error('percentage') is-invalid @enderror">
                        @error('percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Max Amount</label>
                    <div class="col-sm-10">
                        <input type="number" step="0.01" name="max_amount" value="{{ old('max_amount') }}" class="form-control @error('max_amount') is-invalid @enderror">
                        @error('max_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Formula</label>
                    <div class="col-sm-10">
                        <textarea name="formula" class="form-control @error('formula') is-invalid @enderror">{{ old('formula') }}</textarea>
                        @error('formula')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Description</label>
                    <div class="col-sm-10">
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-10 offset-sm-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" id="is_mandatory" value="1" {{ old('is_mandatory') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_mandatory">Mandatory</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active',1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Sort Order</label>
                    <div class="col-sm-10">
                        <input type="number" name="sort_order" value="{{ old('sort_order',0) }}" class="form-control @error('sort_order') is-invalid @enderror">
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('salary.deductions.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection