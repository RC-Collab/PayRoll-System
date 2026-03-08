<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activate Account</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-800">

    <div class="w-full max-w-md bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20">

        <h1 class="text-3xl font-bold text-white text-center mb-2">
            Activate Account
        </h1>

        <p class="text-gray-300 text-center mb-6">
            Enter your email or phone to activate
        </p>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg">
                @foreach ($errors->all() as $error)
                    <p class="text-red-400 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('activate.send') }}" class="space-y-5">
            @csrf

            <div>
                <label class="text-sm text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 text-white placeholder-gray-300 border border-white/30 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="text-sm text-gray-300">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                    class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 text-white placeholder-gray-300 border border-white/30 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 transition rounded-lg text-white font-semibold">
                Activate
            </button>
        </form>

        <div class="mt-6 flex justify-between text-sm text-gray-300">
            <a href="{{ route('login') }}" class="hover:text-amber-400">Back to Login</a>
            <a href="#" onclick="window.history.back()" class="hover:text-amber-400">Cancel</a>
        </div>

    </div>

</body>
</html>