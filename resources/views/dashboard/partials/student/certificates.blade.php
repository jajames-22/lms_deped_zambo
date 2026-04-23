<div class="max-w-7xl mx-auto pb-24 relative">

    {{-- HEADER SECTION --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Certificates</h1>
        <p class="text-gray-500 text-sm">Review and download the certificates you have earned.</p>
    </div>

    {{-- CERTIFICATES GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        @forelse($completedEnrollments as $enrollment)
            @php $material = $enrollment->material; @endphp

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-[#a52a2a]/30 transition-all duration-300 group flex flex-col overflow-hidden cursor-pointer"
                onclick="sessionStorage.setItem('lastActiveTab', '{{ route('student.certificates.index') }}'); sessionStorage.setItem('lastActiveBtn', 'nav-certificates-btn'); window.location.href = '{{ route('student.materials.achieved', ['hashid' => \Vinkla\Hashids\Facades\Hashids::encode($enrollment->id)]) }}'">
                {{-- Certificate Top Graphic --}}
                <div
                    class="h-32 bg-gradient-to-br from-[#a52a2a] to-red-900 relative flex items-center justify-center overflow-hidden">
                    <div
                        class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20">
                    </div>
                    <i
                        class="fas fa-award text-5xl text-yellow-400 drop-shadow-md z-10 transform group-hover:scale-110 transition-transform duration-500"></i>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex flex-col flex-1 relative">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Module Completed</div>
                    <h3
                        class="font-bold text-gray-900 text-lg leading-tight line-clamp-2 group-hover:text-[#a52a2a] transition-colors mb-4">
                        {{ $material->title }}
                    </h3>

                    <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                        <div class="text-xs text-gray-500 font-medium">
                            <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                            {{-- Fallback to updated_at if completed_at is null --}}
                            {{ $enrollment->completed_at ? $enrollment->completed_at->format('M d, Y') : $enrollment->updated_at->format('M d, Y') }}
                        </div>
                        <span
                            class="text-[#a52a2a] text-sm font-bold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                            View <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </div>
                </div>
            </div>

        @empty
            {{-- EMPTY STATE --}}
            <div
                class="col-span-full py-20 px-4 text-center bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col items-center justify-center">
                <div
                    class="w-24 h-24 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center text-4xl mb-4 shadow-inner border border-gray-100">
                    <i class="fas fa-medal"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">No Certificates Yet</h3>
                <p class="text-gray-500 max-w-md mx-auto mb-8">You haven't completed any modules yet. Keep learning and
                    finish your enrolled modules to earn certificates!</p>

                <button
                    onclick="loadPartial('{{ url('/dashboard/enrolled') }}', document.getElementById('nav-enrolled-btn'))"
                    class="px-8 py-3 bg-[#a52a2a] text-white font-bold rounded-xl hover:bg-red-900 transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-book-open"></i> Go to My Modules
                </button>
            </div>
        @endforelse
    </div>
</div>