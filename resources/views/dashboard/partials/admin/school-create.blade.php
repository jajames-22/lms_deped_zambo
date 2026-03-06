<div class="max-w-4xl mx-auto space-y-6 pb-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Register Institution</h1>
            <p class="text-sm text-gray-500">Add a new school to the Zamboanga Division database.</p>
        </div>
        <button type="button" onclick="loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'))"
            class="flex items-center gap-2 px-4 py-2 ...">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Directory</span>
        </button>
    </div>

    <form action="{{ route('schools.store') }}" method="POST" enctype="multipart/form-data" id="createSchoolForm"
        class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm text-center">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4">School
                        Logo</label>
                    <div class="relative group mx-auto w-32 h-32 mb-4">
                        <div id="logo-preview"
                            class="w-full h-full rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden">
                            <i class="fas fa-university text-3xl text-gray-300"></i>
                        </div>
                        <label for="logo"
                            class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl cursor-pointer">
                            <i class="fas fa-camera text-white"></i>
                        </label>
                    </div>
                    <input type="file" name="logo" id="logo" class="hidden" accept="image/*"
                        onchange="previewImage(event)">
                    <p class="text-[10px] text-gray-400">PNG or JPG. Max 2MB.</p>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Official
                            Name</label>
                        <input type="text" name="name" required placeholder="Enter full school name..."
                            class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Level</label>
                            <select name="level" required
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                <option value="elementary">Elementary</option>
                                <option value="highschool">High School</option>
                                <option value="seniorHighschool">Senior High</option>
                                <option value="integrated">Integrated</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Address</label>
                            <input type="text" name="address" placeholder="Barangay/Street"
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-50">
                        <div>
                            <label
                                class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Quadrant</label>
                            <select name="quadrant_id" id="quadrant_id" required
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none">
                                <option value="" disabled selected>Select...</option>
                                @foreach ($quadrants as $quadrant)
                                    <option value="{{ $quadrant->id }}">{{ $quadrant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">District</label>
                            <select name="district_id" id="district_id" required disabled
                                class="w-full px-4 py-3 bg-gray-50 border-transparent focus:border-[#a52a2a] rounded-xl outline-none disabled:opacity-50">
                                <option value="">Select Quadrant first</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-2xl shadow-xl shadow-red-900/20 hover:bg-red-800 transition-all flex items-center justify-center gap-3">
                    <i class="fas fa-check-circle"></i> Complete Registration
                </button>
            </div>
        </div>
    </form>
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
            console.log("Quadrant selected ID:", qId);

            // UI Reset
            dSelect.disabled = true;
            dSelect.innerHTML = '<option>Loading districts...</option>';

            // Fetch districts
            fetch(`/get-districts/${qId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    console.log("Districts received:", data);
                    dSelect.innerHTML = '<option value="" disabled selected>Select District</option>';

                    if (data.length > 0) {
                        data.forEach(d => {
                            const option = document.createElement('option');
                            option.value = d.id;
                            option.textContent = d.name;
                            dSelect.appendChild(option);
                        });
                        dSelect.disabled = false; // <--- This unlocks it
                    } else {
                        dSelect.innerHTML = '<option>No districts found</option>';
                    }
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    dSelect.innerHTML = '<option>Error loading districts</option>';
                });
        });
    } else {
        console.error("Could not find quadrant_id element");
    }
</script>
