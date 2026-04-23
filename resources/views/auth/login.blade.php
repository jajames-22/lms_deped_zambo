<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DepEd Zamboanga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Ensure Cinzel Font is Loaded --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }
        .font-cinzel {
            font-family: 'Cinzel', serif;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    
    <header class="bg-[#a52a2a] text-white flex justify-center shadow-lg fixed top-0 z-50 w-full no-print">
            <div class="px-4 py-3 md:px-8 md:py-6 max-w-[1200px] w-full">
                <div class="flex flex-row sm:flex-row items-center justify-between gap-2 md:gap-6 relative">
                    
                    {{-- Mobile Menu Trigger --}}
                    <button @click="sidebarOpen = true" class="absolute left-0 top-0 lg:hidden text-white hover:text-white/80 transition p-1">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    {{-- Left Logos --}}
                    <div class="flex items-center gap-2 md:gap-4 shrink-0 md:mt-0">
                        <img src="{{ asset('images/deped.png') }}" alt="DepEd" class="h-10 sm:h-12 md:h-16 w-auto drop-shadow-md">
                        <img src="{{ asset('images/r9.png') }}" alt="Region IX" class="h-10 sm:h-12 md:h-16 w-auto drop-shadow-md">
                    </div>

                    {{-- Central Branding --}}
                    <div class="flex flex-col font-cinzel text-white items-start sm:items-start text-center sm:text-left flex-1 px-4 w-full">
                        {{-- Wrapper to constrain the width of the horizontal line to exactly the text width --}}
                        <div class="inline-flex flex-col items-start sm:items-start w-fit">
                            <span class="text-[8px] sm:text-[10px] tracking-widest leading-tight font-bold">Republic of the Philippines</span>
                            <span class="text-[8px] sm:text-[10px] tracking-widest leading-tight font-bold">Department of Education</span>
                            
                            {{-- Horizontal Line (Now restricted by the parent wrapper) --}}
                            <div class="w-full border-b border-white my-1"></div>
                            
                            <h1 class="text-sm sm:text-lg md:text-xl lg:text-2xl tracking-wide font-bold leading-tight">
                                {{ $site_settings->header_title ?? 'Zamboanga City Division' }}
                            </h1>
                        </div>
                    </div>

                    {{-- Right Logo --}}
                    <div class="block md:block shrink-0">
                        <img src="{{ asset('images/ts.png') }}" alt="Transparency Seal" class=" opacity-90 h-10 sm:h-12 md:h-16 w-auto drop-shadow-md">
                    </div>
                </div>
            </div>
        </header>

    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center pt-20 md:pt-32 pb-12 px-4">
            
            <div class="absolute inset-0 bg-red-900/50"></div>

            <div class="relative z-10 w-full max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
                
                <div class="w-full md:w-1/2 text-center md:text-left text-white">
                    <a href="{{ url('/') }}"
                        class="inline-flex w-full items-center text-white/80 hover:text-white mb-6 group transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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

                    {{-- MAIN LOGIN FORM --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-3">
                        @csrf

                        <div>
                            <label for="login_id" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                            <input type="text" name="login_id" id="login_id" value="{{ old('login_id') }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('login_id') border-red-500 @enderror" 
                                placeholder="Enter your username or email" autofocus>
                            
                            @error('login_id')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                                
                                {{-- Button linked to the external resend form using form="id" --}}
                                @if(session('unverified_email'))
                                    <button type="submit" form="resend-verification-form" class="mt-2 text-[#a52a2a] text-xs font-bold hover:underline bg-transparent border-none cursor-pointer p-0 text-left">
                                        Resend verification link
                                    </button>
                                @endif
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
                                <input type="checkbox" name="remember" 
                                    class="w-4 h-4 accent-[#a52a2a] border-gray-300 rounded focus:ring-red-700">
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
                    {{-- END MAIN LOGIN FORM --}}

                    {{-- HIDDEN RESEND VERIFICATION FORM --}}
                    @if(session('unverified_email'))
                        <form id="resend-verification-form" method="POST" action="{{ route('verification.send') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('unverified_email') }}">
                        </form>
                    @endif

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

    {{-- VERIFICATION MODAL OVERLAY --}}
    @if(session('show_verification_modal'))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
            id="verificationModal">

            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full relative text-center animate-fade-in-up">

                <div
                    class="w-20 h-20 bg-red-50 text-[#a52a2a] rounded-full flex items-center justify-center mx-auto mb-5 shadow-sm">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>

                <h2 class="text-2xl font-extrabold text-gray-900 mb-3">Verify Your Email Address</h2>

                <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                    We've sent an email to <br>
                    <strong class="text-gray-900">{{ session('registered_email') ?? session('verify_email') }}</strong>.<br>
                    Please check your inbox and click the verification link.
                </p>

                @if (session('message'))
                    <div class="p-3 mb-5 text-sm font-medium text-green-800 rounded-lg bg-green-50 border border-green-200">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="flex flex-col space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <input type="hidden" name="email"
                            value="{{ session('registered_email') ?? session('verify_email') }}">

                        <button type="submit"
                            class="w-full py-3 px-4 bg-[#a52a2a] hover:bg-red-800 text-white font-bold rounded-lg shadow-md transition-all duration-200">
                            Resend Verification Email
                        </button>
                    </form>

                    <button type="button" onclick="document.getElementById('verificationModal').remove()"
                        class="w-full py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-lg transition-all duration-200 block text-center">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

</body>
</html>