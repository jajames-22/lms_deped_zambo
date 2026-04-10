<div>
    <style>
        /* Ensures the icon background and color change properly when the hidden radio is checked */
        input[value="pending"]:checked + div .status-icon { background-color: #f59e0b !important; color: white !important; }
        input[value="verified"]:checked + div .status-icon { background-color: #10b981 !important; color: white !important; }
        input[value="suspended"]:checked + div .status-icon { background-color: #ef4444 !important; color: white !important; }
    </style>

    <div class="max-w-5xl mx-auto space-y-6 pb-10 relative animate-float-in">
        
        <div class="flex items-center gap-4 mb-3">
            <button type="button" onclick="loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'))"
                class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-[#a52a2a] hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center shadow-sm shrink-0">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Teacher</h1>
                <p class="text-sm text-gray-500">Update details for {{ $teacher->first_name }} {{ $teacher->last_name }}.</p>
            </div>
        </div>

        <form action="{{ route('teachers.update', $teacher->id) }}" method="POST" id="editTeacherForm" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. PERSONAL INFO --}}
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-red-50 text-[#a52a2a] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Personal Information</h2>
                        <p class="text-xs text-gray-500">Teacher's legal name details.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required value="{{ $teacher->first_name }}"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ $teacher->middle_name }}" placeholder="Optional"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required value="{{ $teacher->last_name }}"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div class="md:col-span-1 space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Suffix</label>
                        <input type="text" name="suffix" value="{{ $teacher->suffix }}" placeholder="e.g. Jr., II"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                </div>
            </div>

            {{-- 2. ACCOUNT SECURITY & STATUS --}}
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Account Security & Status</h2>
                        <p class="text-xs text-gray-500">Login IDs and system access.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required value="{{ $teacher->username }}"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Email Address</label>
                        <input type="email" name="email" value="{{ $teacher->email }}"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>
                    
                    <div class="space-y-1.5 md:col-span-2 max-w-md">
                        <label class="text-xs font-bold text-gray-600 uppercase">Reset Password</label>
                        <div class="relative w-full">
                            <input type="password" name="password" id="passwordInput" placeholder="Leave blank to keep current"
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
                            <input type="radio" name="status" value="pending" class="peer sr-only" {{ $teacher->status == 'pending' ? 'checked' : '' }}>
                            <div class="p-4 border-2 border-gray-100 rounded-xl bg-white transition-all duration-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:shadow-sm hover:border-gray-200 flex items-center gap-3">
                                <div class="status-icon w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 transition-colors duration-300 shrink-0">
                                    <i class="fas fa-clock text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Pending</p>
                                    <p class="text-[10px] text-gray-500 leading-tight">Requires admin review with limited controls</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="verified" class="peer sr-only" {{ $teacher->status == 'verified' ? 'checked' : '' }}>
                            <div class="p-4 border-2 border-gray-100 rounded-xl bg-white transition-all duration-300 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-sm hover:border-gray-200 flex items-center gap-3">
                                <div class="status-icon w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 transition-colors duration-300 shrink-0">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Verified</p>
                                    <p class="text-[10px] text-gray-500 leading-tight">Active controls</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="suspended" class="peer sr-only" {{ $teacher->status == 'suspended' ? 'checked' : '' }}>
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

            {{-- 3. EMPLOYMENT PROFILE --}}
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Employment Profile</h2>
                        <p class="text-xs text-gray-500">Employee ID and school assignment.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Employee ID <span class="text-red-500">*</span></label>
                        <input type="text" name="employee_id" required value="{{ $teacher->employee_id }}" placeholder="e.g. 1234567"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all text-sm font-mono text-purple-700">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Assigned School <span class="text-red-500">*</span></label>
                        <select name="school_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" {{ $teacher->school_id == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }} (ID: {{ $school->school_id }})
                                </option>
                            @endforeach
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
    </div>

    <div id="successModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60 transition-opacity duration-300"></div>
        <div id="successModalBox" class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Updated!</h3>
            <p class="text-gray-500 mb-8 text-sm">The educator's details have been successfully updated.</p>
            <button type="button" onclick="loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'))" 
                class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition">
                Return to Directory
            </button>
        </div>
    </div>
</div>

<script>
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

    function closeSuccessModal() {
        var modal = document.getElementById('successModal');
        var box = document.getElementById('successModalBox');

        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    var teacherEditForm = document.getElementById('editTeacherForm');
    var teacherSubmitBtn = document.getElementById('submitBtn');
    
    if (teacherEditForm && teacherSubmitBtn) {
        var newTeacherEditForm = teacherEditForm.cloneNode(true);
        teacherEditForm.parentNode.replaceChild(newTeacherEditForm, teacherEditForm);

        newTeacherEditForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var currentSubmitBtn = document.getElementById('submitBtn');
            var submitIcon = document.getElementById('submitIcon');
            var submitText = document.getElementById('submitText');

            currentSubmitBtn.disabled = true;
            submitIcon.className = 'fas fa-spinner fa-spin';
            submitText.textContent = 'Updating account...';

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
                var modal = document.getElementById('successModal');
                var box = document.getElementById('successModalBox');

                modal.classList.remove('hidden');
                
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);
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