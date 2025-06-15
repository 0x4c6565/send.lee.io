<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">Login</h2>
        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div class="relative">
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    autocomplete="email"
                    autofocus
                    placeholder=" "
                    class="peer w-full px-4 py-2 border rounded-md border-gray-300 placeholder-transparent focus:outline-none focus:border-blue-500">
                <label
                    for="email"
                    class="absolute left-4 -top-2.5 text-gray-500 bg-white px-1 transition-all peer-placeholder-shown:top-2.5 peer-placeholder-shown:left-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:left-4 peer-focus:text-blue-500 peer-focus:text-sm">
                    Email Address
                </label>
            </div>

            <div class="relative">
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    autocomplete="current-password"
                    placeholder=" "
                    class="peer w-full px-4 py-2 border rounded-md border-gray-300 placeholder-transparent focus:outline-none focus:border-blue-500">
                <label
                    for="password"
                    class="absolute left-4 -top-2.5 text-gray-500 bg-white px-1 transition-all peer-placeholder-shown:top-2.5 peer-placeholder-shown:left-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:left-4 peer-focus:text-blue-500 peer-focus:text-sm">
                    Password
                </label>
            </div>


            @if (\Session::has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 my-3 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{!! \Session::get('error') !!}</span>
            </div>
            @endif
            <button type="submit" class="w-full py-2 bg-blue-500 text-white rounded-md font-medium hover:bg-blue-600 transition duration-200">Login</button>
        </form>
    </div>
</body>

</html>