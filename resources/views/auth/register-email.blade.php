<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - DepEd Zamboanga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <main>
        <section style="background-image: url('{{ asset('storage/images/deped_zamdiv.jpg') }}');"
            class="relative bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-red-900/60"></div>
            <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Email Required</h2>
                    <p class="text-gray-500 text-sm mt-1">Hello <b>{{ auth()->user()->username }}</b>, please provide an email address to secure your account and receive notifications.</p>
                </div>

                <form method="POST" action="{{ route('register-email') }}" class="space-y-4" onsubmit="handleFormSubmit(this)">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" id="emailInput" required 
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all" 
                            placeholder="Ex. yourname@deped.gov.ph">
                        @error('email') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full py-3 px-4 bg-[#a52a2a] hover:bg-red-800 text-white font-semibold rounded-lg shadow-md transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span id="btnText">Continue & Verify Email</span>
                    </button>
                </form>
            </div>
        </section>
    </main>

    <script>
        function handleFormSubmit(form) {
            const emailInput = document.getElementById('emailInput');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');

            // Browser validation handles the "empty" check via 'required' attribute,
            // but we check here to ensure the UI only changes if it's actually submitting.
            if (emailInput.value.trim() !== "") {
                submitBtn.disabled = true;
                btnText.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Processing...';
                return true;
            }
            return false;
        }
    </script>
</body>
</html>