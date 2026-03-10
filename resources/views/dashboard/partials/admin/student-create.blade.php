<div class="max-w-4xl mx-auto space-y-6 pb-10 relative">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Register Student</h1>
            <p class="text-sm text-gray-500">Create a new learner account in the database.</p>
        </div>
        <button type="button" onclick="loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'))"
            class="flex items-center gap-2 px-4 py-2 text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-all">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Directory</span>
        </button>
    </div>

    <form action="{{ route('students.store') }}" method="POST" id="createStudentForm" class="space-y-6">
        @csrf

        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-8">
            
            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required placeholder="e.g. Maria"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required placeholder="e.g. Santos"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="md:col-span-1">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Suffix</label>
                        <input type="text" name="suffix" placeholder="e.g. Jr., II"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Account Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Learner Reference Number (LRN) <span class="text-red-500">*</span></label>
                        <input type="text" name="user_id" required placeholder="12-digit LRN"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-green-500 focus:bg-white rounded-xl transition-all outline-none font-mono text-green-700">
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required placeholder="student@deped.gov.ph"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                    
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Temporary Password <span class="text-red-500">*</span></label>
                        <div class="relative w-full">
                            <input type="password" name="password" id="passwordInput" required placeholder="Enter default password"
                                class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">They will use this password to log in.</p>
                    </div>

                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Account Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all">
                            <option value="pending">Pending</option>
                            <option value="verified" selected>Verified</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">Verified accounts can access student portals.</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Academic Profile</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Enrolled School <span class="text-red-500">*</span></label>
                        <select name="school_id" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all">
                            <option value="" disabled selected>Select an institution...</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}">
                                    {{ $school->name }} (ID: {{ $school->school_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Grade Level <span class="text-red-500">*</span></label>
                        <select name="grade_level" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all">
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

        </div>

        <button type="submit" id="submitBtn"
            class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-2xl shadow-xl shadow-red-900/20 hover:bg-red-800 transition-all flex items-center justify-center gap-3 disabled:opacity-75 disabled:cursor-not-allowed">
            <i class="fas fa-user-graduate"></i> <span>Register Student</span>
        </button>
    </form>

    <div id="successModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Success!</h3>
            <p class="text-gray-500 mb-8 text-sm">The student's account has been successfully created.</p>
            <div class="space-y-3">
                <button type="button" onclick="closeSuccessModal()" class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                    Register Another Student
                </button>
                <button type="button" onclick="loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'))" 
                    class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition">
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

    // --- AJAX Form Submission Logic ---
    var studentForm = document.getElementById('createStudentForm');
    var studentSubmitBtn = document.getElementById('submitBtn');
    
    if (studentForm && studentSubmitBtn) {
        var newStudentForm = studentForm.cloneNode(true);
        studentForm.parentNode.replaceChild(newStudentForm, studentForm);

        newStudentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var currentSubmitBtn = document.getElementById('submitBtn');
            var submitIcon = currentSubmitBtn.querySelector('i');
            var submitText = currentSubmitBtn.querySelector('span');

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
                document.getElementById('successModal').classList.remove('hidden');
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
                submitIcon.className = 'fas fa-user-graduate';
                submitText.textContent = 'Register Student';
            });
        });
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }
</script>