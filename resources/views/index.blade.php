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
    <header class="bg-red-700 p-1 flex justify-center items-center">
        <img src="{{ asset('storage/images/deped_zambo_header.png') }}" 
         class="w-full max-w-5xl h-auto block" 
         alt="DepEd Zamboanga Header">
    </header>
    <main>
        <section 
            style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');" 
            class="relative bg-cover bg-center bg-no-repeat pt-32 pb-20 px-6"
        >
            <div class="absolute inset-0 bg-red-900 bg-opacity-10"></div>

            <div class="relative z-10 max-w-7xl mx-auto text-center">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight drop-shadow-2xl">
                    Learning Management <br> System 
                </h1>
            </div>
        </section>
    </main>
</body>
</html>