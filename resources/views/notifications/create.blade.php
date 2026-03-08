@extends('layouts.app')

@section('title', 'Send Notification')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
            <span class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-3 rounded-lg">
                <i class="fas fa-bell"></i>
            </span>
            Send Notification
        </h1>
        <p class="text-gray-600 mt-2">Quick and easy notification sending</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
        <form action="{{ route('notifications.store') }}" method="POST" class="p-8">
            @csrf

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-semibold text-gray-800 mb-2">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title" required maxlength="255"
                       placeholder="e.g., Salary Slip Ready, Leave Approved"
                       value="{{ old('title') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('title')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Message -->
            <div class="mb-6">
                <label for="message" class="block text-sm font-semibold text-gray-800 mb-2">
                    Message <span class="text-red-500">*</span>
                </label>
                <textarea id="message" name="message" required rows="5"
                          placeholder="Enter your notification message..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Send To - By Role -->
            <div class="mb-8">
                <label for="role" class="block text-sm font-semibold text-gray-800 mb-3">
                    Send To <span class="text-red-500">*</span>
                </label>
                <select name="role" id="role" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- Select Role or Type --</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👤 Admin Only</option>
                    <option value="hr" {{ old('role') == 'hr' ? 'selected' : '' }}>💼 HR Only</option>
                    <option value="accountant" {{ old('role') == 'accountant' ? 'selected' : '' }}>📊 Accountant Only</option>
                    <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>👨‍💼 Employee Only</option>
                    <option value="all" {{ old('role') == 'all' ? 'selected' : '' }}>👥 All Roles (Everyone)</option>
                    <option value="public" {{ old('role') == 'public' ? 'selected' : '' }}>📢 Public Notification</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-2">💡 Select "All Roles" to send to all users, or "Public" for a broadcast notification</p>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i>Send Notification
                </button>
                <a href="{{ route('dashboard') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Form validation - ensure role is selected
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedRole = document.getElementById('role').value;
        if (!selectedRole) {
            e.preventDefault();
            alert('Please select a role');
            return;
        }
    });
</script>

<style>
    /* No complex styling needed for simple role select */
</style>
@endsection
