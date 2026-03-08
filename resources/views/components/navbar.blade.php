<header class="bg-gray-900 flex justify-between items-center p-4 border-b border-gray-800 ml-64">
    <div class="text-xl font-semibold">Dashboard</div>
    <div class="flex items-center gap-4">
        <div class="text-gray-300">Hello, Roshan</div>
        <div class="relative group">
            <img src="https://via.placeholder.com/32" class="rounded-full cursor-pointer">
            <div class="absolute right-0 mt-2 w-40 bg-gray-800 rounded shadow-lg opacity-0 group-hover:opacity-100 transition">
                <a href="#" class="block px-4 py-2 hover:bg-indigo-600 rounded">Profile</a>
                <a href="#" class="block px-4 py-2 hover:bg-indigo-600 rounded">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-indigo-600 rounded">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>