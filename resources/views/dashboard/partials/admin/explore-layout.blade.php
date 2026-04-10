<div class="max-w-5xl mx-auto space-y-8 pb-24 relative">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-900 tracking-tight">Explore Layout</h2>
            <p class="text-sm text-gray-500 mt-1">Manage the dynamic categories and featured banner.</p>
        </div>
    </div>

    {{-- 1. FEATURED MATERIALS MANAGER --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Featured Carousel</h3>
                <p class="text-xs text-gray-500">These materials appear in the large auto-sliding banner.</p>
            </div>
        </div>

        {{-- Search & Add --}}
        <div class="relative mb-6 z-10">
            <div class="relative flex items-center bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus-within:border-[#a52a2a] focus-within:ring-2 focus-within:ring-[#a52a2a]/20 transition-all">
                <i class="fas fa-search text-gray-400 mr-3"></i>
                <input type="text" id="material-search" oninput="window.debounceSearch(this.value)" placeholder="Search material by title or instructor name..." class="w-full bg-transparent border-none outline-none text-sm text-gray-900">
                <i id="search-spinner" class="fas fa-spinner fa-spin text-[#a52a2a] ml-3" style="display: none;"></i>
            </div>
            
            {{-- Search Results Dropdown --}}
            <div id="search-results" class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl hidden max-h-80 overflow-y-auto overflow-x-hidden">
            </div>
        </div>

        {{-- Current Featured List --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" id="featured-list">
            @forelse($featuredMaterials as $material)
                <div class="relative group bg-gray-50 border border-gray-200 rounded-xl p-3 flex gap-3 items-center hover:border-[#a52a2a]/30 transition-colors">
                    <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=100' }}" 
                         class="w-12 h-12 rounded-lg object-cover shadow-sm bg-gray-200">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $material->title }}</p>
                        <p class="text-[10px] text-gray-500 truncate">By {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}</p>
                    </div>
                    <button type="button" onclick="window.toggleFeaturedManager({{ $material->id }}, this)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded-full flex items-center justify-center shadow-sm transition-colors tooltip" title="Remove from featured">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            @empty
                <div class="col-span-full text-center py-6">
                    <p class="text-sm text-gray-400">No materials are currently featured.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 2. DYNAMIC SECTIONS LIST --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Active Sections</h3>
                <p class="text-xs text-gray-500">Drag to reorder. These pull materials based on tags.</p>
            </div>
            <button type="button" onclick="window.openSectionModal('add')" class="px-4 py-2 text-sm bg-gray-900 text-white font-bold rounded-lg hover:bg-gray-800 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Section
            </button>
        </div>

        <ul id="sections-list" class="space-y-3">
            @forelse($sections as $section)
                @php 
                    $tags = json_decode($section->tag_name, true);
                    if(!is_array($tags)) $tags = [$section->tag_name];
                @endphp
                <li class="section-item bg-gray-50 border border-gray-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 transition-all hover:shadow-md cursor-move" 
                    draggable="true" data-id="{{ $section->id }}">
                    
                    <div class="text-gray-300 hover:text-gray-500 px-2 hidden sm:block">
                        <i class="fas fa-grip-vertical text-xl"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-lg truncate mb-1">{{ $section->title }}</h4>
                        <div class="flex flex-wrap gap-1.5 mb-1">
                            @foreach($tags as $t)
                                <span class="px-2 py-0.5 bg-[#a52a2a]/10 text-[#a52a2a] rounded shadow-sm text-[9px] font-black uppercase tracking-wider border border-[#a52a2a]/20">
                                    {{ $t }}
                                </span>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 truncate mt-1">{{ $section->subtitle ?: 'No subtitle provided' }}</p>
                    </div>

                    <div class="flex items-center gap-4 shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 mt-3 sm:mt-0 border-gray-200">
                        <label class="relative inline-flex items-center cursor-pointer" title="Toggle Visibility">
                            <input type="checkbox" class="sr-only peer" onchange="window.toggleSectionStatus('{{ $section->id }}', this)" {{ $section->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        </label>

                        <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                        <button type="button" onclick="window.openSectionModal('edit', {{ json_encode($section) }})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors tooltip" title="Edit">
                            <i class="fas fa-pen text-sm"></i>
                        </button>
                        
                        <button type="button" onclick="window.confirmDeleteSection('{{ $section->id }}')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors tooltip" title="Delete">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </li>
            @empty
                <div class="text-center py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <p class="text-sm text-gray-500 mt-1">No dynamic sections added yet.</p>
                </div>
            @endforelse
        </ul>
    </div>
</div>

{{-- Add/Edit Section Modal (WITH FADE TRANSITION) --}}
<div id="sectionModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4" style="position: fixed;">
    <div class="absolute inset-0 bg-gray-900/60" onclick="window.closeSectionModal()"></div>
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 relative z-10" id="sectionModalBox">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 id="modalTitle" class="text-xl font-black text-gray-900">Add Section</h3>
            <button type="button" onclick="window.closeSectionModal()" class="text-gray-400 hover:text-gray-700 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form id="sectionForm" onsubmit="window.saveSection(event)" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="section_id" name="id">
            <input type="hidden" id="section_tag_name" name="tag_name">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Section Title <span class="text-red-500">*</span></label>
                <input type="text" id="section_title" name="title" required placeholder="e.g. Ready for Science?" 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all font-medium text-gray-900">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Subtitle (Optional)</label>
                <input type="text" id="section_subtitle" name="subtitle" placeholder="e.g. Explore our best biology modules" 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all font-medium text-gray-900">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Tags <span class="text-red-500">*</span></label>
                
                <div class="w-full min-h-[50px] bg-gray-50 border border-gray-200 rounded-xl p-2 focus-within:ring-2 focus-within:ring-[#a52a2a]/20 focus-within:border-[#a52a2a] transition-all flex flex-wrap gap-2 items-center cursor-text" onclick="document.getElementById('tag-input-field').focus()">
                    <div id="active-tags-container" class="flex flex-wrap gap-2"></div>
                    <input type="text" id="tag-input-field" placeholder="Type a tag & press Enter..." class="flex-1 bg-transparent border-none outline-none text-sm min-w-[150px] p-1" onkeydown="window.handleExploreTagKeydown(event)">
                </div>
                
                <div class="mt-3">
                    <p class="text-[10px] text-gray-400 font-bold uppercase mb-2">Or click to add:</p>
                    <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto no-scrollbar p-1">
                        @foreach($availableTags as $tag)
                            <button type="button" onclick="window.addExploreTag('{{ $tag }}')" class="px-2 py-1 bg-white border border-gray-200 rounded text-xs text-gray-600 hover:border-[#a52a2a] hover:text-[#a52a2a] transition-colors">
                                {{ $tag }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="window.closeSectionModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" id="saveSectionBtn" class="flex-1 px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Save Section
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Custom Alert Modal (WITH FADE TRANSITION) --}}
<div id="alertModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4" style="position: fixed;">
    <div class="absolute inset-0 bg-gray-900/60" onclick="window.closeAlertModal()"></div>
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10" id="alertModalBox">
        <div id="alertIconContainer" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
            <i id="alertIcon" class="fas fa-info"></i>
        </div>
        <h3 id="alertTitle" class="text-xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="alertMessage" class="text-sm text-gray-500 mb-6"></p>
        <button type="button" onclick="window.closeAlertModal()" class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
            Okay
        </button>
    </div>
</div>

<script>
    // ==========================================
    // DOM PREPARATION (Fixes Modal Trapping)
    // ==========================================
    setTimeout(() => {
        const sectionModal = document.getElementById('sectionModal');
        const alertModal = document.getElementById('alertModal');
        // Moving modals to the end of the <body> ensures they break out of any partial containers
        if(sectionModal && sectionModal.parentElement !== document.body) document.body.appendChild(sectionModal);
        if(alertModal && alertModal.parentElement !== document.body) document.body.appendChild(alertModal);
    }, 50);

    // ==========================================
    // BODY SCROLL LOCKING
    // ==========================================
    window.toggleBodyScroll = function(disable) {
        if (disable) {
            document.body.classList.add('overflow-hidden');
        } else {
            // Check if ANY modal is still open before re-enabling scroll
            const sectionModal = document.getElementById('sectionModal');
            const alertModal = document.getElementById('alertModal');
            const isSectionOpen = sectionModal && !sectionModal.classList.contains('hidden');
            const isAlertOpen = alertModal && !alertModal.classList.contains('hidden');
            
            if (!isSectionOpen && !isAlertOpen) {
                document.body.classList.remove('overflow-hidden');
            }
        }
    };

    // ==========================================
    // ALERT MODAL LOGIC (WITH FADE TRANSITION)
    // ==========================================
    window.showCustomAlert = function(message, type) {
        const modal = document.getElementById('alertModal');
        const box = document.getElementById('alertModalBox');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const title = document.getElementById('alertTitle');
        const msg = document.getElementById('alertMessage');

        if(!modal) return;

        msg.innerText = message;

        if (type === 'success') {
            title.innerText = 'Success!';
            iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-100 text-green-500';
            icon.className = 'fas fa-check-circle';
        } else {
            title.innerText = 'Error!';
            iconContainer.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-red-100 text-red-500';
            icon.className = 'fas fa-exclamation-circle';
        }

        window.toggleBodyScroll(true); // Lock background
        modal.classList.remove('hidden');
        
        // ADDED: Fade in the background and scale up the box
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    };

    window.closeAlertModal = function() {
        const modal = document.getElementById('alertModal');
        const box = document.getElementById('alertModalBox');
        
        // ADDED: Fade out the background and scale down the box
        if(box) {
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
        }
        if(modal) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
        }
        
        // Wait 300ms for the fade to finish, then hide it completely
        setTimeout(() => { 
            if(modal) modal.classList.add('hidden'); 
            window.toggleBodyScroll(false); // Unlock background
        }, 300);
    };

    // ==========================================
    // FEATURED MATERIALS MANAGER
    // ==========================================
    window.exploreSearchTimeout = null;
    window.exploreSearchReqId = 0; // Tracks the active search ticket

    window.debounceSearch = function(query) {
        clearTimeout(window.exploreSearchTimeout);
        const resultsBox = document.getElementById('search-results');
        const spinner = document.getElementById('search-spinner');

        if (!resultsBox) return;

        // 1. If cleared, instantly hide spinner and invalidate old searches
        if (query.trim().length < 2) {
            window.exploreSearchReqId++; 
            resultsBox.classList.add('hidden');
            if (spinner) spinner.style.display = 'none';
            return;
        }
        
        window.exploreSearchTimeout = setTimeout(async () => {
            const currentReqId = ++window.exploreSearchReqId; // Generate new ticket
            
            // Show spinner ONLY when fetch begins
            if (spinner) spinner.style.display = 'inline-block';
            
            try {
                const response = await fetch(`{{ url('/dashboard/explore-layout/search-materials') }}?q=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                // Safety Check: Ignore this result if a newer search was started
                if (currentReqId !== window.exploreSearchReqId) return;

                const materials = await response.json();
                resultsBox.innerHTML = '';

                if (materials.length === 0) {
                    resultsBox.innerHTML = '<div class="p-4 text-sm text-gray-500 text-center">No materials found.</div>';
                } else {
                    materials.forEach(mat => {
                        const name = mat.instructor ? `${mat.instructor.first_name} ${mat.instructor.last_name}` : 'Unknown';
                        const thumb = mat.thumbnail ? `/storage/${mat.thumbnail}` : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=100';
                        
                        resultsBox.innerHTML += `
                            <div class="p-3 border-b border-gray-50 hover:bg-gray-50 flex items-center justify-between gap-3 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="${thumb}" class="w-10 h-10 rounded-lg object-cover bg-gray-200 shrink-0">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">${mat.title}</p>
                                        <p class="text-[10px] text-gray-500 truncate">By ${name}</p>
                                    </div>
                                </div>
                                <button type="button" onclick="window.toggleFeaturedManager(${mat.id}, this)" class="px-3 py-1.5 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-[#a52a2a] transition-colors shrink-0">
                                    Add
                                </button>
                            </div>
                        `;
                    });
                }
                resultsBox.classList.remove('hidden');
            } catch (error) {
                if (currentReqId === window.exploreSearchReqId) console.error("Search failed", error);
            } finally {
                // ALWAYS hide spinner, BUT ONLY if this is still the active search!
                if (currentReqId === window.exploreSearchReqId) {
                    if (spinner) spinner.style.display = 'none';
                }
            }
        }, 400); 
    };

    document.addEventListener('click', (e) => {
        const resultsBox = document.getElementById('search-results');
        if (resultsBox && !e.target.closest('.relative.z-20')) {
            resultsBox.classList.add('hidden');
        }
    });

    window.toggleFeaturedManager = async function(materialId, btn) {
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const response = await fetch(`{{ url('/dashboard/materials') }}/${materialId}/toggle-featured`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (response.ok && data.success) {
                window.showCustomAlert(data.message, 'success');
                window.refreshCurrentExplorePartial(); 
            } else {
                window.showCustomAlert('Failed to update featured status.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        } catch (error) {
            window.showCustomAlert('Failed to toggle featured status.', 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };

    // ==========================================
    // MULTI-TAG MANAGER
    // ==========================================
    window.exploreTags = [];
    
    window.renderExploreTags = function() {
        const container = document.getElementById('active-tags-container');
        const hiddenInput = document.getElementById('section_tag_name');
        if (!container || !hiddenInput) return;

        container.innerHTML = '';
        window.exploreTags.forEach(tag => {
            container.innerHTML += `
                <span class="px-2 py-1 bg-[#a52a2a] text-white rounded text-xs font-bold flex items-center gap-1 shadow-sm transition-transform hover:scale-105">
                    ${tag}
                    <i class="fas fa-times cursor-pointer ml-1 hover:text-red-300" onclick="window.removeExploreTag('${tag}')"></i>
                </span>
            `;
        });
        hiddenInput.value = JSON.stringify(window.exploreTags); 
    };

    window.addExploreTag = function(tag) {
        const cleanTag = tag.trim();
        if (cleanTag && !window.exploreTags.includes(cleanTag)) {
            window.exploreTags.push(cleanTag);
            window.renderExploreTags();
        }
        const tagInput = document.getElementById('tag-input-field');
        if(tagInput) tagInput.value = '';
    };

    window.removeExploreTag = function(tag) {
        window.exploreTags = window.exploreTags.filter(t => t !== tag);
        window.renderExploreTags();
    };

    window.handleExploreTagKeydown = function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            window.addExploreTag(e.target.value);
        }
    };

    // ==========================================
    // SECTIONS MODAL LOGIC (WITH FADE TRANSITION)
    // ==========================================
    window.exploreModalMode = 'add';

    window.openSectionModal = function(mode, data = null) {
        window.exploreModalMode = mode;
        const modal = document.getElementById('sectionModal');
        const box = document.getElementById('sectionModalBox');
        
        const form = document.getElementById('sectionForm');
        if(form) form.reset();
        
        window.exploreTags = [];

        if (mode === 'edit' && data) {
            document.getElementById('modalTitle').innerText = 'Edit Section';
            document.getElementById('section_id').value = data.id;
            document.getElementById('section_title').value = data.title;
            document.getElementById('section_subtitle').value = data.subtitle || '';
            
            try {
                let parsed = JSON.parse(data.tag_name);
                window.exploreTags = Array.isArray(parsed) ? parsed : [data.tag_name];
            } catch(e) {
                window.exploreTags = [data.tag_name];
            }
        } else {
            document.getElementById('modalTitle').innerText = 'Add New Section';
            document.getElementById('section_id').value = '';
        }

        window.renderExploreTags(); 

        window.toggleBodyScroll(true); // Lock background
        modal.classList.remove('hidden');
        
        // ADDED: Fade in the background and scale up the box
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    };

    window.closeSectionModal = function() {
        const modal = document.getElementById('sectionModal');
        const box = document.getElementById('sectionModalBox');
        
        // ADDED: Fade out the background and scale down the box
        if(box) {
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
        }
        if(modal) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
        }
        
        // Wait 300ms for the fade to finish, then hide it completely
        setTimeout(() => { 
            if(modal) modal.classList.add('hidden'); 
            window.toggleBodyScroll(false); // Unlock background
        }, 300);
    };

    window.saveSection = async function(e) {
        e.preventDefault();
        
        // Final safety check: Catch any text typed but not entered
        const tagInput = document.getElementById('tag-input-field');
        if (tagInput && tagInput.value.trim() !== '') {
            window.addExploreTag(tagInput.value);
        }

        if(window.exploreTags.length === 0) {
            window.showCustomAlert('Please add at least one tag.', 'error');
            return;
        }

        const form = e.target;
        const btn = document.getElementById('saveSectionBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const id = document.getElementById('section_id').value;
        const url = window.exploreModalMode === 'add' ? '{{ route("dashboard.explore-layout.store") }}' : `/dashboard/explore-layout/${id}`;
        const method = window.exploreModalMode === 'add' ? 'POST' : 'PUT';
        
        const formData = new FormData(form);
        const jsonData = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(jsonData)
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                window.closeSectionModal();
                window.showCustomAlert(data.message, 'success');
                window.refreshCurrentExplorePartial();
            } else {
                window.showCustomAlert(data.message || 'Validation failed.', 'error');
            }
        } catch (error) {
            window.showCustomAlert('Network error occurred.', 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };

    window.toggleSectionStatus = async function(id, checkbox) {
        checkbox.disabled = true;
        try {
            const response = await fetch(`{{ url('/dashboard/explore-layout') }}/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                window.showCustomAlert(data.message, 'success');
            } else {
                checkbox.checked = !checkbox.checked;
                window.showCustomAlert('Failed to update status.', 'error');
            }
        } catch (error) {
            checkbox.checked = !checkbox.checked;
            window.showCustomAlert('Network error occurred.', 'error');
        } finally {
            checkbox.disabled = false;
        }
    };

    window.confirmDeleteSection = async function(id) {
        if (!confirm('Are you sure you want to delete this section? This will only remove it from the explore page, not the materials themselves.')) return;
        try {
            const response = await fetch(`{{ url('/dashboard/explore-layout') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                window.showCustomAlert(data.message, 'success');
                window.refreshCurrentExplorePartial();
            } else {
                window.showCustomAlert('Failed to delete section.', 'error');
            }
        } catch (error) {
            window.showCustomAlert('Network error occurred.', 'error');
        }
    };

    // ==========================================
    // DRAG AND DROP
    // ==========================================
    window.draggedSectionItem = null;
    
    window.initSectionDragAndDrop = function() {
        const list = document.getElementById('sections-list');
        if (!list) return;

        list.querySelectorAll('.section-item').forEach(item => {
            item.addEventListener('dragstart', function(e) {
                window.draggedSectionItem = item;
                setTimeout(() => item.classList.add('opacity-50', 'border-[#a52a2a]'), 0);
            });

            item.addEventListener('dragend', function() {
                setTimeout(() => {
                    if(window.draggedSectionItem) window.draggedSectionItem.classList.remove('opacity-50', 'border-[#a52a2a]');
                    window.draggedSectionItem = null;
                    window.saveNewSectionOrder();
                }, 0);
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                const bounding = item.getBoundingClientRect();
                const offset = bounding.y + (bounding.height / 2);
                if (e.clientY - offset > 0) {
                    item.style['border-bottom'] = '2px solid #a52a2a';
                    item.style['border-top'] = '';
                } else {
                    item.style['border-top'] = '2px solid #a52a2a';
                    item.style['border-bottom'] = '';
                }
            });

            item.addEventListener('dragleave', function() {
                item.style['border-bottom'] = '';
                item.style['border-top'] = '';
            });

            item.addEventListener('drop', function(e) {
                e.preventDefault();
                item.style['border-bottom'] = '';
                item.style['border-top'] = '';
                if (item !== window.draggedSectionItem && window.draggedSectionItem) {
                    const bounding = item.getBoundingClientRect();
                    const offset = bounding.y + (bounding.height / 2);
                    if (e.clientY - offset > 0) {
                        item.parentNode.insertBefore(window.draggedSectionItem, item.nextSibling);
                    } else {
                        item.parentNode.insertBefore(window.draggedSectionItem, item);
                    }
                }
            });
        });
    };

    window.saveNewSectionOrder = async function() {
        const list = document.getElementById('sections-list');
        if(!list) return;
        
        const orderedIds = Array.from(list.querySelectorAll('.section-item')).map(item => item.getAttribute('data-id'));
        try {
            await fetch('{{ route("dashboard.explore-layout.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ordered_ids: orderedIds })
            });
        } catch (error) {
            console.error('Failed to save order.');
        }
    };

    window.refreshCurrentExplorePartial = function() {
        // Also remove overflow hidden class in case the modal forces a reload while still open
        document.body.classList.remove('overflow-hidden');
        loadPartial('{{ url("/dashboard/explore-layout") }}', document.getElementById('nav-explore-layout-btn'));
    };

    setTimeout(window.initSectionDragAndDrop, 100);
</script>