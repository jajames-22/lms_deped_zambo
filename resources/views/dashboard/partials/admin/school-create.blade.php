<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New School</h1>
            <p class="text-gray-500 text-sm">Register a new institution in the Zamboanga Division.</p>
        </div>
        
        <button type="button" onclick="loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'))" 
            class="flex items-center gap-2 px-4 py-2 text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-xl font-medium transition-all active:scale-95">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Directory</span>
        </button>
    </div>

    <form action="{{ route('schools.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
        @csrf 

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">School Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required placeholder="e.g., Zamboanga City National High School (Main)" 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-700">
            </div>

            <div>
                <label for="level" class="block text-sm font-bold text-gray-700 mb-2">Academic Level <span class="text-red-500">*</span></label>
                <select name="level" id="level" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-700">
                    <option value="" disabled selected>Select Level...</option>
                    <option value="elementary">Elementary</option>
                    <option value="highschool">High School</option>
                    <option value="seniorHighschool">Senior High School</option>
                    <option value="integrated">Integrated School</option>
                </select>
            </div>

            <div>
                <label for="logo" class="block text-sm font-bold text-gray-700 mb-2">School Logo (Optional)</label>
                <input type="file" name="logo" id="logo" accept="image/*"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#a52a2a]/10 file:text-[#a52a2a] hover:file:bg-[#a52a2a]/20">
            </div>

            <div>
                <label for="quadrant_id" class="block text-sm font-bold text-gray-700 mb-2">Quadrant <span class="text-red-500">*</span></label>
                <select name="quadrant_id" id="quadrant_id" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-700">
                    <option value="" disabled selected>Select Quadrant...</option>
                    @foreach($quadrants as $quadrant)
                        <option value="{{ $quadrant->id }}">{{ $quadrant->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="district_id" class="block text-sm font-bold text-gray-700 mb-2">District <span class="text-red-500">*</span></label>
                <select name="district_id" id="district_id" required disabled
                    class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-500 cursor-not-allowed disabled:opacity-75">
                    <option value="" disabled selected>Select a Quadrant first...</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-bold text-gray-700 mb-2">Complete Address (Optional)</label>
                <textarea name="address" id="address" rows="3" placeholder="Street, Barangay, City..."
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-gray-700 resize-none"></textarea>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <button type="reset" class="px-6 py-3 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition-all active:scale-95">
                Clear Form
            </button>
            <button type="submit" class="px-8 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition-all active:scale-95 flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Save School</span>
            </button>
        </div>
    </form>
</div>