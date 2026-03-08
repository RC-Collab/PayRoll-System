<aside class="w-64 bg-gray-900 border-r border-gray-800 min-h-screen fixed">
    <div class="p-6 text-center font-bold text-xl border-b border-gray-800 mb-4">
        Payroll System
    </div>
    <nav class="flex flex-col gap-1">
        <a href="{{ route('dashboard') }}" class="px-6 py-3 hover:bg-indigo-600 rounded transition">Dashboard</a>
        <a href="{{ route('employees') }}" class="px-6 py-3 hover:bg-indigo-600 rounded transition">Employees</a>
        <a href="{{ route('payroll') }}" class="px-6 py-3 hover:bg-indigo-600 rounded transition">Payroll</a>
        <a href="{{ route('reports') }}" class="px-6 py-3 hover:bg-indigo-600 rounded transition">Reports</a>
        <a href="{{ route('settings') }}" class="px-6 py-3 hover:bg-indigo-600 rounded transition">Settings</a>
    </nav>
</aside>