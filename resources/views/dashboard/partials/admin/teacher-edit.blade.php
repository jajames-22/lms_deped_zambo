<div class="max-w-4xl mx-auto space-y-6 pb-10 relative">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <button type="button" onclick="loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'))"
                class="flex items-center gap-2 px-4 py-2 text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-all self-stretch">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="ml-3">
                <h1 class="text-2xl font-bold text-gray-900">Edit Teacher</h1>
                <p class="text-sm text-gray-500">Update details for {{ $teacher->first_name }} {{ $teacher->last_name }}.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST" id="editTeacherForm" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-8">
            
            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required value="{{ $teacher->first_name }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ $teacher->middle_name }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required value="{{ $teacher->last_name }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="md:col-span-1">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Suffix</label>
                        <input type="text" name="suffix" value="{{ $teacher->suffix }}" placeholder="e.g. Jr., Sr., III"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Account Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Employee ID (User ID) <span class="text-red-500">*</span></label>
                        <input type="text" name="user_id" required value="{{ $teacher->user_id }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-blue-500 focus:bg-white rounded-xl transition-all outline-none font-mono text-blue-700">
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required value="{{ $teacher->email }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>
                    
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Reset Password</label>
                        <div class="relative w-full">
                            <input type="password" name="password" id="passwordInput" placeholder="Leave blank to keep current password"
                                class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Account Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all">
                            <option value="pending" {{ $teacher->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ $teacher->status == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="suspended" {{ $teacher->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">Suspended accounts will not be able to log in.</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">School Assignment</h3>
                <div class="w-full md:max-w-md">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Assign to School <span class="text-red-500">*</span></label>
                    <select name="school_id" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                        <option value="" disabled>Select an institution...</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ $teacher->school_id == $school->id ? 'selected' : '' }}>
                                {{ $school->name }} (ID: {{ $school->school_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        <button type="submit" id="submitBtn"
            class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-2xl shadow-xl shadow-red-900/20 hover:bg-red-800 transition-all flex items-center justify-center gap-3 disabled:opacity-75 disabled:cursor-not-allowed">
            <i class="fas fa-save"></i> <span>Save Changes</span>
        </button>
    </form>

    <div id="successModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Updated!</h3>
            <p class="text-gray-500 mb-8 text-sm">The teacher's details have been successfully updated.</p>
            <button type="button" onclick="loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'))" 
                class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition">
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
    var teacherEditForm = document.getElementById('editTeacherForm');
    var teacherSubmitBtn = document.getElementById('submitBtn');
    
    if (teacherEditForm && teacherSubmitBtn) {
        var newTeacherEditForm = teacherEditForm.cloneNode(true);
        teacherEditForm.parentNode.replaceChild(newTeacherEditForm, teacherEditForm);

        newTeacherEditForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var currentSubmitBtn = document.getElementById('submitBtn');
            var submitIcon = currentSubmitBtn.querySelector('i');
            var submitText = currentSubmitBtn.querySelector('span');

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
                    loadPartial('{{ route('dashboard.teachers') }}', document.getElementById('nav-teachers-btn'));
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