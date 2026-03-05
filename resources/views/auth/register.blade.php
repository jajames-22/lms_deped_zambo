<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DepEd Zamboanga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
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
                    <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight text-shadow-lg/30">
                        Create Account</h1>
                    <p class="text-white/80 text-lg">Sign up for the Learning Management System</p>
                </div>

                <div class="w-full bg-white rounded-2xl shadow-2xl p-6 md:p-10 border border-gray-100">
                    <form method="POST" action="/register" class="space-y-4" onsubmit="disableButton()">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('first_name') border-red-500 @enderror"
                                    placeholder="Ex. Juan">
                                @error('first_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name
                                    (Optional)</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('middle_name') border-red-500 @enderror"
                                    placeholder="Ex. Perez">
                                @error('middle_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}
                                </p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('last_name') border-red-500 @enderror"
                                    placeholder="Ex. Dela Cruz">
                                @error('last_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Suffix (Optional)</label>
                                <input type="text" name="suffix" value="{{ old('suffix') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('suffix') border-red-500 @enderror"
                                    placeholder="Jr.">
                                @error('suffix') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('email') border-red-500 @enderror"
                                placeholder="Ex. xxx@deped.gov.ph">
                            @error('email') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name of School
                            </label>

                            <select name="school_id" id="schoolSelect"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300">
                                <option value="">Select School</option>

                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('school_id')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Register As
                            </label>

                            <div class="flex gap-6">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="role" value="student" {{ old('role') == 'student' ? 'checked' : '' }}>
                                    Student
                                </label>

                                <label class="flex items-center gap-2">
                                    <input type="radio" name="role" value="teacher" {{ old('role') == 'teacher' ? 'checked' : '' }}>
                                    Teacher
                                </label>
                            </div>

                            @error('role')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- LRN / Teacher ID -->
                        <div id="userIdWrapper" style="display:none;">
                            <label id="userIdLabel" class="block text-sm font-medium text-gray-700 mb-1"></label>

                            <input type="text" name="user_id" value="{{ old('user_id') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('user_id') border-red-500 @enderror"
                                placeholder="">

                            @error('user_id')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="gradeLevelWrapper">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Grade Level
                            </label>

                            <select name="grade_level" class="w-full px-4 py-2 rounded-lg border border-gray-300">

                                <option value="">Select Grade</option>

                                <option value="Kinder" {{ old('grade_level') == 'Kinder' ? 'selected' : '' }}>
                                    Kinder
                                </option>

                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="Grade {{ $i }}" {{ old('grade_level') == "Grade $i" ? 'selected' : '' }}>
                                        Grade {{ $i }}
                                    </option>
                                @endfor

                            </select>

                            @error('grade_level')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="password"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all @error('password') border-red-500 @enderror"
                                    placeholder="Password">
                                @error('password') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" name="password_confirmation"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition-all"
                                    placeholder="Re-enter your Password">
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" id="registerBtn"
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
                    Thanks for registering! We've sent an email to <br>
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

                    <a href="{{ route('login') }}"
                        class="w-full py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-lg transition-all duration-200 block text-center">
                        Go to Login
                    </a>
                </div>
            </div>
        </div>
    @endif

    <script>
        function disableButton() {
            let btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.innerText = 'Registering...';
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Tom Select (searchable school dropdown)
            new TomSelect("#schoolSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            const roleRadios = document.querySelectorAll('input[name="role"]');
            const gradeWrapper = document.getElementById('gradeLevelWrapper');

            function toggleGradeLevel() {
                const selectedRole = document.querySelector('input[name="role"]:checked');

                if (!selectedRole || selectedRole.value === "teacher") {
                    gradeWrapper.style.display = "none";
                } else {
                    gradeWrapper.style.display = "block";
                }
            }

            toggleGradeLevel();

            roleRadios.forEach(radio => {
                radio.addEventListener("change", toggleGradeLevel);
            });

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const roleRadios = document.querySelectorAll('input[name="role"]');
            const gradeWrapper = document.getElementById('gradeLevelWrapper');
            const userIdWrapper = document.getElementById('userIdWrapper');
            const userIdLabel = document.getElementById('userIdLabel');
            const userIdInput = document.querySelector('input[name="user_id"]');

            function toggleFields() {
                const selectedRole = document.querySelector('input[name="role"]:checked');

                if (!selectedRole) {
                    gradeWrapper.style.display = "none";
                    userIdWrapper.style.display = "none";
                    return;
                }

                if (selectedRole.value === "student") {
                    gradeWrapper.style.display = "block";
                    userIdWrapper.style.display = "block";
                    userIdLabel.innerText = "LRN";
                    userIdInput.placeholder = "Enter LRN";
                }

                if (selectedRole.value === "teacher") {
                    gradeWrapper.style.display = "none";
                    userIdWrapper.style.display = "block";
                    userIdLabel.innerText = "Teacher's ID";
                    userIdInput.placeholder = "Enter Teacher's ID";
                }
            }

            roleRadios.forEach(radio => {
                radio.addEventListener('change', toggleFields);
            });

            toggleFields(); // run on page load (important for old())
        });
    </script>
</body>

</html>