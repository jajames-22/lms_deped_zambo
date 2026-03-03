<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DepEd Zamboanga</title>
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

            <div class="relative z-10 w-full max-w-lg mx-auto flex flex-col items-center">
                
                <div class="text-center text-white mb-6 w-full">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">Reset Password</h1>
                    <p class="text-white/80 text-lg">Please enter your new password below.</p>
                </div>

                <div class="w-full bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ $email }}" required readonly
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-500 focus:outline-none cursor-not-allowed">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" id="password" required autofocus
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('password') border-red-500 @enderror" 
                                >
                            @error('password')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('password_confirmation') border-red-500 @enderror" >
                            @error('password_confirmation')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                class="w-full py-3 px-4 bg-[#a52a2a] hover:bg-red-800 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </section>
    </main>

</body>
</html>