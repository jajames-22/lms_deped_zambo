<div>
    <style>
        /* Ensures the icon background and color change properly when the hidden radio is checked */
        input[value="pending"]:checked + div .status-icon { background-color: #f59e0b !important; color: white !important; }
        input[value="verified"]:checked + div .status-icon { background-color: #10b981 !important; color: white !important; }
        input[value="suspended"]:checked + div .status-icon { background-color: #ef4444 !important; color: white !important; }
    </style>

    <div class="max-w-5xl mx-auto space-y-6 pb-10 relative animate-float-in">
        
        <div class="flex items-center gap-4 mb-3">
            <button type="button" onclick="loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'))"
                class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-[#a52a2a] hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center shadow-sm shrink-0">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Register Student</h1>
                <p class="text-sm text-gray-500">Create a new learner account in the database.</p>
            </div>
        </div>

        <form action="{{ route('students.store') }}" method="POST" id="createStudentForm" class="space-y-6">
            @csrf

            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-red-50 text-[#a52a2a] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Personal Information</h2>
                        <p class="text-xs text-gray-500">Student's legal name details.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required placeholder="e.g. Maria"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required placeholder="e.g. Santos"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div class="md:col-span-1 space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Suffix</label>
                        <input type="text" name="suffix" placeholder="e.g. Jr., II"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Account Credentials</h2>
                        <p class="text-xs text-gray-500">Login IDs and system access.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required placeholder="e.g. maria_santos"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">LRN <span class="text-red-500">*</span></label>
                        <input type="text" name="lrn" required placeholder="12-digit Learner Reference Number"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all text-sm font-mono text-green-700">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Email Address <span class="text-gray-400 font-normal normal-case tracking-normal">(Optional)</span></label>
                        <input type="email" name="email" placeholder="student@deped.gov.ph"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Temporary Password <span class="text-red-500">*</span></label>
                        <div class="relative w-full">
                            <input type="password" name="password" id="passwordInput" required placeholder="Enter default password"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm pr-10">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-gray-100">
                    <label class="text-xs font-bold text-gray-600 uppercase">Account Status <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                        
                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="pending" class="peer sr-only">
                            <div class="p-4 border-2 border-gray-100 rounded-xl bg-white transition-all duration-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:shadow-sm hover:border-gray-200 flex items-center gap-3">
                                <div class="status-icon w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 transition-colors duration-300 shrink-0">
                                    <i class="fas fa-clock text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Pending</p>
                                    <p class="text-[10px] text-gray-500 leading-tight">Requires admin review</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="verified" class="peer sr-only" checked>
                            <div class="p-4 border-2 border-gray-100 rounded-xl bg-white transition-all duration-300 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-sm hover:border-gray-200 flex items-center gap-3">
                                <div class="status-icon w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 transition-colors duration-300 shrink-0">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Verified</p>
                                    <p class="text-[10px] text-gray-500 leading-tight">Active dashboard access</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="suspended" class="peer sr-only">
                            <div class="p-4 border-2 border-gray-100 rounded-xl bg-white transition-all duration-300 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:shadow-sm hover:border-gray-200 flex items-center gap-3">
                                <div class="status-icon w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 transition-colors duration-300 shrink-0">
                                    <i class="fas fa-ban text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Suspended</p>
                                    <p class="text-[10px] text-gray-500 leading-tight">Account is blocked</p>
                                </div>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Academic Profile</h2>
                        <p class="text-xs text-gray-500">School and grade level assignments.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Enrolled School <span class="text-red-500">*</span></label>
                        <select name="school_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                            <option value="" disabled selected>Select an institution...</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}">
                                    {{ $school->name }} (ID: {{ $school->school_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Grade Level <span class="text-red-500">*</span></label>
                        <select name="grade_level" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                            <option value="" disabled selected>Select grade level...</option>
                            <optgroup label="Primary">
                                <option value="Kindergarten">Kindergarten</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                            </optgroup>
                            <optgroup label="Junior High">
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                            </optgroup>
                            <optgroup label="Senior High">
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" id="submitBtn"
                    class="px-8 py-3.5 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-gray-800 transition-all flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                    <i class="fas fa-user-plus" id="submitIcon"></i> <span id="submitText">Register Student</span>
                </button>
            </div>
        </form>
    </div>

    <div id="successModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60 transition-opacity duration-300"></div>
        <div id="successModalBox" class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Success!</h3>
            <p class="text-gray-500 mb-8 text-sm">The student's account has been successfully created.</p>
            <div class="space-y-3">
                <button type="button" onclick="closeSuccessModal()" class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                    Register Another
                </button>
                <button type="button" onclick="loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'))" 
                    class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition">
                    Return to Directory
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Password Toggle Logic ---
    function togglePassword() {
        var pwdInput = document.getElementById('passwordInput');
        var eyeIcon = document.getElementById('eyeIcon');
        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            pwdInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // --- Modal Closing Animation ---
    function closeSuccessModal() {
        var modal = document.getElementById('successModal');
        var box = document.getElementById('successModalBox');

        // Trigger zoom-out fade animation
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Matches the duration-300 class
    }

    // --- AJAX Form Submission Logic ---
    var studentForm = document.getElementById('createStudentForm');
    var studentSubmitBtn = document.getElementById('submitBtn');
    
    if (studentForm && studentSubmitBtn) {
        var newStudentForm = studentForm.cloneNode(true);
        studentForm.parentNode.replaceChild(newStudentForm, studentForm);

        newStudentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var currentSubmitBtn = document.getElementById('submitBtn');
            var submitIcon = document.getElementById('submitIcon');
            var submitText = document.getElementById('submitText');

            currentSubmitBtn.disabled = true;
            submitIcon.className = 'fas fa-spinner fa-spin';
            submitText.textContent = 'Saving account...';

            var formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => { throw err; });
                return response.json();
            })
            .then(data => {
                var modal = document.getElementById('successModal');
                var box = document.getElementById('successModalBox');

                // Remove hidden class first
                modal.classList.remove('hidden');
                
                // Add a small delay so the browser registers the display:block before animating
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);

                document.getElementById('createStudentForm').reset(); // Clear form
            })
            .catch(error => {
                console.error("Submission error:", error);
                var errorMsg = "An error occurred while saving.";
                if(error.errors) {
                    errorMsg = Object.values(error.errors).flat().join('\n');
                }
                alert(errorMsg);
            })
            .finally(() => {
                currentSubmitBtn.disabled = false;
                submitIcon.className = 'fas fa-user-plus';
                submitText.textContent = 'Register Student';
            });
        });
    }
</script>