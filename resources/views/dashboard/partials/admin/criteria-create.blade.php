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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: transform 0.1s;
    }

    input[type=range].passing-slider::-webkit-slider-thumb:hover {
        transform: scale(1.2);
    }
</style>

<div class="space-y-6 pb-20 max-w-5xl mx-auto relative animate-float-in">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <button onclick="CriteriaBuilder.attemptLeave()"
                class="h-10 w-10 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-full flex items-center justify-center transition border border-gray-200 shrink-0">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isset($criteria) ? 'Edit Criteria' : 'Build Criteria' }}
                </h1>
                <p class="text-gray-500 text-sm mt-1">Define the standard evaluation rubric.</p>
            </div>
        </div>
    </div>

    {{-- METADATA SETTINGS --}}
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 space-y-4">
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Criteria Title</label>
            <input type="text" id="criteria-title" value="{{ $criteria->title ?? '' }}"
                placeholder="e.g. Standard Math DepEd Evaluation"
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all font-bold text-gray-900">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Description
                (Optional)</label>
            <textarea id="criteria-description" rows="2" placeholder="Brief description of when to use this rubric..."
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm resize-none text-gray-700">{{ $criteria->description ?? '' }}</textarea>
        </div>
    </div>

    {{-- FIXED RATING SCALE REFERENCE --}}
    <div
        class="bg-gray-900 p-6 md:p-8 rounded-3xl shadow-md border border-gray-800 text-white relative overflow-hidden">
        <div class="flex items-center gap-3 mb-6 relative z-10">
            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center text-xl"><i
                    class="fas fa-star text-yellow-400"></i></div>
            <h3 class="text-lg font-bold">Standard Rating Scale</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 relative z-10">
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center">
                <div class="text-2xl font-black text-green-400 mb-1">5</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300">Excellent</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center">
                <div class="text-2xl font-black text-blue-400 mb-1">4</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300">Good</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center">
                <div class="text-2xl font-black text-yellow-400 mb-1">3</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300">Satisfactory</div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center">
                <div class="text-2xl font-black text-orange-400 mb-1">2</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300 leading-tight">Needs Improvement
                </div>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/10 text-center">
                <div class="text-2xl font-black text-red-400 mb-1">1</div>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-300 leading-tight">Unsatisfactory
                </div>
            </div>
        </div>
    </div>

    {{-- GLOBAL APPROVAL THRESHOLD (PASSING RATE) --}}
    <div
        class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <div
                    class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-check-double"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Minimum Approval Score</h2>
            </div>
            <p class="text-sm text-gray-500 md:ml-13">Set the minimum percentage score required to pass evaluation.</p>
        </div>
        <div class="w-full md:w-64 shrink-0 bg-gray-50 p-5 rounded-2xl border border-gray-200 text-center">
            <span id="passing-rate-display" class="text-3xl font-black text-green-600 mb-3 block">75%</span>
            <input type="range" id="passing-rate-slider" min="1" max="100" value="{{ $criteria->passing_rate ?? 75 }}"
                class="passing-slider" oninput="CriteriaBuilder.updatePassingRate(this.value)">
        </div>
    </div>

    {{-- DYNAMIC BUILDER CONTAINER --}}
    <div id="criteria-builder-container" class="space-y-4"></div>

    {{-- BOTTOM ACTION BAR --}}
    <div class="flex p-6 gap-2">
        <button type="button" onclick="CriteriaBuilder.addCategory('', [], true)"
            class="w-full px-5 py-3 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl shadow-sm hover:bg-red-50 justify-center transition-all flex items-center gap-2 active:scale-95">
            <i class="fas fa-folder-plus"></i> Add Category
        </button>
        <button type="button" onclick="CriteriaBuilder.save()" id="saveCriteriaBtn"
            class="w-full px-8 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all flex justify-center items-center gap-2 active:scale-95">
            <i class="fas fa-save"></i> Save Criteria
        </button>
    </div>

    {{-- EMPTY STATE --}}
    <div id="criteria-empty-state"
        class="bg-white p-12 rounded-3xl shadow-sm border border-dashed border-gray-300 text-center hidden transition-all duration-300">
        <button type="button" onclick="CriteriaBuilder.addCategory('', [], true)"
            class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition-all inline-flex items-center gap-2 active:scale-95">
            <i class="fas fa-folder-plus"></i> Add First Category
        </button>
    </div>
</div>

{{-- MODALS & NOTIFICATIONS --}}

<div id="criteria-delete-modal" class="fixed inset-0 z-[150] hidden">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity" onclick="CriteriaBuilder.closeModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div
            class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center animate-fade-in-up">
            <div
                class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner">
                <i class="fas fa-trash-alt"></i></div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Delete Category?</h3>
            <div class="flex gap-3 mt-6"><button type="button" onclick="CriteriaBuilder.closeModal()"
                    class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button><button
                    type="button" id="criteria-confirm-delete-btn"
                    class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-md transition flex items-center justify-center gap-2"><i
                        class="fas fa-trash-alt"></i> Delete</button></div>
        </div>
    </div>
</div>

<div id="criteria-unsaved-modal" class="fixed inset-0 z-[160] hidden">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity" onclick="CriteriaBuilder.closeUnsavedModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div
            class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center animate-fade-in-up relative">
            <button onclick="CriteriaBuilder.closeUnsavedModal()"
                class="absolute top-4 right-5 text-gray-400 hover:text-gray-700 transition-colors text-lg"><i
                    class="fas fa-times"></i></button>
            <div
                class="h-16 w-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner">
                <i class="fas fa-exclamation-triangle"></i></div>
            <h3 class="text-xl font-black text-gray-900 mb-2">There are Unsaved Changes</h3>
            <p class="text-sm text-gray-500 mb-6">If you leave now, any changes you've made will be lost.</p>
            <div class="flex flex-col gap-2"><button type="button" onclick="CriteriaBuilder.save(true)"
                    class="w-full py-3 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 shadow-md transition">Save
                    Changes</button><button type="button" onclick="CriteriaBuilder.discardAndLeave()"
                    class="w-full py-3 bg-red-50 border border-red-100 text-red-600 font-bold rounded-xl hover:bg-red-100 transition">Discard
                    Changes</button><button type="button" onclick="CriteriaBuilder.closeUnsavedModal()"
                    class="w-full py-2 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm mt-1">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="criteria-snackbar"
    class="fixed bg-green-600 bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
    <div id="criteria-snackbar-icon" class="text-xl"></div><span id="criteria-snackbar-message"></span>
</div>

<script>
    window.CriteriaBuilder = window.CriteriaBuilder || {};
    window.criteriaSnackbarTimer = window.criteriaSnackbarTimer || null;
    CriteriaBuilder.isDirty = false;

    // Track Changes
    document.addEventListener('input', (e) => {
        if (e.target.closest('#criteria-builder-container') || e.target.id === 'criteria-title' || e.target.id === 'criteria-description' || e.target.id === 'passing-rate-slider') {
            CriteriaBuilder.isDirty = true;
        }
    });

    CriteriaBuilder.attemptLeave = function () {
        if (CriteriaBuilder.isDirty) {
            document.getElementById('criteria-unsaved-modal').classList.remove('hidden');
        } else {
            CriteriaBuilder.discardAndLeave();
        }
    };

    CriteriaBuilder.closeUnsavedModal = function () {
        document.getElementById('criteria-unsaved-modal').classList.add('hidden');
    };

    CriteriaBuilder.discardAndLeave = function () {
        CriteriaBuilder.isDirty = false;
        CriteriaBuilder.closeUnsavedModal();
        loadPartial('{{ route('dashboard.criteria') }}', document.getElementById('nav-criteria-btn'));
    };

    CriteriaBuilder.toRoman = function (num) { const roman = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XIII", "XIV", "XV"]; return roman[num] || num; };

    CriteriaBuilder.updatePassingRate = function (val) {
        document.getElementById('passing-rate-display').innerText = val + '%';
        const slider = document.getElementById('passing-rate-slider');
        slider.style.background = `linear-gradient(to right, #16a34a ${val}%, #e5e7eb ${val}%)`;
    };

    CriteriaBuilder.showToast = function (message, type = 'success') {
        const snackbar = document.getElementById('criteria-snackbar');
        const icon = document.getElementById('criteria-snackbar-icon');
        snackbar.className = "fixed bottom-6 right-6 z-[200] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white";
        if (type === 'error') { snackbar.classList.add('bg-[#a52a2a]'); icon.innerHTML = '<i class="fas fa-exclamation-circle"></i>'; }
        else { snackbar.classList.add('bg-green-600'); icon.innerHTML = '<i class="fas fa-check-circle"></i>'; }
        document.getElementById('criteria-snackbar-message').innerText = message;
        setTimeout(() => snackbar.classList.remove('translate-y-24', 'opacity-0'), 10);
        clearTimeout(window.criteriaSnackbarTimer);
        window.criteriaSnackbarTimer = setTimeout(() => { snackbar.classList.add('translate-y-24', 'opacity-0'); }, 3000);
    };

    CriteriaBuilder.getCategoryHTML = function (title = '') {
        return `
            <div class="category-wrapper group/cat flex flex-col">
                <div class="category-block bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-500 hover:shadow-md animate-fade-in-up relative">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#a52a2a]"></div>
                    <div class="p-6 md:p-8 bg-gray-50/50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1 flex items-center gap-3">
                            <span class="category-number font-black text-xl text-[#a52a2a] w-8 text-right shrink-0"></span>
                            <input type="text" class="category-title w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 font-bold focus:ring-2 focus:ring-[#a52a2a]/20 outline-none placeholder-gray-400" placeholder="e.g. Content Accuracy" value="${title}">
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button type="button" onclick="CriteriaBuilder.addItem(this)" class="px-4 py-2.5 bg-blue-50 text-blue-600 font-bold rounded-xl hover:bg-blue-100 transition-colors text-sm flex items-center gap-2 border border-blue-200 active:scale-95">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                            <button type="button" onclick="CriteriaBuilder.requestRemoveCategory(this)" class="w-10 h-10 bg-white border border-gray-200 text-gray-400 rounded-xl hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-colors flex items-center justify-center active:scale-95"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    <div class="items-container p-6 md:p-8 space-y-2"></div>
                </div>
                <div class="h-0 flex justify-center items-center opacity-0 group-hover/cat:opacity-100 transition-opacity duration-200 z-10 overflow-visible relative">
                    <button type="button" onclick="CriteriaBuilder.insertCategoryAfter(this)" class="w-8 h-8 bg-[#a52a2a] text-white rounded-full shadow-md border-2 border-white flex items-center justify-center hover:scale-110 hover:bg-red-800 transition-all text-sm absolute top-[-16px]" title="Add Category Below"><i class="fas fa-plus"></i></button>
                </div>
            </div>`;
    };
    

    CriteriaBuilder.getItemHTML = function (desc = '') {
        return `
            <div class="item-wrapper group/item flex flex-col">
                <div class="item-row flex items-start gap-3 transition-all duration-300 relative">
                    <div class="w-8 h-11 flex items-center justify-end shrink-0"><span class="item-number font-bold text-gray-400 text-sm"></span></div>
                    <input type="text" class="item-desc w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#a52a2a]/20 outline-none placeholder-gray-400" placeholder="Enter specific criteria description..." value="${desc}">
                    <button type="button" onclick="CriteriaBuilder.removeItem(this)" class="h-11 w-11 shrink-0 bg-transparent text-gray-300 rounded-xl hover:bg-red-50 hover:text-red-500 transition-colors flex items-center justify-center opacity-0 group-hover/item:opacity-100 active:scale-95"><i class="fas fa-times"></i></button>
                </div>
                <div class="h-3 flex justify-center items-center opacity-0 group-hover/item:opacity-100 transition-opacity duration-200 z-10 overflow-visible relative mt-1">
                    <button type="button" onclick="CriteriaBuilder.insertItemAfter(this)" class="w-6 h-6 bg-blue-50 text-blue-600 rounded-full border border-blue-200 shadow-sm flex items-center justify-center hover:scale-110 hover:bg-blue-600 hover:text-white transition-all text-xs absolute" title="Add Item Below"><i class="fas fa-plus"></i></button>
                </div>
            </div>`;
    };

    CriteriaBuilder.addCategory = function (title = '', items = [], isUserAction = false) {
        const container = document.getElementById('criteria-builder-container');
        if (!container) return;
        container.insertAdjacentHTML('beforeend', CriteriaBuilder.getCategoryHTML(title));
        const newCategoryWrapper = container.lastElementChild;
        const itemsContainer = newCategoryWrapper.querySelector('.items-container');

        if (items.length === 0) itemsContainer.insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML());
        else items.forEach(desc => itemsContainer.insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML(desc)));

        CriteriaBuilder.updateNumbering();
        if (isUserAction) {
            CriteriaBuilder.isDirty = true;
            setTimeout(() => { newCategoryWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 50);
        }
    };

    CriteriaBuilder.insertCategoryAfter = function (btn) {
        const currentWrapper = btn.closest('.category-wrapper');
        currentWrapper.insertAdjacentHTML('afterend', CriteriaBuilder.getCategoryHTML());
        const newCategoryWrapper = currentWrapper.nextElementSibling;
        newCategoryWrapper.querySelector('.items-container').insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML());
        CriteriaBuilder.updateNumbering();
        CriteriaBuilder.isDirty = true;
    };

    CriteriaBuilder.insertItemAfter = function (btn) {
        const currentItemWrapper = btn.closest('.item-wrapper');
        currentItemWrapper.insertAdjacentHTML('afterend', CriteriaBuilder.getItemHTML());
        CriteriaBuilder.updateNumbering();
        CriteriaBuilder.isDirty = true;
    };

    CriteriaBuilder.closeModal = function () { document.getElementById('criteria-delete-modal').classList.add('hidden'); }
    CriteriaBuilder.requestRemoveCategory = function (btn) {
        document.getElementById('criteria-delete-modal').classList.remove('hidden');
        document.getElementById('criteria-confirm-delete-btn').onclick = function () {
            CriteriaBuilder.closeModal();
            const wrapper = btn.closest('.category-wrapper');
            wrapper.style.opacity = '0';
            setTimeout(() => { wrapper.remove(); CriteriaBuilder.updateNumbering(); CriteriaBuilder.isDirty = true; }, 300);
        };
    };

    CriteriaBuilder.removeItem = function (btn) {
        const wrapper = btn.closest('.item-wrapper');
        wrapper.style.opacity = '0';
        setTimeout(() => { wrapper.remove(); CriteriaBuilder.updateNumbering(); CriteriaBuilder.isDirty = true; }, 300);
    };

    CriteriaBuilder.updateNumbering = function () {
        const blocks = document.querySelectorAll('.category-block');
        const emptyState = document.getElementById('criteria-empty-state');
        if (blocks.length === 0 && emptyState) emptyState.classList.remove('hidden');
        else if (emptyState) emptyState.classList.add('hidden');

        blocks.forEach((block, index) => {
            block.querySelector('.category-number').innerText = CriteriaBuilder.toRoman(index + 1) + ".";
            block.querySelectorAll('.item-row').forEach((item, itemIndex) => { item.querySelector('.item-number').innerText = (itemIndex + 1) + "."; });
        });
    };

    CriteriaBuilder.save = async function (redirectAfter = false) {
        const btn = document.getElementById('saveCriteriaBtn');
        const originalHtml = btn.innerHTML;
        const rubricData = [];
        let hasErrors = false;

        const titleInput = document.getElementById('criteria-title').value.trim();
        const descInput = document.getElementById('criteria-description').value.trim();
        const passingRate = parseInt(document.getElementById('passing-rate-slider').value) || 75;

        if (!titleInput) return CriteriaBuilder.showToast('Criteria Title is required.', 'error');

        document.querySelectorAll('.category-block').forEach(block => {
            const title = block.querySelector('.category-title').value.trim();
            if (!title) hasErrors = true;
            const items = [];
            block.querySelectorAll('.item-desc').forEach(input => {
                const desc = input.value.trim();
                if (desc) items.push(desc);
            });
            rubricData.push({ category: title, items: items });
        });

        if (rubricData.length === 0) return CriteriaBuilder.showToast('Add at least one category to save.', 'error');
        if (hasErrors) return CriteriaBuilder.showToast('Category titles cannot be left blank.', 'error');

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const payload = {
            id: "{{ $criteria->id ?? '' }}",
            title: titleInput,
            description: descInput,
            passing_rate: passingRate,
            rubric: rubricData
        };

        try {
            const response = await fetch('{{ route("dashboard.criteria.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (response.ok) {
                CriteriaBuilder.isDirty = false;
                if (redirectAfter) {
                    CriteriaBuilder.discardAndLeave();
                } else {
                    CriteriaBuilder.showToast('Criteria saved successfully!', 'success');
                    setTimeout(() => loadPartial('{{ route('dashboard.criteria') }}', document.getElementById('nav-criteria-btn')), 1000);
                }
            } else throw new Error(data.message || 'Failed to save rubric.');
        } catch (error) {
            CriteriaBuilder.showToast(error.message, 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };

    // Load initial Data
    setTimeout(() => {
        let savedData = @json(isset($criteria) ? (is_string($criteria->rubric) ? json_decode($criteria->rubric, true) : $criteria->rubric) : []);
        let existingRubric = [];
        let savedPassingRate = {{ $criteria->passing_rate ?? 75 }};

        // Parse legacy or current schema payload
        if (Array.isArray(savedData)) existingRubric = savedData;
        else if (savedData && typeof savedData === 'object') {
            existingRubric = savedData.rubric || [];
            if (savedData.passing_rate) savedPassingRate = savedData.passing_rate;
        }

        const slider = document.getElementById('passing-rate-slider');
        if (slider) { slider.value = savedPassingRate; CriteriaBuilder.updatePassingRate(savedPassingRate); }

        if (existingRubric.length > 0) {
            existingRubric.forEach(cat => CriteriaBuilder.addCategory(cat.category, cat.items, false));
        } else {
            CriteriaBuilder.addCategory('Content Quality & Relevance', ['The material presents factual, accurate, and up-to-date information.', 'The content aligns perfectly with the DepEd curriculum.'], false);
            CriteriaBuilder.addCategory('Instructional Design', ['Learning objectives are clearly stated and achievable.', 'Assessments accurately measure student understanding.'], false);
        }

        // Reset dirty flag after initial setup is complete
        setTimeout(() => { CriteriaBuilder.isDirty = false; }, 200);
    }, 50);

    // Re-added logic for the explicit Add Item button in the header
    CriteriaBuilder.addItem = function (btn) {
        const itemsContainer = btn.closest('.category-block').querySelector('.items-container');
        itemsContainer.insertAdjacentHTML('beforeend', CriteriaBuilder.getItemHTML());
        CriteriaBuilder.updateNumbering();
        CriteriaBuilder.isDirty = true;
        
        // Auto-focus the newly added item to make typing faster
        const newItem = itemsContainer.lastElementChild;
        newItem.querySelector('.item-desc').focus();
    };
</script>