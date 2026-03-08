<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Login</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-800">

    <div class="w-full max-w-md bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20">

        <h1 class="text-3xl font-bold text-white text-center mb-2">
            Payroll System
        </h1>

        <p class="text-gray-300 text-center mb-6">
            Secure employee access
        </p>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <!-- Display status message -->
            @if (session('status'))
                <div class="bg-green-500 text-white text-sm p-2 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Display Errors -->
            @if ($errors->any())
                <div class="bg-red-500 text-white text-sm p-2 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="text-sm text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 text-white placeholder-gray-300 border border-white/30 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div class="relative">
                <label class="text-sm text-gray-300">Password</label>
                <input type="password" name="password" required
                    class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 text-white placeholder-gray-300 border border-white/30 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <button
                class="w-full py-3 bg-amber-500 hover:bg-amber-600 transition rounded-lg text-white font-semibold">
                Login
            </button>
        </form>

        <div class="mt-6 flex justify-between text-sm text-gray-300">
            <a href="{{ route('activate.form') }}" class="hover:text-amber-400">Activate Account</a>
            <a href="{{ route('forgot-password') }}" class="hover:text-amber-400">Forgot Password?</a>
        </div>

    </div>

</body>
</html>