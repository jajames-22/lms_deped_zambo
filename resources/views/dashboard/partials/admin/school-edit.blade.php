<div class="w-full mx-auto space-y-6 pb-10 relative h-auto">
    <div class="flex items-center">
        <button type="button" onclick="loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'))"
            class="flex items-center gap-2 px-4 py-2 text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-all self-stretch">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="ml-3">
            <h1 class="text-2xl font-bold text-gray-900">Edit Institution</h1>
            <p class="text-sm text-gray-500">Update details for {{ $school->name }}.</p>
        </div>
    </div>

    <form action="{{ route('schools.update', $school->id) }}" method="POST" enctype="multipart/form-data" id="editSchoolForm" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm text-center">
                    <label class="block text-xs text-gray-400 uppercase tracking-widest mb-4">School Logo</label>
                    <div class="relative group mx-auto w-32 h-32 mb-4">
                        <div id="logo-preview" class="w-full h-full rounded-full bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden">
                            @if($school->logo)
                                <img src="{{ asset('storage/' . $school->logo) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-university text-3xl text-gray-300"></i>
                            @endif
                        </div>
                        <label for="logo" class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-full cursor-pointer">
                            <i class="fas fa-camera text-white"></i>
                        </label>
                    </div>
                    <input type="file" name="logo" id="logo" class="hidden" accept="image/*" onchange="previewImage(event)">
                    <p class="text-[10px] text-gray-400">PNG or JPG. Max 2MB. Leave blank to keep current logo.</p>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <label class="text-xs text-gray-400 uppercase tracking-widest block mb-2">School ID</label>
                            <input type="text" name="school_id" required value="{{ $school->school_id }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none font-mono text-blue-700">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-400 uppercase tracking-widest block mb-2">Official Name</label>
                            <input type="text" name="name" required value="{{ $school->name }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-widest block mb-2">Level</label>
                            <select name="level" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                <option value="elementary" {{ $school->level == 'elementary' ? 'selected' : '' }}>Elementary</option>
                                <option value="highschool" {{ $school->level == 'highschool' ? 'selected' : '' }}>High School</option>
                                <option value="seniorhighschool" {{ $school->level == 'seniorhighschool' ? 'selected' : '' }}>Senior High</option>
                                <option value="integrated" {{ $school->level == 'integrated' ? 'selected' : '' }}>Integrated</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-widest block mb-2">Address</label>
                            <input type="text" name="address" value="{{ $school->address }}"
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-50">
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-widest block mb-2">Quadrant</label>
                            <select name="quadrant_id" id="quadrant_id" required class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                <option value="" disabled>Select...</option>
                                @foreach ($quadrants as $quadrant)
                                    <option value="{{ $quadrant->id }}" {{ ($school->district->quadrant_id ?? '') == $quadrant->id ? 'selected' : '' }}>
                                        {{ $quadrant->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-widest block mb-2">District</label>
                            <select name="district_id" id="district_id" required
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}" {{ $school->district_id == $district->id ? 'selected' : '' }}>
                                        {{ $district->name }}
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
            </div>
        </div>
    </form>

    <div id="successModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Updated!</h3>
            <p class="text-gray-500 mb-8 text-sm">School details have been successfully updated.</p>
            <button type="button" onclick="loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'))" 
                class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition">
                Return to Directory
            </button>
        </div>
    </div>
</div>



<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('logo-preview');
            output.innerHTML = `<img src="${reader.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Changed 'const' to 'var' to prevent redeclaration crashes on partial reloads
    var qSelect = document.getElementById('quadrant_id');
    var dSelect = document.getElementById('district_id');

    if (qSelect) {
        // Remove old listener if it exists to prevent duplicates, then add new one
        var newQSelect = qSelect.cloneNode(true);
        qSelect.parentNode.replaceChild(newQSelect, qSelect);
        
        newQSelect.addEventListener('change', function() {
            var qId = this.value;
            var dSelectCurrent = document.getElementById('district_id');
            dSelectCurrent.disabled = true;
            dSelectCurrent.innerHTML = '<option>Loading districts...</option>';

            fetch(`/get-districts/${qId}`)
                .then(res => res.json())
                .then(data => {
                    dSelectCurrent.innerHTML = '<option value="" disabled selected>Select District</option>';
                    if (data.length > 0) {
                        data.forEach(d => {
                            var option = document.createElement('option');
                            option.value = d.id;
                            option.textContent = d.name;
                            dSelectCurrent.appendChild(option);
                        });
                        dSelectCurrent.disabled = false;
                    } else {
                        dSelectCurrent.innerHTML = '<option>No districts found</option>';
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        });
    }

    // Changed 'const' to 'var' here as well
    var editForm = document.getElementById('editSchoolForm');
    var submitBtn = document.getElementById('submitBtn');
    
    if (editForm && submitBtn) {
        var submitIcon = submitBtn.querySelector('i');
        var submitText = submitBtn.querySelector('span');

        // We clone and replace the form to strip away old event listeners from previous loads
        var newEditForm = editForm.cloneNode(true);
        editForm.parentNode.replaceChild(newEditForm, editForm);

        newEditForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var currentSubmitBtn = document.getElementById('submitBtn');
            var currentSubmitIcon = currentSubmitBtn.querySelector('i');
            var currentSubmitText = currentSubmitBtn.querySelector('span');

            currentSubmitBtn.disabled = true;
            currentSubmitIcon.className = 'fas fa-spinner fa-spin';
            currentSubmitText.textContent = 'Updating database...';

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
                    loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
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
                currentSubmitIcon.className = 'fas fa-save';
                currentSubmitText.textContent = 'Save Changes';
            });
        });
    }
</script>