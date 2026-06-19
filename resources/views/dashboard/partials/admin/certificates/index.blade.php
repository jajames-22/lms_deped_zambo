{{-- ================================================================
     ADMIN — CERTIFICATE TEMPLATES INDEX
     ================================================================ --}}
<div class="max-w-6xl mx-auto space-y-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Certificate Templates</h1>
            <p class="text-gray-500 mt-1 text-sm">Manage and customise the certificate design used by students.</p>
        </div>
        <button onclick="loadPartial('{{ route('dashboard.cert-templates.create') }}', document.getElementById('nav-certificates-btn'))"
            class="flex items-center gap-2 px-5 py-2.5 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition shadow-lg shadow-[#a52a2a]/30 text-sm shrink-0">
            <i class="fas fa-plus"></i> Add New Template
        </button>
    </div>

    {{-- TOAST --}}
    <div id="cert-toast" class="fixed bg-gray-900 bottom-6 right-6 z-[9999] transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl font-medium text-sm text-white">
        <i id="cert-toast-icon" class="fas fa-check-circle text-lg"></i>
        <span id="cert-toast-msg"></span>
    </div>

    {{-- TEMPLATE GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="template-grid">
        @foreach ($templates as $tpl)
            <div class="group bg-white rounded-2xl border-2 {{ $tpl->is_active ? 'border-[#a52a2a] shadow-xl shadow-[#a52a2a]/10' : 'border-gray-100 hover:border-gray-300' }} transition-all overflow-hidden flex flex-col" id="tpl-card-{{ $tpl->id }}">

                {{-- Thumbnail --}}
                <div class="relative h-44 bg-gradient-to-br from-gray-50 to-gray-200 overflow-hidden flex items-center justify-center p-2">
                    @if($tpl->background_image)
                        <img src="{{ asset('storage/' . $tpl->background_image) }}" alt="{{ $tpl->name }}"
                            class="w-full h-full object-cover rounded-xl shadow-sm opacity-95 group-hover:scale-105 transition-transform duration-500">
                    @else
                        {{-- Default template preview --}}
                        <img src="{{ asset('images/default-cert-thumbnail.png') }}" alt="Default Certificate"
                            class="w-full h-full object-cover rounded-xl shadow-sm opacity-95 group-hover:scale-105 transition-transform duration-500">
                    @endif

                    {{-- Active Badge --}}
                    @if($tpl->is_active)
                        <div class="absolute top-3 left-3 px-2.5 py-1 bg-[#a52a2a] text-white border border-white/20 text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg flex items-center gap-1">
                            <i class="fas fa-star text-yellow-300 text-[9px]"></i> ACTIVE
                        </div>
                    @endif
                    @if($tpl->is_default)
                        <div class="absolute top-3 right-3 px-3 py-1.5 bg-blue-600 text-white border border-white/20 text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg">
                            DEFAULT
                        </div>
                    @endif
                </div>

                {{-- Card Body --}}
                <div class="flex-1 p-4 flex flex-col gap-3">
                    <div>
                        <h3 class="font-black text-gray-900 text-base leading-tight">{{ $tpl->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $tpl->updated_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-auto">
                        {{-- Set Active --}}
                        @if(!$tpl->is_active)
                            <button onclick="setActive({{ $tpl->id }})"
                                class="flex-1 px-3 py-2 text-xs font-bold text-[#a52a2a] bg-[#a52a2a]/10 hover:bg-[#a52a2a] hover:text-white rounded-lg transition border border-[#a52a2a]/20">
                                <i class="fas fa-star mr-1"></i> Set Active
                            </button>
                        @else
                            <span class="flex-1 px-3 py-2 text-xs font-bold text-green-700 bg-green-50 rounded-lg border border-green-200 text-center">
                                <i class="fas fa-check-circle mr-1"></i> Active
                            </span>
                        @endif

                        {{-- Edit --}}
                        @if(!$tpl->is_default)
                            <button onclick="loadPartial('{{ route('dashboard.cert-templates.edit', $tpl->id) }}', document.getElementById('nav-certificates-btn'))"
                                class="px-3 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        @endif

                        {{-- Delete --}}
                        @if(!$tpl->is_default)
                            <button onclick="deleteTemplate({{ $tpl->id }})"
                                class="px-3 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>

{{-- DELETE CONFIRM MODAL --}}
<div id="delete-cert-modal" class="hidden fixed inset-0 z-[9998] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-2xl max-w-sm w-full p-7 shadow-2xl text-center z-10">
        <div class="w-14 h-14 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 class="text-lg font-black text-gray-900 mb-2">Delete Template?</h3>
        <p class="text-sm text-gray-500 mb-6">This action cannot be undone. The default template will be used instead.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition text-sm">Cancel</button>
            <button id="confirm-delete-btn" class="flex-1 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition text-sm">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let _pendingDeleteId = null;

function showToast(message, success = true) {
    const toast = document.getElementById('cert-toast');
    const msg   = document.getElementById('cert-toast-msg');
    const icon  = document.getElementById('cert-toast-icon');
    
    icon.className  = `fas ${success ? 'fa-check-circle' : 'fa-exclamation-circle'} text-lg`;
    msg.textContent = message;
    
    // Clear previous color classes
    toast.classList.remove('bg-green-600', 'bg-[#a52a2a]', 'bg-gray-900');
    toast.classList.add(success ? 'bg-green-600' : 'bg-[#a52a2a]');
    
    toast.classList.remove('translate-y-24', 'opacity-0');
    
    setTimeout(() => {
        toast.classList.add('translate-y-24', 'opacity-0');
    }, 3500);
}

function setActive(id) {
    fetch(`/dashboard/certificate-templates/${id}/activate`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        showToast(data.message, data.success);
        if (data.success) setTimeout(() => loadPartial('{{ route('dashboard.cert-templates.index') }}', document.getElementById('nav-certificates-btn')), 1000);
    });
}

function deleteTemplate(id) {
    _pendingDeleteId = id;
    document.getElementById('delete-cert-modal').classList.remove('hidden');
    document.getElementById('confirm-delete-btn').onclick = confirmDelete;
}

function closeDeleteModal() {
    document.getElementById('delete-cert-modal').classList.add('hidden');
    _pendingDeleteId = null;
}

function confirmDelete() {
    if (!_pendingDeleteId) return;
    fetch(`/dashboard/certificate-templates/${_pendingDeleteId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        closeDeleteModal();
        showToast(data.message, data.success);
        if (data.success) {
            const card = document.getElementById(`tpl-card-${_pendingDeleteId}`);
            if (card) card.remove();
        }
    });
}
</script>
