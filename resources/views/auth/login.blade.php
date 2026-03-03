<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DepEd Zamboanga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    
    <header style="background-color: #a52a2a;" class="fixed p-1 flex justify-center z-40 w-full items-center">
        <img src="{{ asset('storage/images/deped_zambo_header.png') }}" class="w-full max-w-4xl h-auto block"
            alt="DepEd Zamboanga Header">
    </header>

    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat min-h-screen flex items-center pt-40 pb-10 px-4 md:px-8">
            
            <div class="absolute inset-0 bg-red-900/50"></div>

            <div class="relative z-10 w-full max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
                
                <div class="w-full md:w-1/2 text-center md:text-left text-white">
                    <a href="{{ url('/') }}" class="inline-flex items-center text-white/80 hover:text-white mb-6 group transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="font-medium">Back to Home</span>
                    </a>

                    <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">Welcome Back!</h1>
                    <p class="text-lg md:text-xl text-white/80 max-w-lg mx-auto md:mx-0">
                        Sign in to access your Learning Management System dashboard and continue your educational journey.
                    </p>
                </div>

                <div class="w-full md:w-1/2 max-w-md bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                    <div class="text-center mb-3">
                        <h2 class="text-2xl font-bold text-gray-800">Login</h2>
                        <p class="text-gray-500 text-sm mt-1">Please enter your credentials to continue</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-3">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('email') border-red-500 @enderror" 
                                placeholder="Enter your email" autofocus>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" id="password" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('password') border-red-500 @enderror" 
                                placeholder="Enter your password">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between mt-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="remember" class="w-4 h-4 text-[#a52a2a] border-gray-300 rounded focus:ring-[#a52a2a]">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-[#a52a2a] hover:underline font-medium">Forgot password?</a>
                            @endif
                        </div>

                        <button type="submit" 
                            class="w-full py-3 px-4 bg-[#a52a2a] hover:bg-red-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 mt-4">
                            Log In
                        </button>
                    </form>

                    <div class="mt-5 text-center border-t border-gray-100 pt-5">
                        <p class="text-gray-600 text-sm">
                            Don't have an account? 
                            <a href="/register" class="text-[#a52a2a] font-bold hover:underline">Create one here</a>
                        </p>
                    </div>
                </div>

            </div>
        </section>
    </main>

</body>
</html>