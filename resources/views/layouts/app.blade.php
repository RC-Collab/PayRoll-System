<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nepal Payroll') | Payroll System</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- CDNs for Styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Custom Styles */
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #3a0ca3;
            --accent-teal: #4cc9f0;
            --success-green: #10b981;
            --warning-amber: #f59e0b;
            --danger-red: #ef4444;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
        }
        
        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            margin-bottom: 0.25rem;
        }
        
        .sidebar-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-btn.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: inset 3px 0 0 #4cc9f0;
        }
        
        .sidebar-btn i {
            width: 20px;
            text-align: center;
        }
        
        /* Dropdown Styling */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .dropdown-content.open {
            max-height: 500px;
        }
        
        .dropdown-toggle .arrow {
            transition: transform 0.3s ease;
        }
        
        .dropdown-toggle.open .arrow {
            transform: rotate(180deg);
        }
        
        /* Main Content */
        .main-header {
            background: linear-gradient(90deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
        }
        
        /* Card Styling */
        .dashboard-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Table Styling */
        .data-table {
            width: 100%;
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table tr:hover {
            background-color: #f9fafb;
        }
        
        /* Badge Styling */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .badge-success { background-color: #10b981; color: white; }
        .badge-warning { background-color: #f59e0b; color: white; }
        .badge-info { background-color: #3b82f6; color: white; }
        .badge-secondary { background-color: #6b7280; color: white; }
        .badge-danger { background-color: #ef4444; color: white; }
        .badge-dark { background-color: #1f2937; color: white; }
        
        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
        }
        
        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .btn-outline {
            background-color: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        
        .btn-outline:hover {
            background-color: #f9fafb;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        /* Form Styling */
        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        /* Alert Styling */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
        }
        
        .page-link {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            background: white;
            border: 1px solid #d1d5db;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .page-link:hover {
            background: #f3f4f6;
        }
        
        .page-item.active .page-link {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .sidebar {
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">

<div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside id="sidebar"
           class="sidebar fixed inset-y-0 left-0 w-72 text-white flex flex-col transition-transform duration-300 md:translate-x-0 -translate-x-full z-50">

        <!-- Logo + Close Button -->
        <div class="px-6 py-6 flex items-center justify-between border-b border-blue-700">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fas fa-money-check-alt text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">{{ \App\Models\SystemSetting::where('key', 'company_name')->first()?->value ?? 'Nepal Payroll' }}</h1>
                    <p class="text-xs opacity-80">Management System</p>
                </div>
            </div>
            <button id="sidebar-close" class="md:hidden text-white hover:text-blue-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-4 space-y-1 overflow-y-auto py-6">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="sidebar-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                Dashboard
            </a>

            <!-- Employees -->
            <a href="{{ route('employees.index') }}" 
               class="sidebar-btn {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                Employees
            </a>
            
            <!-- Attendance -->
            @if(\Route::has('attendance.index'))
            <a href="{{ route('attendance.index') }}" 
               class="sidebar-btn {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                Attendance
            </a>
            @endif
            
            <!-- Simple Salary Link (No Dropdown) -->
<a href="{{ route('salary.index') }}" 
   class="sidebar-btn {{ request()->routeIs('salary.*') ? 'active' : '' }}">
    <i class="fas fa-money-bill-wave"></i>
    Salary
</a>
            
            <!-- Leaves -->
            <a href="{{ route('leaves.index') }}" 
               class="sidebar-btn {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                <i class="fas fa-umbrella-beach"></i>
                Leaves
            </a>
            
            <!-- Departments -->
            <a href="{{ route('departments.index') }}" 
               class="sidebar-btn {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                Departments
            </a>
            
            
            
<!-- Settings Link -->
<a href="{{ route('settings.index') }}" 
   class="sidebar-btn {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i>
    Settings
</a>

        </nav>

        <!-- Footer -->
        <div class="px-4 py-4 border-t border-blue-700">
            <div class="flex items-center justify-between mb-3 text-sm opacity-80">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-day"></i>
                    {{ now()->format('M d, Y') }}
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-clock"></i>
                    {{ now('Asia/Kathmandu')->format('h:i A') }}
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="w-full" id="logoutForm">
                @csrf
                <button type="button" onclick="confirmLogout()" class="w-full sidebar-btn bg-white/10 hover:bg-white/20 border border-white/20 text-left">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE MENU BUTTON -->
    <button id="mobile-menu-button"
            class="fixed top-4 left-4 z-50 md:hidden bg-blue-600 text-white p-3 rounded-full shadow-lg">
        <i class="fas fa-bars"></i>
    </button>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col md:ml-72">
        <!-- TOP NAVBAR -->
        <header class="main-header px-6 py-4 flex justify-between items-center sticky top-0 z-10">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ now()->format('l, F d, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-6">
                <!-- Notifications with Dropdown -->
                <div class="relative group">
                    <button onclick="openNotificationsModal()" class="p-2 hover:bg-gray-100 rounded-full transition relative" title="Notifications">
                        <i class="fas fa-bell text-gray-600 text-lg"></i>
                        <span id="notificationBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold hidden animate-pulse">
                            0
                        </span>
                    </button>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-3 hover:bg-gray-100 rounded-full px-3 py-2 transition" onclick="toggleUserMenu()">
                        <div class="text-right hidden md:block">
                            <p class="font-semibold text-gray-800 text-sm leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role ?? 'User' }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold shadow-md">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </button>
                    
                    <!-- User Menu Dropdown -->
                    <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 hidden group-hover:block z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500 capitalize">{{ auth()->user()->role ?? 'User' }}</p>
                        </div>
                        @if(in_array(auth()->user()->role, ['admin', 'hr']))
                        <a href="{{ route('notifications.create') }}" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 border-b border-gray-200">
                            <i class="fas fa-plus mr-2 text-blue-600"></i>Create Notification
                        </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <section class="flex-1 px-4 md:px-6 py-6 overflow-y-auto">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('warning') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong class="block">Please fix the following errors:</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            <!-- Main Content -->
            @yield('content')
        </section>
    </main>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const closeBtn = document.getElementById('sidebar-close');
    const mobileBtn = document.getElementById('mobile-menu-button');

    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        mobileBtn.classList.toggle('hidden');
    });

    closeBtn.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        mobileBtn.classList.remove('hidden');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 768 && 
            !sidebar.contains(e.target) && 
            !mobileBtn.contains(e.target) && 
            !sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.add('-translate-x-full');
            mobileBtn.classList.remove('hidden');
        }
    });

    // Dropdown Toggle Function
    function toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const toggle = dropdown.previousElementSibling;
        
        dropdown.classList.toggle('open');
        toggle.classList.toggle('open');
    }

    // Auto-open dropdown if on active page
    document.addEventListener('DOMContentLoaded', function() {
        // Open salary dropdown if on salary/payroll page
        if (window.location.pathname.includes('/salary') || window.location.pathname.includes('/payroll')) {
            const salaryDropdown = document.getElementById('salary-dropdown');
            const salaryToggle = document.querySelector('[onclick="toggleDropdown(\'salary-dropdown\')"]');
            if (salaryDropdown && salaryToggle) {
                salaryDropdown.classList.add('open');
                salaryToggle.classList.add('open');
            }
        }
        
        // Open reports dropdown if on tax/reports page
        if (window.location.pathname.includes('/tax') || window.location.pathname.includes('/report')) {
            const reportsDropdown = document.getElementById('reports-dropdown');
            const reportsToggle = document.querySelector('[onclick="toggleDropdown(\'reports-dropdown\')"]');
            if (reportsDropdown && reportsToggle) {
                reportsDropdown.classList.add('open');
                reportsToggle.classList.add('open');
            }
        }
        
        // Initialize DataTables
        if ($.fn.DataTable.isDataTable('table')) {
            $('table').DataTable({
                pageLength: 25,
                responsive: true,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
        
        // Initialize Select2
        $('select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    // Toggle User Menu
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        menu.classList.toggle('hidden');
    }

    // Close user menu when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.getElementById('userMenu');
        const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');
        
        if (!userButton && !event.target.closest('#userMenu')) {
            userMenu?.classList.add('hidden');
        }
    });

    // Delete Confirmation
    function confirmDelete(url, itemName = 'this item') {
        if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
        return false;
    }

    // Modal Helper Functions
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            hideModal(e.target.id);
        }
    });

    // Escape key closes modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.style.display === 'flex') {
                    hideModal(modal.id);
                }
            });
        }
    });

    // Print Function
    function printDiv(divId) {
        const printContents = document.getElementById(divId).innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }

    // Logout Confirmation
    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out of your account',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm').submit();
            }
        });
    }
</script>

<!-- Include Notification Modal -->
@include('notifications.modal')

@stack('scripts')

</body>
</html>