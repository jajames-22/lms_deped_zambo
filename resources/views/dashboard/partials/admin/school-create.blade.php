<div class="max-w-4xl mx-auto space-y-6 pb-10 relative">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Register Institution</h1>
            <p class="text-sm text-gray-500">Add a new school to the Zamboanga Division database.</p>
        </div>
        <button type="button" onclick="loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'))"
            class="flex items-center gap-2 px-4 py-2 text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-all">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Directory</span>
        </button>
    </div>

    <form action="{{ route('schools.store') }}" method="POST" enctype="multipart/form-data" id="createSchoolForm" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm text-center">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4">School Logo</label>
                    <div class="relative group mx-auto w-32 h-32 mb-4">
                        <div id="logo-preview" class="w-full h-full rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden">
                            <i class="fas fa-university text-3xl text-gray-300"></i>
                        </div>
                        <label for="logo" class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl cursor-pointer">
                            <i class="fas fa-camera text-white"></i>
                        </label>
                    </div>
                    <input type="file" name="logo" id="logo" class="hidden" accept="image/*" onchange="previewImage(event)">
                    <p class="text-[10px] text-gray-400">PNG or JPG. Max 2MB.</p>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Official Name</label>
                        <input type="text" name="name" required placeholder="Enter full school name..."
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Level</label>
                            <select name="level" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                <option value="elementary">Elementary</option>
                                <option value="High School">High School</option>
                                <option value="Senior High School">Senior High</option>
                                <option value="integrated">Integrated</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Address</label>
                            <input type="text" name="address" placeholder="Barangay/Street"
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-50">
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Quadrant</label>
                            <select name="quadrant_id" id="quadrant_id" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                <option value="" disabled selected>Select...</option>
                                @foreach ($quadrants as $quadrant)
                                    <option value="{{ $quadrant->id }}">{{ $quadrant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">District</label>
                            <select name="district_id" id="district_id" required disabled
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none disabled:opacity-50">
                                <option value="">Select Quadrant first</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" id="submitBtn"
                    class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-2xl shadow-xl shadow-red-900/20 hover:bg-red-800 transition-all flex items-center justify-center gap-3 disabled:opacity-75 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle"></i> <span>Complete Registration</span>
                </button>
            </div>
        </div>
    </form>

    <div id="successModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Success!</h3>
            <p class="text-gray-500 mb-8 text-sm">The school has been successfully registered to the database.</p>
            <div class="space-y-3">
                <button type="button" onclick="closeSuccessModal()" class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                    Add Another School
                </button>
                <button type="button" onclick="loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'))" 
                    class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition">
                    Return to Directory
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    console.log("School Create Script Loaded");

    // 1. Image Preview
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('logo-preview');
            output.innerHTML = `<img src="${reader.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // 2. AJAX District Loading
    const qSelect = document.getElementById('quadrant_id');
    const dSelect = document.getElementById('district_id');

    if (qSelect) {
        qSelect.addEventListener('change', function() {
            const qId = this.value;
            dSelect.disabled = true;
            dSelect.innerHTML = '<option>Loading districts...</option>';

            fetch(`/get-districts/${qId}`)
                .then(res => res.json())
                .then(data => {
                    dSelect.innerHTML = '<option value="" disabled selected>Select District</option>';
                    if (data.length > 0) {
                        data.forEach(d => {
                            const option = document.createElement('option');
                            option.value = d.id;
                            option.textContent = d.name;
                            dSelect.appendChild(option);
                        });
                        dSelect.disabled = false;
                    } else {
                        dSelect.innerHTML = '<option>No districts found</option>';
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        });
    }

    // 3. AJAX Form Submission
    const form = document.getElementById('createSchoolForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitIcon = submitBtn.querySelector('i');
    const submitText = submitBtn.querySelector('span');

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Stop page reload

        // Loading State
        submitBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = 'Saving to database...';

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' // Forces Laravel to return JSON errors instead of redirecting
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            // Show Success Modal
            document.getElementById('successModal').classList.remove('hidden');
            
            // Reset the form in the background
            form.reset();
            document.getElementById('logo-preview').innerHTML = '<i class="fas fa-university text-3xl text-gray-300"></i>';
            dSelect.disabled = true;
            dSelect.innerHTML = '<option value="">Select Quadrant first</option>';
        })
        .catch(error => {
            console.error("Submission error:", error);
            // Quick error handling for validation
            let errorMsg = "An error occurred while saving.";
            if(error.errors) {
                errorMsg = Object.values(error.errors).flat().join('\n');
            }
            alert(errorMsg);
        })
        .finally(() => {
            // Revert Loading State
            submitBtn.disabled = false;
            submitIcon.className = 'fas fa-check-circle';
            submitText.textContent = 'Complete Registration';
        });
    });

    // 4. Close Modal Logic
    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }
</script>