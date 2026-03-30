

<div class="space-y-6 pb-20 w-full mx-auto relative mt-8">
    
    <div class="flex items-center justify-between">
        <button onclick="loadPartial('{{ url('/dashboard/student/home') }}')"
            class="flex items-center text-gray-500 hover:text-[#a52a2a] font-semibold transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Dashboard
        </button>

        <div class="flex items-center gap-4">
            <span class="px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded-lg uppercase tracking-wider flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Enrolled
            </span>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#a52a2a]/5 to-transparent rounded-bl-full pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest">
                        Learning Module
                    </span>
                </div>
                <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $material->title }}</h1>
                <p class="text-gray-600 max-w-3xl leading-relaxed">
                    {{ $material->description ?: 'No description provided.' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-gradient-to-r from-[#a52a2a] to-red-900 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden border border-gray-700">
            <i class="fas fa-book-reader absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
            <h3 class="text-white font-bold uppercase tracking-widest text-xs mb-2">Continue Learning</h3>
            <p class="text-sm text-red-100 mb-6 max-w-md">Access your lessons and complete assessments to track your progress.</p>
            <button class="px-6 py-3 bg-white text-[#a52a2a] font-bold rounded-xl hover:bg-gray-100 transition-all flex items-center gap-2">
                <i class="fas fa-play"></i> Start First Lesson
            </button>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col justify-center">
            <h3 class="text-gray-500 font-bold uppercase tracking-widest text-xs mb-6">Module Content</h3>
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ count($lessons) }}</p>
                        <p class="text-xs font-bold text-gray-500 uppercase">Lessons</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ count($exams) }}</p>
                        <p class="text-xs font-bold text-gray-500 uppercase">Exams</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>