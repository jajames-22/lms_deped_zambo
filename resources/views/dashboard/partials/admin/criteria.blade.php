<style>
    /* Custom Slider Styling */
    input[type=range].passing-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
        outline: none;
    }
    input[type=range].passing-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 20px;
        width: 20px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #16a34a;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: transform 0.1s;
    }
    input[type=range].passing-slider::-webkit-slider-thumb:hover {
        transform: scale(1.2);
    }
</style>

<div class="space-y-6 pb-20 max-w-5xl mx-auto relative animate-float-in">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Global Approval Criteria</h1>
            <p class="text-gray-500 text-sm mt-1">Define the standard evaluation rubric used by Admins to review and approve materials.</p>
        </div>
        
        <div class="flex gap-3">
            <button type="button" onclick="CriteriaBuilder.addCategory('', [], true)" class="px-5 py-2.5 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl shadow-sm hover:bg-red-50 transition-all flex items-center gap-2 active:scale-95">
                <i class="fas fa-folder-plus"></i> Add Category
            </button>
            <button type="button" onclick="CriteriaBuilder.save()" id="saveCriteriaBtn" class="px-6 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all flex items-center gap-2 active:scale-95">
                <i class="fas fa-save"></i> Save Criteria
            </button>
        </div>
    </div>

    {{-- FIXED RATING SCALE REFERENCE --}}
    <div class="bg-gray-900 p-6 md:p-8 rounded-3xl shadow-md border border-gray-800 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-white/5 to-transparent rounded-bl-full pointer-events-none"></div>
        
        <div class="flex items-center gap-3 mb-6 relative z-10">
            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center text-xl">
                <i class="fas fa-star text-yellow-400"></i>
            </div>
            <h3 class="text-lg font-bold">Standard Rating Scale</h3>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 relative z-10">
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center transition-transform hover:scale-105 cursor-default">
                <div class="text-2xl font-black text-green-400 mb-1">5</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300">Excellent</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center transition-transform hover:scale-105 cursor-default">
                <div class="text-2xl font-black text-blue-400 mb-1">4</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300">Good</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center transition-transform hover:scale-105 cursor-default">
                <div class="text-2xl font-black text-yellow-400 mb-1">3</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300">Satisfactory</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center transition-transform hover:scale-105 cursor-default">
                <div class="text-2xl font-black text-orange-400 mb-1">2</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300 leading-tight">Needs Improvement</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center transition-transform hover:scale-105 cursor-default">
                <div class="text-2xl font-black text-red-400 mb-1">1</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300 leading-tight">Unsatisfactory</div>
            </div>
        </div>
    </div>

    {{-- GLOBAL APPROVAL THRESHOLD (PASSING RATE) --}}
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-check-double"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Minimum Approval Score</h2>
            </div>
            <p class="text-sm text-gray-500 md:ml-13">Set the minimum percentage score required for a submitted material to pass evaluation and be officially published.</p>
        </div>
        <div class="w-full md:w-64 shrink-0 bg-gray-50 p-5 rounded-2xl border border-gray-200 text-center">
            <span id="passing-rate-display" class="text-3xl font-black text-green-600 mb-3 block">75%</span>
            <input type="range" id="passing-rate-slider" min="1" max="100" value="75" class="passing-slider" oninput="CriteriaBuilder.updatePassingRate(this.value)">
        </div>
    </div>

    {{-- DYNAMIC BUILDER CONTAINER --}}
    <div id="criteria-builder-container" class="space-y-6">
        </div>

    {{-- EMPTY STATE (Hidden if categories exist) --}}
    <div id="criteria-empty-state" class="bg-white p-12 rounded-3xl shadow-sm border border-dashed border-gray-300 text-center hidden transition-all duration-300">
        <div class="w-20 h-20 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            <i class="fas fa-list-check"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">No Criteria Defined</h3>
        <p class="text-gray-500 text-sm mb-6">Start building your evaluation rubric by adding a category.</p>
        <button type="button" onclick="CriteriaBuilder.addCategory('', [], true)" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition-all inline-flex items-center gap-2 active:scale-95">
            <i class="fas fa-folder-plus"></i> Add First Category
        </button>
    </div>

</div>

{{-- CONFIRMATION MODAL --}}
<div id="criteria-delete-modal" class="fixed inset-0 z-[150] hidden">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity" onclick="CriteriaBuilder.closeModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center animate-fade-in-up">
            <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Delete Category?</h3>
            <p class="text-gray-500 text-sm mb-6">Are you sure you want to delete this entire category and all its criteria? This cannot be undone.</p>
            <div class="flex gap-3 mt-2">
                <button type="button" onclick="CriteriaBuilder.closeModal()" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95">Cancel</button>
                <button type="button" id="criteria-confirm-delete-btn" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-md transition active:scale-95 flex items-center justify-center gap-2">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- SNACKBAR NOTIFICATION --}}
<div id="criteria-snackbar" class="fixed bg-green-600 bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
    <div id="criteria-snackbar-icon" class="text-xl"><i class="fas fa-check-circle"></i></div>
    <span id="criteria-snackbar-message">Notification message here</span>
    <button onclick="document.getElementById('criteria-snackbar').classList.add('translate-y-24', 'opacity-0')" class="ml-4 text-white/70 hover:text-white transition"><i class="fas fa-times"></i></button>
</div>

<script>
    // Define namespace safely for SPA
    window.CriteriaBuilder = window.CriteriaBuilder || {};
    
    // SAFE DECLARATION: Attach timer to window to prevent SPA "redeclaration" errors
    window.criteriaSnackbarTimer = window.criteriaSnackbarTimer || null;

    // Helper to generate Roman Numerals dynamically
    CriteriaBuilder.toRoman = function(num) {
        const roman = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XIII", "XIV", "XV"];
        return roman[num] || num;
    };

    // --- UI UPDATES ---
    CriteriaBuilder.updatePassingRate = function(val) {
        document.getElementById('passing-rate-display').innerText = val + '%';
        const slider = document.getElementById('passing-rate-slider');
        // Dynamic green track gradient
        slider.style.background = `linear-gradient(to right, #16a34a ${val}%, #e5e7eb ${val}%)`;
    };

    // --- CUSTOM NOTIFICATION SYSTEM ---
    CriteriaBuilder.showToast = function(message, type = 'success') {
        const snackbar = document.getElementById('criteria-snackbar');
        const icon = document.getElementById('criteria-snackbar-icon');
        const msg = document.getElementById('criteria-snackbar-message');

        snackbar.className = "fixed bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white";
        
        if (type === 'error') {
            snackbar.classList.add('bg-[#a52a2a]');
            icon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
        } else {
            snackbar.classList.add('bg-green-600');
            icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        }

        msg.innerText = message;
        
        setTimeout(() => snackbar.classList.remove('translate-y-24', 'opacity-0'), 10);
        
        clearTimeout(window.criteriaSnackbarTimer);
        window.criteriaSnackbarTimer = setTimeout(() => {
            snackbar.classList.add('translate-y-24', 'opacity-0');
        }, 3000);
    };

    // --- HTML TEMPLATES ---
    CriteriaBuilder.getCategoryHTML = function(title = '') {
        return `
            <div class="category-block bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-500 hover:shadow-md animate-fade-in-up relative">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#a52a2a]"></div>
                
                <div class="p-6 md:p-8 bg-gray-50/50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1 flex items-center gap-3">
                        <span class="category-number font-black text-xl text-[#a52a2a] w-8 text-right shrink-0"></span>
                        <input type="text" class="category-title w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 font-bold focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all placeholder-gray-400" 
                            placeholder="e.g. Content Accuracy & Relevance" value="${title}">
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" onclick="CriteriaBuilder.addItem(this)" class="px-4 py-2.5 bg-blue-50 text-blue-600 font-bold rounded-xl hover:bg-blue-100 transition-colors text-sm flex items-center gap-2 border border-blue-200 active:scale-95">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                        <button type="button" onclick="CriteriaBuilder.requestRemoveCategory(this)" class="w-10 h-10 bg-white border border-gray-200 text-gray-400 rounded-xl hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-colors flex items-center justify-center active:scale-95">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>

                <div class="items-container p-6 md:p-8 space-y-3">
                    </div>
            </div>
        `;
    };

    CriteriaBuilder.getItemHTML = function(desc = '') {
        return `
            <div class="item-row flex items-start gap-3 group animate-fade-in-up transition-all duration-300">
                <div class="w-8 h-11 flex items-center justify-end shrink-0">
                    <span class="item-number font-bold text-gray-400 text-sm"></span>
                </div>
                <input type="text" class="item-desc w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all placeholder-gray-400" 
                    placeholder="Enter specific criteria description..." value="${desc}">
                <button type="button" onclick="CriteriaBuilder.removeItem(this)" class="h-11 w-11 shrink-0 bg-transparent text-gray-300 rounded-xl hover:bg-red-50 hover:text-red-500 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 active:scale-95">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    };

    // --- DOM MANIPULATION ---
    CriteriaBuilder.addCategory = function(title = '', items = [], isUserAction = false) {
        const container = document.getElementById('criteria-builder-container');
        if(!container) return; 
        
        container.insertAdjacentHTML('beforeend', CriteriaBuilder.getCategoryHTML(title));
        
        const newCategory = container.lastElementChild;
        const itemsContainer = newCategory.querySelector('.items-container');
        
        if (items.length === 0) {
            itemsContainer.insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML());
        } else {
            items.forEach(desc => {
                itemsContainer.insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML(desc));
            });
        }
        
        CriteriaBuilder.updateNumbering();

        if (isUserAction) {
            setTimeout(() => {
                newCategory.scrollIntoView({ behavior: 'smooth', block: 'center' });
                newCategory.querySelector('.category-title').focus();
                CriteriaBuilder.showToast('New Category Block Added', 'success');
            }, 50);
        }
    };

    CriteriaBuilder.addItem = function(btn) {
        const itemsContainer = btn.closest('.category-block').querySelector('.items-container');
        itemsContainer.insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML());
        CriteriaBuilder.updateNumbering();
        
        const newItem = itemsContainer.lastElementChild;
        newItem.querySelector('.item-desc').focus();
    };

    // Modal Logic
    CriteriaBuilder.closeModal = function() {
        document.getElementById('criteria-delete-modal').classList.add('hidden');
    }

    CriteriaBuilder.requestRemoveCategory = function(btn) {
        const modal = document.getElementById('criteria-delete-modal');
        modal.classList.remove('hidden');
        
        document.getElementById('criteria-confirm-delete-btn').onclick = function() {
            CriteriaBuilder.closeModal();
            const block = btn.closest('.category-block');
            
            block.style.opacity = '0';
            block.style.transform = 'scale(0.95)';
            setTimeout(() => {
                block.remove();
                CriteriaBuilder.updateNumbering();
                CriteriaBuilder.showToast('Category deleted successfully.', 'success');
            }, 300);
        };
    };

    CriteriaBuilder.removeItem = function(btn) {
        const row = btn.closest('.item-row');
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        setTimeout(() => {
            row.remove();
            CriteriaBuilder.updateNumbering();
        }, 300);
    };

    CriteriaBuilder.updateNumbering = function() {
        const blocks = document.querySelectorAll('.category-block');
        const emptyState = document.getElementById('criteria-empty-state');
        
        if (blocks.length === 0) {
            if(emptyState) emptyState.classList.remove('hidden');
        } else {
            if(emptyState) emptyState.classList.add('hidden');
        }

        blocks.forEach((block, index) => {
            block.querySelector('.category-number').innerText = CriteriaBuilder.toRoman(index + 1) + ".";
            
            const items = block.querySelectorAll('.item-row');
            items.forEach((item, itemIndex) => {
                item.querySelector('.item-number').innerText = (itemIndex + 1) + ".";
            });
        });
    };

    // --- SAVE LOGIC ---
    CriteriaBuilder.save = async function() {
        const btn = document.getElementById('saveCriteriaBtn');
        const originalHtml = btn.innerHTML;
        
        const rubricData = [];
        let hasErrors = false;

        // Grab passing rate from slider
        const passingRate = parseInt(document.getElementById('passing-rate-slider').value) || 75;

        // Scrape categories
        document.querySelectorAll('.category-block').forEach(block => {
            const title = block.querySelector('.category-title').value.trim();
            if (!title) hasErrors = true;

            const items = [];
            block.querySelectorAll('.item-desc').forEach(input => {
                const desc = input.value.trim();
                if (desc) items.push(desc); 
            });

            rubricData.push({
                category: title,
                items: items
            });
        });

        if (rubricData.length === 0) return CriteriaBuilder.showToast('You must add at least one category to save.', 'error');
        if (hasErrors) return CriteriaBuilder.showToast('Category titles cannot be left blank.', 'error');

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        // 🟢 PREPARE NEW PAYLOAD FORMAT
        const payload = {
            passing_rate: passingRate,
            rubric: rubricData
        };

        try {
            const response = await fetch('{{ route("dashboard.criteria.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok) {
                CriteriaBuilder.showToast('Global Criteria and passing rate saved successfully!', 'success');
            } else {
                throw new Error(data.message || 'Failed to save rubric.');
            }
        } catch (error) {
            console.error(error);
            CriteriaBuilder.showToast(error.message, 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };

    // --- LOAD INITIAL DATA ---
    setTimeout(() => {
        // Fetch raw data which may now contain { passing_rate: XX, rubric: [...] }
        const savedData = @json($rubric ?? []);
        
        const container = document.getElementById('criteria-builder-container');
        if (container) container.innerHTML = '';

        // Safely determine where the rubric array actually is based on legacy vs new saves
        let existingRubric = [];
        let savedPassingRate = 75;

        if (Array.isArray(savedData)) {
            // Legacy save (Just an array of categories)
            existingRubric = savedData;
        } else if (savedData && typeof savedData === 'object') {
            // New save format
            existingRubric = savedData.rubric || [];
            savedPassingRate = savedData.passing_rate || 75;
        }

        // Apply slider value
        const slider = document.getElementById('passing-rate-slider');
        if(slider) {
            slider.value = savedPassingRate;
            CriteriaBuilder.updatePassingRate(savedPassingRate);
        }

        if (existingRubric && existingRubric.length > 0) {
            existingRubric.forEach(cat => {
                CriteriaBuilder.addCategory(cat.category, cat.items, false);
            });
        } else {
            // Default template structure
            CriteriaBuilder.addCategory('Content Quality & Relevance', [
                'The material presents factual, accurate, and up-to-date information.',
                'The content aligns perfectly with the DepEd curriculum.'
            ], false);
            CriteriaBuilder.addCategory('Instructional Design', [
                'Learning objectives are clearly stated and achievable.',
                'Assessments accurately measure student understanding.'
            ], false);
        }
    }, 50);

</script>