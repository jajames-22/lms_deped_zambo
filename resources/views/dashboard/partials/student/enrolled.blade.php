<div class="max-w-7xl mx-auto pb-24 relative">

    {{-- HEADER SECTION --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Learning Modules</h1>
            <p class="text-gray-500 text-sm">Pick up right where you left off and track your progress.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button onclick="loadPartial('{{ url('/dashboard/explore') }}', document.getElementById('nav-explore-btn'))" 
                class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
                <i class="fas fa-compass text-gray-400"></i> Explore
            </button>
            <button onclick="openJoinModal()" 
                class="px-5 py-2.5 bg-[#a52a2a] text-white text-sm font-bold rounded-xl hover:bg-red-900 transition-all shadow-sm flex items-center gap-2">
                <i class="fas fa-plus"></i> Join Class
            </button>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="flex gap-6 border-b border-gray-200 mb-8 px-2">
        <button onclick="switchTab('active')" id="tab-btn-active" class="pb-3 border-b-2 border-[#a52a2a] text-[#a52a2a] font-bold text-sm transition-all">
            Active Modules <span class="ml-1 bg-red-50 text-[#a52a2a] py-0.5 px-2 rounded-full text-xs">{{ count($activeEnrollments ?? []) }}</span>
        </button>
        <button onclick="switchTab('dropped')" id="tab-btn-dropped" class="pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-800 font-bold text-sm transition-all">
            Dropped Modules <span class="ml-1 bg-gray-100 text-gray-500 py-0.5 px-2 rounded-full text-xs">{{ count($droppedAccesses ?? []) }}</span>
        </button>
    </div>

    {{-- ACTIVE TAB CONTENT --}}
    <div id="tab-content-active" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-2 transition-opacity duration-300">
        @forelse($activeEnrollments ?? [] as $enrollment)
            @php 
                $material = $enrollment->material; 
                if(!$material) continue; 
            @endphp
            
            {{-- Active cards just redirect normally --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-[#a52a2a]/30 transition-all duration-300 group flex flex-col overflow-hidden cursor-pointer relative"
                onclick="window.location.href = '{{ route('student.materials.show', $material->hashid) }}'">
                
                {{-- Status Badge --}}
                <div class="absolute top-3 right-3 z-10">
                    <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-gray-700 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm flex items-center gap-1.5 border border-gray-100">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $enrollment->status === 'completed' ? 'bg-green-400' : 'bg-amber-400' }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $enrollment->status === 'completed' ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                        </span>
                        {{ str_replace('_', ' ', $enrollment->status ?? 'In Progress') }}
                    </span>
                </div>

                {{-- Thumbnail --}}
                <div class="relative w-full aspect-video overflow-hidden bg-gray-100">
                    <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-2 group-hover:text-[#a52a2a] transition-colors mb-2">
                        {{ $material->title }}
                    </h3>
                    
                    {{-- INSTRUCTOR --}}
                    <p class="text-xs text-gray-500 font-medium truncate flex items-center gap-1.5 mb-4">
                        <i class="fas fa-chalkboard-user text-gray-400"></i> 
                        {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                    </p>

                    <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-1 rounded font-bold uppercase tracking-wider">
                            {{ $material->tags->first()->name ?? 'General' }}
                        </span>
                        
                        <span class="text-[#a52a2a] text-sm font-bold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                            Continue <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 px-4 text-center bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col items-center justify-center">
                <div class="w-24 h-24 bg-red-50 text-[#a52a2a] rounded-full flex items-center justify-center text-4xl mb-4 shadow-inner">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">No Active Modules</h3>
                <p class="text-gray-500 max-w-md mx-auto mb-8">You aren't actively enrolled in any modules right now. Explore the catalog or use an access code to get started.</p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <button onclick="loadPartial('{{ url('/dashboard/explore') }}', document.getElementById('nav-explore-btn'))" 
                        class="px-8 py-3 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                        Browse Catalog
                    </button>
                    <button onclick="openJoinModal()" 
                        class="px-8 py-3 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fas fa-key"></i> Enter Code
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- DROPPED TAB CONTENT --}}
    <div id="tab-content-dropped" class="hidden grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-2 transition-opacity duration-300 opacity-0">
        @forelse($droppedAccesses ?? [] as $access)
            @php 
                $material = $access->material; 
                if(!$material) continue; 
            @endphp
            
            {{-- Dropped cards trigger the Rejoin modal if Private, or Redirect if Public --}}
            <div class="bg-gray-50/80 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col overflow-hidden cursor-pointer relative grayscale-[0.3]"
                @if(!$material->is_public && $access->status === 'dropped')
                    onclick="openRejoinModal('{{ addslashes($material->title) }}')"
                @else
                    onclick="window.location.href = '{{ route('student.materials.show', $material->hashid) }}'"
                @endif
            >
                {{-- Status Badge --}}
                <div class="absolute top-3 right-3 z-10">
                    <span class="px-2.5 py-1 bg-white text-gray-500 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm flex items-center gap-1.5 border border-gray-200">
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-400"></span>
                        DROPPED
                    </span>
                </div>

                {{-- Thumbnail --}}
                <div class="relative w-full aspect-video overflow-hidden bg-gray-200">
                    <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 mix-blend-multiply opacity-80">
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-600 text-lg leading-tight line-clamp-2 mb-2">
                        {{ $material->title }}
                    </h3>
                    
                    {{-- INSTRUCTOR --}}
                    <p class="text-xs text-gray-500 font-medium truncate flex items-center gap-1.5 mb-4">
                        <i class="fas fa-chalkboard-user text-gray-400"></i> 
                        {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                    </p>

                    <div class="mt-auto pt-4 border-t border-gray-200 flex items-center justify-between">
                        <span class="text-[10px] bg-white border border-gray-200 text-gray-500 px-2 py-1 rounded font-bold uppercase tracking-wider">
                            {{ $material->tags->first()->name ?? 'General' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center">
                <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-1">No Dropped Modules</h3>
                <p class="text-gray-500 text-sm">You haven't dropped out of any modules.</p>
            </div>
        @endforelse
    </div>


    {{-- MODAL: JOIN WITH ACCESS CODE --}}
    <div id="join-code-modal" class="fixed inset-0 z-[100] hidden h-full">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeJoinModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 p-8 relative">
                
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-[#a52a2a]/10 to-transparent rounded-bl-full pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-14 w-14 bg-red-50 text-[#a52a2a] border border-red-100 rounded-2xl flex items-center justify-center text-2xl shadow-sm mb-4">
                            <i class="fas fa-unlock-keyhole"></i>
                        </div>
                        <button onclick="closeJoinModal()" class="text-gray-400 hover:text-[#a52a2a] bg-gray-50 hover:bg-red-50 h-8 w-8 rounded-full flex items-center justify-center transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <h3 id="join-modal-title" class="text-2xl font-black text-gray-900 mb-2">Join a Module</h3>
                    <p id="join-modal-desc" class="text-gray-500 text-sm mb-6">Enter the access code provided by your instructor to instantly enroll.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <input type="text" id="join-code-input" placeholder="e.g. A1B2C3" maxlength="10"
                                   class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl outline-none font-mono uppercase text-lg text-center tracking-[0.3em] transition-colors focus:border-[#a52a2a] focus:ring-2 focus:ring-[#a52a2a]/20">
                            
                            <p id="join-code-error" class="text-sm text-red-500 text-center font-bold mt-2 hidden"></p>
                        </div>
                        
                        <button type="button" id="submit-join-btn" onclick="submitJoinCode()"
                                class="w-full py-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-right-to-bracket"></i> Enroll Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab Switching Logic
    function switchTab(tabName) {
        const activeTabBtn = document.getElementById('tab-btn-active');
        const droppedTabBtn = document.getElementById('tab-btn-dropped');
        const activeContent = document.getElementById('tab-content-active');
        const droppedContent = document.getElementById('tab-content-dropped');

        if (tabName === 'active') {
            // Update Buttons
            activeTabBtn.classList.replace('border-transparent', 'border-[#a52a2a]');
            activeTabBtn.classList.replace('text-gray-500', 'text-[#a52a2a]');
            
            droppedTabBtn.classList.replace('border-[#a52a2a]', 'border-transparent');
            droppedTabBtn.classList.replace('text-[#a52a2a]', 'text-gray-500');

            // Hide Dropped, Show Active
            droppedContent.classList.add('opacity-0');
            setTimeout(() => {
                droppedContent.classList.remove('grid');
                droppedContent.classList.add('hidden');
                
                activeContent.classList.remove('hidden');
                activeContent.classList.add('grid');
                setTimeout(() => activeContent.classList.remove('opacity-0'), 50);
            }, 300);

        } else if (tabName === 'dropped') {
            // Update Buttons
            droppedTabBtn.classList.replace('border-transparent', 'border-[#a52a2a]');
            droppedTabBtn.classList.replace('text-gray-500', 'text-[#a52a2a]');
            
            activeTabBtn.classList.replace('border-[#a52a2a]', 'border-transparent');
            activeTabBtn.classList.replace('text-[#a52a2a]', 'text-gray-500');

            // Hide Active, Show Dropped
            activeContent.classList.add('opacity-0');
            setTimeout(() => {
                activeContent.classList.remove('grid');
                activeContent.classList.add('hidden');
                
                droppedContent.classList.remove('hidden');
                droppedContent.classList.add('grid');
                setTimeout(() => droppedContent.classList.remove('opacity-0'), 50);
            }, 300);
        }
    }

    // Modal Logic
    function openJoinModal() {
        document.getElementById('join-code-modal').classList.remove('hidden');
        setTimeout(() => { document.getElementById('join-code-input').focus(); }, 100);
    }

    function closeJoinModal() {
        document.getElementById('join-code-modal').classList.add('hidden');
        
        const input = document.getElementById('join-code-input');
        const errorMsg = document.getElementById('join-code-error');
        
        input.value = '';
        input.classList.remove('border-red-500', 'bg-red-50');
        errorMsg.classList.add('hidden');
        errorMsg.innerText = '';
    }

    document.getElementById('join-code-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') submitJoinCode();
    });

    async function submitJoinCode() {
        const input = document.getElementById('join-code-input');
        const btn = document.getElementById('submit-join-btn');
        const errorMsg = document.getElementById('join-code-error');
        const code = input.value.trim().toUpperCase();
        const originalHtml = btn.innerHTML;
        
        errorMsg.classList.add('hidden');
        input.classList.remove('border-red-500', 'bg-red-50');
        
        if(!code) {
            errorMsg.innerText = 'Please enter an access code.';
            errorMsg.classList.remove('hidden');
            input.classList.add('border-red-500', 'bg-red-50'); 
            input.focus();
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("student.enroll.code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ access_code: code })
            });
            
            const data = await response.json();
            
            if(response.ok && data.success) {
                closeJoinModal();
                if (typeof showSnackbar === 'function') showSnackbar('Successfully enrolled! Loading...', 'success');
                
                // Force a hard full-page redirect to match acceptInvitation
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
                
            } else {
                errorMsg.innerText = data.message || 'Invalid code.';
                errorMsg.classList.remove('hidden');
                input.classList.add('border-red-500', 'bg-red-50');
                input.focus();
            }
        } catch (error) {
            console.error(error);
            errorMsg.innerText = 'A network error occurred. Please check your connection.';
            errorMsg.classList.remove('hidden');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    // --- Add this new function ---
    function openRejoinModal(courseTitle) {
        document.getElementById('join-modal-title').innerText = 'Rejoin Module';
        document.getElementById('join-modal-desc').innerHTML = `Enter the access code to rejoin <strong>${courseTitle}</strong>.`;
        openJoinModal();
    }

    // --- Update your existing closeJoinModal function to this ---
    function closeJoinModal() {
        document.getElementById('join-code-modal').classList.add('hidden');
        
        const input = document.getElementById('join-code-input');
        const errorMsg = document.getElementById('join-code-error');
        
        input.value = '';
        input.classList.remove('border-red-500', 'bg-red-50');
        errorMsg.classList.add('hidden');
        errorMsg.innerText = '';

        // Reset the modal text back to default after animation finishes
        setTimeout(() => {
            document.getElementById('join-modal-title').innerText = 'Join a Module';
            document.getElementById('join-modal-desc').innerText = 'Enter the access code provided by your instructor to instantly enroll.';
        }, 300);
    }
</script>