<div class="space-y-6 pb-20 max-w-6xl mx-auto">
    @php 
        $isLive = ($assessment->status === 'published'); 
    @endphp

    <div class="flex items-center justify-between">
        <button onclick="loadPartial('{{ url('/dashboard/assessment') }}', document.getElementById('nav-assessment-btn'))"
            class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Assessments
        </button>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 {{ $isLive ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    @if($isLive)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    @else
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    @endif
                </span>
                {{ $isLive ? 'Published' : 'Draft Mode' }}
            </span>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#a52a2a]/5 to-transparent rounded-bl-full pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest">
                        Grade {{ $assessment->year_level }}
                    </span>
                    <span class="text-gray-400 text-sm font-medium">
                        Created {{ $assessment->created_at->format('M d, Y') }}
                    </span>
                </div>
                
                <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $assessment->title }}</h1>
                <p class="text-gray-600 max-w-3xl leading-relaxed">
                    {{ $assessment->description ?: 'No description provided for this assessment.' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0 md:w-48">
                <button onclick="loadPartial('{{ route('dashboard.assessments.builder', $assessment->id) }}', document.getElementById('nav-assessment-btn'))"
                    class="w-full py-3 px-4 bg-white border-2 border-[#a52a2a] text-[#a52a2a] font-bold rounded-xl hover:bg-[#a52a2a] hover:text-white transition-all flex items-center justify-center gap-2 group shadow-sm">
                    <i class="fas fa-tools group-hover:rotate-12 transition-transform"></i>
                    Edit Content
                </button>

                @if($isLive)
                    <button class="w-full py-3 px-4 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#a52a2a]/20">
                        <i class="fas fa-chart-pie"></i>
                        View Analytics
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden border border-gray-700">
            <i class="fas fa-key absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
            
            <h3 class="text-gray-400 font-bold uppercase tracking-widest text-xs mb-2">Student Access Key</h3>
            <p class="text-sm text-gray-300 mb-6 max-w-md">Share this code with your students. They will use it to enter the exam lobby and start the assessment.</p>
            
            <div class="flex items-center gap-4 bg-black/30 p-2 rounded-2xl w-fit border border-white/10 backdrop-blur-sm">
                <span id="access-key-text" class="text-3xl font-mono font-bold tracking-widest pl-4 pr-2 text-white">
                    {{ $assessment->access_key }}
                </span>
                <button onclick="copyAccessKey('{{ $assessment->access_key }}', this)"
                    class="bg-white/10 hover:bg-white/20 text-white p-3 rounded-xl transition-all flex items-center justify-center group" title="Copy to clipboard">
                    <i class="fas fa-copy group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col justify-center">
            <h3 class="text-gray-500 font-bold uppercase tracking-widest text-xs mb-6">Assessment Structure</h3>
            
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ $assessment->categories_count ?? 0 }}</p>
                        <p class="text-xs font-bold text-gray-500 uppercase">Sections</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ $assessment->questions_count ?? 0 }}</p>
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Questions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Student Access Management</h3>
                <p class="text-sm text-gray-500 mt-1">Only students with LRNs listed below will be able to take this exam.</p>
            </div>
            
            <form id="add-lrn-form" class="flex gap-2 w-full md:w-auto">
                <input type="text" id="student-lrn-input" name="lrn" placeholder="Enter Student LRN" required pattern="[0-9]+" title="Please enter numbers only"
                    class="w-full md:w-64 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm font-medium">
                
                <button type="button" onclick="submitLrn(this)" 
                    class="px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Add
                </button>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">LRN</th>
                        <th class="px-6 py-4">Student Name</th>
                        <th class="px-6 py-4">School</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($whitelistedStudents ?? [] as $access)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 font-mono font-bold text-gray-900">{{ $access->lrn }}</td>
                        
                        <td class="px-6 py-4 font-semibold text-gray-800">
                            @if($access->student)
                                {{-- Change first_name/last_name if your columns are named differently (e.g., $access->student->name) --}}
                                {{ $access->student->first_name ?? '' }} {{ $access->student->last_name ?? '' }}
                            @else
                                <span class="italic text-gray-400 text-xs">No account registered</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-gray-500">
                            @if($access->student && $access->student->school)
                                {{ $access->student->school->name ?? '-' }}
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4">
                            @if($access->status === 'taking_exam')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-100 text-blue-700 text-xs font-bold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    Taking Exam
                                </span>
                            @elseif($access->status === 'finished')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-100 text-green-700 text-xs font-bold">
                                    <i class="fas fa-check text-[10px]"></i>
                                    Finished
                                </span>
                            @elseif($access->status === 'lobby')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-100 text-amber-700 text-xs font-bold">
                                    <i class="fas fa-clock text-[10px]"></i>
                                    In Lobby
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 text-xs font-bold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                    Offline
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-center">
                            <button type="button" onclick="removeLrn('{{ $access->id }}')" class="text-gray-400 hover:text-red-500 transition" title="Remove Access">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-id-card text-3xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium">No students added yet.</p>
                                <p class="text-xs mt-1">Enter an LRN above to grant access.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 mt-8">
        <h3 class="text-red-800 font-bold mb-2">Danger Zone</h3>
        <p class="text-sm text-red-600 mb-4">Deleting this assessment will permanently remove it and all associated student submissions. This action cannot be undone.</p>
        
        <button onclick="window.deleteAssessmentFromList('{{ $assessment->id }}', '{{ route('dashboard.assessments.destroy', $assessment->id) }}')" 
            class="px-6 py-2.5 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
            Delete Assessment
        </button>
    </div>
</div>

<script>
    // Simple copy to clipboard function
    function copyAccessKey(key, btnElement) {
        navigator.clipboard.writeText(key).then(() => {
            const icon = btnElement.querySelector('i');
            icon.className = 'fas fa-check text-green-400';
            btnElement.classList.add('bg-green-500/20');
            
            setTimeout(() => {
                icon.className = 'fas fa-copy group-hover:scale-110 transition-transform';
                btnElement.classList.remove('bg-green-500/20');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            alert('Failed to copy access key.');
        });
    }

    

    // --- NEW: Remove LRN via AJAX ---
    async function removeLrn(accessId) {
        if (!confirm('Are you sure you want to revoke this student\'s access?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        try {
            const response = await fetch(`/dashboard/assessments/access/${accessId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Instantly reload this partial to remove the row from the table
                loadPartial('{{ route("dashboard.assessments.manage", $assessment->id) }}', document.getElementById('nav-assessment-btn'));
            } else {
                alert(data.message || 'Failed to remove student access.');
            }
        } catch (error) {
            alert('A network error occurred while removing the LRN.');
            console.error(error);
        }
    }

    async function submitLrn(btn) {
        const lrnInput = document.getElementById('student-lrn-input');
        const lrnValue = lrnInput.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        // Basic frontend validation before we even hit the server
        if (!lrnValue || isNaN(lrnValue)) {
            alert('Please enter a valid numeric LRN.');
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("dashboard.assessments.access.add", $assessment->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ lrn: lrnValue })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Success!
                lrnInput.value = ''; 
                loadPartial('{{ route("dashboard.assessments.manage", $assessment->id) }}', document.getElementById('nav-assessment-btn'));
            } else if (response.status === 422) {
                // Validation Error
                alert("Validation Error: " + data.errors.lrn[0]);
            } else {
                // Other Error
                alert(data.message || 'Failed to add LRN.');
            }
        } catch (error) {
            alert('A network error occurred. Check the console.');
            console.error(error);
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }


</script>