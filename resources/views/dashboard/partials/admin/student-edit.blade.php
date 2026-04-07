<div class="max-w-5xl mx-auto space-y-6 pb-10 relative animate-float-in">
    
    <div class="flex items-center gap-4">
        <button type="button" onclick="loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'))"
            class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-[#a52a2a] hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center shadow-sm shrink-0">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Student</h1>
            <p class="text-sm text-gray-500">Update details for {{ $student->first_name }} {{ $student->last_name }}.</p>
        </div>
    </div>

    <form action="{{ route('students.update', $student->id) }}" method="POST" id="editStudentForm" class="space-y-6">
        @csrf
        @method('PUT')

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
                    <input type="text" name="first_name" required value="{{ $student->first_name }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ $student->middle_name }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" required value="{{ $student->last_name }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                <div class="md:col-span-1 space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Suffix</label>
                    <input type="text" name="suffix" value="{{ $student->suffix }}" placeholder="e.g. Jr., II"
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required value="{{ $student->username }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">LRN <span class="text-red-500">*</span></label>
                    <input type="text" name="lrn" required value="{{ $student->lrn }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all text-sm font-mono text-green-700">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Email Address <span class="text-gray-400 font-normal normal-case tracking-normal">(Optional)</span></label>
                    <input type="email" name="email" value="{{ $student->email }}" placeholder="student@deped.gov.ph"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Reset Password</label>
                    <div class="relative w-full">
                        <input type="password" name="password" id="passwordInput" placeholder="Leave blank to keep current"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm pr-10">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-gray-600 uppercase">Account Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        <option value="pending" {{ $student->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ $student->status == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="suspended" {{ $student->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
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
                        <option value="" disabled>Select an institution...</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ $student->school_id == $school->id ? 'selected' : '' }}>
                                {{ $school->name }} (ID: {{ $school->school_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Grade Level <span class="text-red-500">*</span></label>
                    <select name="grade_level" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        <option value="" disabled>Select grade level...</option>
                        <optgroup label="Primary">
                            <option value="Kindergarten" {{ $student->grade_level == 'Kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                            <option value="Grade 1" {{ $student->grade_level == 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                            <option value="Grade 2" {{ $student->grade_level == 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                            <option value="Grade 3" {{ $student->grade_level == 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                            <option value="Grade 4" {{ $student->grade_level == 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                            <option value="Grade 5" {{ $student->grade_level == 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                            <option value="Grade 6" {{ $student->grade_level == 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                        </optgroup>
                        <optgroup label="Junior High">
                            <option value="Grade 7" {{ $student->grade_level == 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                            <option value="Grade 8" {{ $student->grade_level == 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                            <option value="Grade 9" {{ $student->grade_level == 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                            <option value="Grade 10" {{ $student->grade_level == 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                        </optgroup>
                        <optgroup label="Senior High">
                            <option value="Grade 11" {{ $student->grade_level == 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                            <option value="Grade 12" {{ $student->grade_level == 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" id="submitBtn"
                class="px-8 py-3.5 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-gray-800 transition-all flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                <i class="fas fa-save" id="submitIcon"></i> <span id="submitText">Save Changes</span>
            </button>
        </div>
    </form>

    <div id="successModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Updated!</h3>
            <p class="text-gray-500 mb-8 text-sm">The student's details have been successfully updated.</p>
            <button type="button" onclick="loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'))" 
                class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition">
                Return to Directory
            </button>
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
    var studentEditForm = document.getElementById('editStudentForm');
    var studentSubmitBtn = document.getElementById('submitBtn');
    
    if (studentEditForm && studentSubmitBtn) {
        var newStudentEditForm = studentEditForm.cloneNode(true);
        studentEditForm.parentNode.replaceChild(newStudentEditForm, studentEditForm);

        newStudentEditForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var currentSubmitBtn = document.getElementById('submitBtn');
            var submitIcon = document.getElementById('submitIcon');
            var submitText = document.getElementById('submitText');

            currentSubmitBtn.disabled = true;
            submitIcon.className = 'fas fa-spinner fa-spin';
            submitText.textContent = 'Updating database...';

            var formData = new FormData(this);
            formData.append('_method', 'PUT'); 

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
                setTimeout(() => {
                    loadPartial('{{ route('dashboard.students') }}', document.getElementById('nav-students-btn'));
                }, 2000);
            })
            .catch(error => {
                console.error("Submission error:", error);
                var errorMsg = "An error occurred while updating.";
                if(error.errors) {
                    errorMsg = Object.values(error.errors).flat().join('\n');
                }
                alert(errorMsg);
            })
            .finally(() => {
                currentSubmitBtn.disabled = false;
                submitIcon.className = 'fas fa-save';
                submitText.textContent = 'Save Changes';
            });
        });
    }
</script>