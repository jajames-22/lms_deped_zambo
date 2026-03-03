<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DepEd Zamboanga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    
    <header style="background-color: #a52a2a;" class="fixed p-1 flex justify-center z-40 w-full items-center shadow-md">
        <img src="{{ asset('storage/images/deped_zambo_header.png') }}" class="w-full max-w-4xl h-auto block"
            alt="DepEd Zamboanga Header">
    </header>

    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center pt-20 md:pt-32 pb-12 px-4">
            
            <div class="absolute inset-0 bg-red-900/60"></div>

            <div class="relative z-10 w-full max-w-3xl mx-auto flex flex-col items-center">
                
                <div class="text-center text-white mb-6 w-full">
                    <a href="{{ url('/') }}" class="inline-flex w-full items-center text-white/80 hover:text-white mb-6 group transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="font-medium">Back to Home</span>
                    </a>
                    <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">Create Account</h1>
                    <p class="text-white/80 text-lg">Sign up for the Learning Management System</p>
                </div>

                <div class="w-full bg-white rounded-2xl shadow-2xl p-6 md:p-10 border border-gray-100">
                    <form method="POST" action="/register" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('first_name') border-red-500 @enderror" 
                                    placeholder="Ex. Juan">
                                @error('first_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name') }}" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('middle_name') border-red-500 @enderror" 
                                    placeholder="Ex. Perez">
                                @error('middle_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('last_name') border-red-500 @enderror" 
                                    placeholder="Ex. Dela Cruz">
                                @error('last_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Suffix (Optional)</label>
                                <input type="text" name="suffix" value="{{ old('suffix') }}" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('suffix') border-red-500 @enderror" 
                                    placeholder="Jr.">
                                @error('suffix') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('email') border-red-500 @enderror" 
                                placeholder="Ex. xxx@deped.gov.ph">
                            @error('email') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('password') border-red-500 @enderror" 
                                    placeholder="Password">
                                @error('password') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" name="password_confirmation" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all" 
                                    placeholder="Re-enter your Password">
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" 
                                class="w-full py-3.5 px-4 bg-[#a52a2a] hover:bg-red-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                                Register Account
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center border-t border-gray-100 pt-5">
                        <p class="text-gray-600 text-sm">
                            Already have an account? 
                            <a href="/login" class="text-[#a52a2a] font-bold hover:underline">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

</body>

</html>