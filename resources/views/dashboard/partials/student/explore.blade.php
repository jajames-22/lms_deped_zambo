<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>

<div class="max-w-7xl mx-auto space-y-12 pb-24">
    
    {{-- 1. FEATURED BANNER (Most Viewed Public Material) --}}
    @if($featuredMaterial)
    <div class="relative w-full h-80 md:h-[450px] rounded-2xl overflow-hidden shadow-2xl group cursor-pointer"
         onclick="loadPartial('{{ route('dashboard.materials.manage', $featuredMaterial->id) }}', document.getElementById('nav-materials-btn'))">
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/60 to-transparent z-10"></div>
        <img src="{{ $featuredMaterial->thumbnail ? asset('storage/' . $featuredMaterial->thumbnail) : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=1000' }}" 
             class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-1000">
        
        <div class="absolute inset-0 z-20 flex flex-col justify-end p-8 md:p-16 w-full md:w-3/4">
            <span class="px-3 py-1 bg-[#a52a2a] text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-md w-max mb-4">Trending Now</span>
            <h1 class="text-4xl md:text-6xl font-black text-white mb-4 leading-none">{{ $featuredMaterial->title }}</h1>
            <p class="text-gray-300 text-sm md:text-lg mb-8 line-clamp-2 max-w-2xl">{{ $featuredMaterial->description }}</p>
            
            <div class="flex items-center gap-4">
                <button class="bg-white text-black hover:bg-gray-200 font-bold py-3 px-8 rounded-lg transition-all flex items-center gap-2">
                    <i class="fas fa-play"></i> Start Learning
                </button>
                <button class="bg-white/10 hover:bg-white/20 text-white font-bold py-3 px-6 rounded-lg backdrop-blur-md transition-all border border-white/20">
                    View Details
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. CATEGORY: LOGIC AND NUMBERS (Mathematics/Programming Tags) --}}
    <section>
        <div class="flex items-center justify-between mb-6 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">You might like: Logic and Numbers</h2>
                <p class="text-sm text-gray-500">Explore Mathematics and Technology modules</p>
            </div>
            <a href="#" class="text-xs font-bold text-[#a52a2a] uppercase tracking-widest hover:underline">See All</a>
        </div>
        <div class="flex overflow-x-auto no-scrollbar gap-6 pb-4 snap-x px-2">
            @forelse($logicMaterials as $material)
                <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer"
                     onclick="loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))">
                    <div class="relative w-full aspect-[4/3] overflow-hidden">
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" class="w-full h-full object-cover group-hover:scale-110 transition-duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors"></div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors">{{ $material->title }}</h3>
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $material->description }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 italic text-sm px-2">No materials found in this category.</p>
            @endforelse
        </div>
    </section>

    {{-- 3. CATEGORY: POPULAR MATERIALS (Ranked by Views) --}}
    <section class="py-4">
        <div class="flex items-center justify-between mb-6 px-2">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Popular Materials</h2>
        </div>
        <div class="flex overflow-x-auto no-scrollbar gap-10 pb-4 px-2">
            @foreach($popularMaterials as $index => $material)
            <div class="flex-none flex items-center gap-4 group cursor-pointer"
                 onclick="loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))">
                <span class="text-7xl md:text-8xl font-black text-gray-200 group-hover:text-[#a52a2a]/20 transition-colors italic leading-none">
                    {{ $index + 1 }}
                </span>
                <div class="w-48 h-64 rounded-xl overflow-hidden shadow-lg relative">
                    <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=300' }}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent p-4 flex flex-col justify-end">
                        <p class="text-white font-bold text-sm leading-tight line-clamp-2">{{ $material->title }}</p>
                        <p class="text-gray-400 text-[10px] mt-1">{{ number_format($material->views) }} views</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- 4. CATEGORY: FROM YOUR REGISTERED SCHOOL --}}
    <section>
        <div class="flex items-end justify-between mb-6 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">From {{ auth()->user()->school->name ?? 'Your School' }}</h2>
                <p class="text-sm text-gray-500 mt-1">Materials created by instructors at your institution</p>
            </div>
            <a href="#" class="text-sm font-bold text-[#a52a2a] hover:underline uppercase tracking-wider">See All</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-2">
            @forelse($schoolMaterials as $material)
                <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-xl border border-transparent hover:border-gray-100 transition-all cursor-pointer group bg-gray-50/50"
                     onclick="loadPartial('{{ route('dashboard.materials.manage', $material->id) }}', document.getElementById('nav-materials-btn'))">
                    <div class="h-24 w-24 flex-none rounded-xl bg-gray-200 overflow-hidden relative shadow-sm">
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?q=80&w=200' }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-base leading-tight truncate group-hover:text-[#a52a2a] transition mb-1">
                            {{ $material->title }}
                        </h3>
                        <p class="text-xs text-gray-500 font-medium truncate mb-2">
                            By {{ $material->instructor->first_name }} {{ $material->instructor->last_name }}
                        </p>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] bg-[#a52a2a]/10 text-[#a52a2a] px-2 py-0.5 rounded font-bold uppercase">
                                {{ $material->tags->first()->name ?? 'General' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <i class="fas fa-school text-3xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500 font-medium">No school-specific materials available yet.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>