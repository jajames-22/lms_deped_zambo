<div class="space-y-6 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Assessment Management</h1>
            <p class="text-gray-500 text-sm">Data-driven test to measure students learning progress.</p>
        </div>

        <button
            onclick="loadPartial('{{ route('dashboard.assessments.create') }}', document.getElementById('nav-assessment-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-[#a52a2a]/20 hover:bg-red-800 transition-all active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Create New Test</span>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center space-x-1 bg-gray-200/50 p-1 rounded-xl w-fit shrink-0">
            <button onclick="AssessmentManager.filter('all', this)"
                class="assessment-tab px-6 py-2 text-sm font-bold rounded-lg transition-all bg-white text-[#a52a2a] shadow-sm">All</button>
            <button onclick="AssessmentManager.filter('live', this)"
                class="assessment-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700">Live</button>
            <button onclick="AssessmentManager.filter('draft', this)"
                class="assessment-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700">Drafts</button>
        </div>

        <div class="relative w-full max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="assessment-search" onkeyup="AssessmentManager.search()"
                placeholder="Search test by title..."
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
    </div>

    <div id="assessment-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 items-stretch relative">
        @forelse($assessments as $assessment)
            @php 
                $isLive = ($assessment->status === 'published'); 
                
                // Bulletproof Database Counts
                $dbCatCount = $assessment->categories_count ?? $assessment->categories()->count();
                $dbQCount = $assessment->questions_count ?? $assessment->questions()->count();

                $displayCatCount = $dbCatCount;
                $displayQCount = $dbQCount;

                // Dynamic Draft JSON counting
                if (!$isLive && !empty($assessment->draft_json)) {
                    $draftData = json_decode($assessment->draft_json, true);
                    if (is_array($draftData) && isset($draftData['categories'])) {
                        $jsonCatCount = count($draftData['categories']);
                        $jsonQCount = 0;
                        foreach ($draftData['categories'] as $cat) {
                            if (isset($cat['questions']) && is_array($cat['questions'])) {
                                $jsonQCount += count($cat['questions']);
                            }
                        }
                        $displayCatCount = $jsonCatCount > 0 ? $jsonCatCount : $dbCatCount;
                        $displayQCount = $jsonQCount > 0 ? $jsonQCount : $dbQCount;
                    }
                }
            @endphp

            <div id="assessment-card-{{ $assessment->id }}"
                onclick="loadPartial('{{ route('dashboard.assessments.manage', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                class="assessment-card {{ $isLive ? 'live' : 'draft' }} flex flex-col h-full bg-white rounded-2xl border border-gray-200 {{ $isLive ? 'border-t-green-500 border-t-4' : 'border-t-amber-400 border-t-4' }} shadow-sm hover:shadow-lg transition-all duration-300 group overflow-hidden relative cursor-pointer">

                <button
                    onclick="event.stopPropagation(); AssessmentManager.delete('{{ $assessment->id }}', '{{ route('dashboard.assessments.destroy', $assessment->id) }}')"
                    class="absolute top-3 right-3 h-7 w-7 rounded-full bg-gray-50 text-gray-400 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center hover:bg-red-100 hover:text-red-600 z-10"
                    title="Delete Assessment">
                    <i class="fas fa-trash-alt text-[11px]"></i>
                </button>

                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-3 pr-6">
                        <div class="flex items-center gap-2">
                            <div
                                class="{{ $isLive ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }} p-2 rounded-lg flex items-center justify-center">
                                <i class="fas {{ $isLive ? 'fa-file-signature' : 'fa-tools' }} text-lg"></i>
                            </div>
                            <span
                                class="px-2 py-0.5 {{ $isLive ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} text-[9px] font-bold rounded uppercase tracking-wide">
                                {{ $isLive ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>

                    <h4 class="test-title text-base font-bold text-gray-900 mb-1 line-clamp-1"
                        title="{{ $assessment->title }}">{{ $assessment->title }}</h4>

                    <p class="text-xs text-gray-500 mb-3 line-clamp-2 flex-1 leading-relaxed">
                        {{ $assessment->description ?? 'No description provided for this assessment.' }}
                    </p>

                    {{-- STATS BLOCK --}}
                    <div class="bg-gray-50 rounded-xl p-3 mb-2 space-y-1.5 text-[11px] text-gray-600 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-500"><i class="fas fa-key w-3.5 text-center"></i> Access Key:</span>
                            @if($assessment->access_key)
                                <b class="tracking-widest text-[#a52a2a] font-mono text-xs">{{ $assessment->access_key }}</b>
                            @else
                                <b class="text-gray-400 italic">Pending</b>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-500"><i class="fas fa-layer-group w-3.5 text-center"></i> Sections:</span>
                            <b class="text-gray-900 text-xs">{{ $displayCatCount }}</b>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-500"><i class="fas fa-list-ol w-3.5 text-center"></i> Total Items:</span>
                            <b class="text-gray-900 text-xs">{{ $displayQCount }}</b>
                        </div>
                    </div>

                    {{-- DRAFT INDICATOR --}}
                    @if(!$isLive)
                        <div class="bg-amber-50/50 rounded-xl p-2 mb-2 text-[11px] font-medium text-amber-600/80 border border-amber-100/50 flex items-center justify-center border-dashed">
                            <i class="fas fa-pencil-ruler mr-1.5"></i> Continue building this test
                        </div>
                    @endif

                    <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                        <button
                            onclick="event.stopPropagation(); loadPartial('{{ route('dashboard.assessments.builder', $assessment->id) }}',document.getElementById('nav-assessment-btn'))"
                            class="flex-1 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] transition-all shadow-sm">
                            <i class="fas {{ $isLive ? 'fa-edit' : 'fa-play' }} mr-1"></i>
                            {{ $isLive ? 'Edit' : 'Resume' }}
                        </button>

                        @if($isLive)
                            <button
                                onclick="event.stopPropagation(); loadPartial('{{ route('dashboard.assessments.analytics', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                                id="analytics-btn"
                                class="flex-1 py-2 bg-[#a52a2a] text-white text-xs font-bold rounded-xl hover:bg-red-800 transition-all shadow-sm shadow-[#a52a2a]/20">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Analytics
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="h-14 w-14 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-3">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">No assessment found, try adding one!</h3>
            </div>
        @endforelse

        <div id="dynamic-empty-state" style="display: none;"
            class="col-span-full py-12 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
            <div class="h-14 w-14 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-3">
                <i class="fas fa-search-minus text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">No assessment found, try adding one!</h3>
        </div>
    </div>
</div>

<div id="status-modal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="document.getElementById('status-modal').classList.add('hidden')"></div>

    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-6 text-center">

            <div id="status-modal-icon"
                class="h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"></div>

            <h3 id="status-modal-title" class="text-xl font-bold text-gray-900 mb-2">Title</h3>
            <p id="status-modal-message" class="text-gray-500 text-sm mb-6">Message goes here.</p>

            <div class="flex gap-3 mt-2">
                <button id="status-modal-cancel-btn" type="button"
                    class="w-full py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition active:scale-95 hidden">
                    Cancel
                </button>
                <button id="status-modal-btn" type="button"
                    class="w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.AssessmentManager = window.AssessmentManager || {};

    AssessmentManager.currentStatus = 'all';

    AssessmentManager.showModal = function (type, title, message, callback = null) {
        const modal = document.getElementById('status-modal');
        if (!modal) return alert(message);

        const iconContainer = document.getElementById('status-modal-icon');
        const titleEl = document.getElementById('status-modal-title');
        const msgEl = document.getElementById('status-modal-message');
        const btn = document.getElementById('status-modal-btn');
        const cancelBtn = document.getElementById('status-modal-cancel-btn');

        titleEl.innerText = title;
        msgEl.innerText = message;
        iconContainer.className = 'h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
        btn.className = 'w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md';
        cancelBtn.classList.add('hidden');
        btn.innerText = 'OK';
        cancelBtn.onclick = null;
        btn.onclick = null;

        if (type === 'error') {
            iconContainer.classList.add('bg-red-50', 'text-red-500');
            iconContainer.innerHTML = '<i class="fas fa-times-circle"></i>';
            btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-600/20');
        } else if (type === 'confirm') {
            iconContainer.classList.add('bg-red-50', 'text-red-500');
            iconContainer.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-600/20');
            btn.innerText = 'Yes, Delete';
            cancelBtn.classList.remove('hidden');
            cancelBtn.onclick = () => modal.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        btn.onclick = () => {
            modal.classList.add('hidden');
            if (callback) callback();
        };
    };

    AssessmentManager.delete = function (id, url) {
        AssessmentManager.showModal('confirm', 'Delete Assessment?', 'Are you sure you want to delete this assessment? This action cannot be undone.', async () => {

            const card = document.getElementById('assessment-card-' + id);
            if (!card) return;

            const btn = card.querySelector('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
            btn.disabled = true;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    card.style.transform = 'scale(0.95)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        AssessmentManager.applyFilters();
                    }, 300);
                } else {
                    AssessmentManager.showModal('error', 'Delete Failed', 'The server returned an error while deleting.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (e) {
                AssessmentManager.showModal('error', 'Network Error', 'Could not delete the assessment. Please check your connection.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }

        });
    };

    AssessmentManager.filter = function (status, btnElement) {
        AssessmentManager.currentStatus = status;

        document.querySelectorAll('.assessment-tab').forEach(tab => {
            tab.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm');
            tab.classList.add('text-gray-500', 'hover:text-gray-700');
        });

        btnElement.classList.remove('text-gray-500', 'hover:text-gray-700');
        btnElement.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm');

        AssessmentManager.applyFilters();
    };

    AssessmentManager.search = function () {
        AssessmentManager.applyFilters();
    };

    AssessmentManager.applyFilters = function () {
        const query = document.getElementById('assessment-search').value.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.assessment-card').forEach(card => {
            const title = card.querySelector('.test-title').innerText.toLowerCase();
            const matchesTab = (AssessmentManager.currentStatus === 'all' || card.classList.contains(AssessmentManager.currentStatus));

            if (matchesTab && title.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const dynamicEmptyState = document.getElementById('dynamic-empty-state');
        if (dynamicEmptyState) {
            const totalCards = document.querySelectorAll('.assessment-card').length;
            if (totalCards > 0 && visibleCount === 0) {
                dynamicEmptyState.style.display = 'flex';
            } else {
                dynamicEmptyState.style.display = 'none';
            }
        }
    };
</script>