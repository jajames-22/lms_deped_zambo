<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->first_name }} 👋</h1>
        <p class="text-gray-500 text-sm">Pick up right where you left off and track your academic progress.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="toggleAssessmentModal(true)"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-5 py-2.5 bg-[#a52a2a] text-white text-sm font-bold rounded-xl shadow-sm hover:bg-red-800 transition-all group">
            <i class="fas fa-edit group-hover:rotate-12 transition-transform"></i>
            <span>Take Assessment</span>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div
        class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg shadow-orange-900/20 relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-orange-100 text-xs font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5"><i
                    class="fas fa-chart-pie"></i> Overall Progress</p>
            <h3 class="text-3xl font-black mb-1">{{ $overallProgress }}<span class="text-xl text-orange-200">%</span>
            </h3>
            <p class="text-sm text-orange-100">Of enrolled modules completed</p>
        </div>
        <i class="fas fa-tasks absolute -bottom-4 -right-4 text-7xl text-white/20 transform -rotate-12"></i>
    </div>

    <div
        class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/20 flex items-center justify-between">
        <div>
            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider mb-1">Average Exam Score</p>
            <h3 class="text-3xl font-black">{{ $averageExamScore }}<span class="text-xl text-blue-300">%</span></h3>
        </div>
        <i class="fas fa-spell-check text-5xl text-white/20"></i>
    </div>

    <div
        class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl p-6 text-white shadow-lg shadow-green-900/20 flex items-center justify-between">
        <div>
            <p class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-1">Certificates Awarded</p>
            <h3 class="text-3xl font-black">{{ number_format($completedModulesCount) }}</h3>
        </div>
        <i class="fas fa-medal text-5xl text-white/20"></i>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Active Modules</p>
            <h3 class="text-2xl font-black text-gray-900">{{ number_format($activeModulesCount) }}</h3>
        </div>
        <i class="fas fa-book-reader text-blue-500 text-2xl"></i>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Completed Modules</p>
            <h3 class="text-2xl font-black text-gray-900">{{ number_format($completedModulesCount) }}</h3>
        </div>
        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">

    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Continue Learning</h3>
                <i class="fas fa-book-open text-blue-500"></i>
            </div>

            <div class="p-6 flex flex-col gap-4">
                @forelse($continueLearning as $enrollment)
                    <div
                        class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col sm:flex-row items-center gap-4 hover:border-[#a52a2a]/30 transition group">

                        <div
                            class="w-full sm:w-24 h-24 rounded-lg overflow-hidden bg-white border border-gray-200 flex items-center justify-center flex-shrink-0">
                            @if($enrollment->material->thumbnail)
                                <img src="{{ asset('storage/' . $enrollment->material->thumbnail) }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    alt="Material Thumbnail">
                            @else
                                <i class="fas fa-book text-gray-300 text-3xl"></i>
                            @endif
                        </div>

                        <div class="flex-1 w-full min-w-0">
                            <h4 class="font-bold text-gray-800 truncate">
                                {{ $enrollment->material->title ?? 'Untitled Material' }}</h4>
                            <p class="text-[10px] text-gray-500 mb-3 uppercase tracking-wider">Last accessed:
                                {{ $enrollment->updated_at->diffForHumans() }}</p>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>

                        <a href="{{ route('student.materials.show', $enrollment->material_id) }}"
                            class="bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-gray-100 hover:text-blue-600 transition w-full sm:w-auto text-center shadow-sm whitespace-nowrap block sm:inline-block">
                            Resume <i class="fas fa-arrow-right ml-1"></i>
                        </a>

                    </div>
                @empty
                    <div class="text-center py-8">
                        <div
                            class="w-16 h-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check-double text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">You are all caught up!</h4>
                        <p class="text-sm text-gray-500 mt-1 mb-4">You have no pending lessons.</p>
                        <button
                            onclick="loadPartial('{{ route('dashboard.explore') }}', document.getElementById('nav-explore-btn'))"
                            class="bg-blue-50 text-blue-600 px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-100 transition">
                            Browse Course Library
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="space-y-8">

        <div
            class="bg-gradient-to-br from-gray-50 text-center to-gray-100 rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h4 class="font-bold text-gray-800 mb-1">Ready for something new?</h4>
            <p class="text-xs text-gray-500 mb-4">Discover new subjects and expand your knowledge.</p>
            <button
                onclick="loadPartial('{{ route('dashboard.explore') }}', document.getElementById('nav-explore-btn'))"
                class="text-xs font-bold text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition w-full">
                Explore Library
            </button>
        </div>

    </div>
</div>

<div id="assessmentModal"
    class="fixed inset-0 z-100 opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="toggleAssessmentModal(false)"></div>

    <div id="assessmentModalBox"
        class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform scale-95 transition-all duration-300 border border-gray-100">
        <div class="w-16 h-16 bg-[#a52a2a]/10 text-[#a52a2a] rounded-2xl flex items-center justify-center mb-1 mx-auto">
            <i class="fas fa-key text-2xl"></i>
        </div>

        <div class="text-center mb-4">
            <h3 class="text-2xl font-bold text-gray-900">Assessment Code</h3>
            <p class="text-gray-500 text-sm">Enter the code provided by your teacher.</p>
        </div>

        <form id="verifyCodeForm" action="{{ route('student.assessment.verify') }}" class="space-y-3">
            @csrf

            <div id="codeErrorBox"
                class="hidden bg-red-50 text-red-600 text-sm p-3 rounded-xl border border-red-100 text-center font-bold">
            </div>

            <input type="text" id="assessment_code" name="assessment_code" required placeholder="CODE-123"
                class="w-full px-5 py-4 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-2xl transition-all outline-none text-center font-bold uppercase text-gray-800 text-lg">

            <div class="flex gap-3">
                <button type="button" onclick="toggleAssessmentModal(false)"
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" id="verifySubmitBtn"
                    class="flex-1 flex items-center justify-center px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition">
                    <span>Start</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAssessmentModal(show) {
        const modal = document.getElementById('assessmentModal');
        const modalBox = document.getElementById('assessmentModalBox');
        const errorBox = document.getElementById('codeErrorBox');
        const form = document.getElementById('verifyCodeForm');

        if (show) {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBox.classList.remove('scale-95');
            modalBox.classList.add('scale-100');
        } else {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');
            form.reset();
            errorBox.classList.add('hidden');
        }
    }

    if (window.assessmentSubmitHandler) {
        document.removeEventListener('submit', window.assessmentSubmitHandler);
    }

    window.assessmentSubmitHandler = function (e) {
        if (e.target && e.target.id === 'verifyCodeForm') {
            e.preventDefault();

            var form = e.target;
            var submitBtn = document.getElementById('verifySubmitBtn');
            var errorBox = document.getElementById('codeErrorBox');
            var originalText = submitBtn.innerHTML;
            var csrfToken = form.querySelector('input[name="_token"]').value;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            errorBox.classList.add('hidden');

            var formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.redirect_url) {
                        submitBtn.innerHTML = '<i class="fas fa-check"></i>';
                        window.location.href = data.redirect_url;
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    var errorMessage = "An error occurred. Please try again.";
                    if (error.message) {
                        errorMessage = error.message;
                    } else if (error.errors && error.errors.assessment_code) {
                        errorMessage = error.errors.assessment_code[0];
                    }

                    errorBox.textContent = errorMessage;
                    errorBox.classList.remove('hidden');
                });
        }
    };

    document.addEventListener('submit', window.assessmentSubmitHandler);
</script>