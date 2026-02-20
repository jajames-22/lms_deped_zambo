<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Document</title>
    <style>
        *{
            box-sizing: border-box;
            
        }
    </style>
    
</head>
<body>
    <header style="background-color: #a52a2a;" class="sticky top-0 p-1 flex justify-center z-40 w-full items-center">
        <img src="{{ asset('storage/images/deped_zambo_header.png') }}" 
         class="w-full max-w-5xl h-auto block" 
         alt="DepEd Zamboanga Header">
    </header>
    <main>
        <section 
            style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');" 
            class="relative bg-cover bg-center bg-no-repeat pt-20 pb-20 px-6"
        >
            <div class="absolute inset-0 bg-red-900/50"></div>
            
            <div class="relative z-10 max-w-7xl mx-auto text-center flex items-center justify-center flex-col">
                <img src="{{ asset('storage/images/hero-text.png') }}" 
                class="w-full max-w-xl h-auto block" 
                alt="DepEd Zamboanga Header">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">
                    Learning Management <br> System 
                </h1>
                <p class="text-white max-w-2xl">A centralized digital repository and learning management system (LMS) that facilitates the distribution of accessible, division-standard learning materials to ensure educational continuity across Zamboanga City.</p>
                <div class="block mt-7 g-4">
                    <button style="background-color: #a52a2a;" class="px-8 py-3 hover:bg-red-800 text-white font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Login</button>
                    <button class="px-8 py-3 bg-white hover:bg-white-100 text-red-700 font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Sign Up</button>
                </div>
            </div>
        </section>
        <section class="h-screen">

        </section>
    </main>
</body>
</html>