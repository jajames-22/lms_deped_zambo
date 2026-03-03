<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - DepEd Zamboanga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    
    <header style="background-color: #a52a2a;" class="fixed top-0 p-2 flex justify-center z-40 w-full items-center shadow-md">
        <img src="{{ asset('storage/images/deped_zambo_header.png') }}" class="w-full max-w-4xl h-auto block"
            alt="DepEd Zamboanga Header">
    </header>

    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center pt-30 md:pt-40 pb-12 px-4">
            
            <div class="absolute inset-0 bg-red-900/50"></div>

            <div class="relative z-10 w-full max-w-md mx-auto flex flex-col items-center">
                
                <div class="text-center text-white mb-6 w-full">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">Forgot Password</h1>
                    <p class="text-white/80 text-lg">Enter your email to receive a reset link.</p>
                </div>

                <div class="w-full bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                    
                    @if (session('status'))
                        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
                            <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('email') border-red-500 @enderror" 
                                placeholder="Ex. xxx@deped.gov.ph">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                class="w-full py-3 px-4 bg-[#a52a2a] hover:bg-red-800 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                Send Reset Link
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center border-t border-gray-100 pt-5">
                        <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-[#a52a2a] font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Login
                        </a>
                    </div>
                </div>

            </div>
        </section>
    </main>

</body>
</html>