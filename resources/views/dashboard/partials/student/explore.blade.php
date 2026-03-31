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

<div class="max-w-7xl mx-auto space-y-12 pb-24 relative">
    {{-- 1. FEATURED BANNER CAROUSEL (Admin Selected) --}}
    @if($featuredMaterials->isNotEmpty())
        <div class="relative w-full h-80 md:h-[450px] rounded-2xl overflow-hidden shadow-2xl group" id="featured-carousel">
            
            {{-- Carousel Track --}}
            <div class="flex transition-transform duration-700 ease-in-out h-full w-full" id="carousel-track">
                @foreach($featuredMaterials as $index => $material)
                    <div class="w-full h-full flex-shrink-0 relative cursor-pointer"
                         onclick="window.location.href = '{{ route('dashboard.materials.show', $material->id) }}';">
                        
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10"></div>
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=1000' }}" 
                             class="absolute inset-0 w-full h-full object-cover opacity-80">
                        
                        <div class="absolute inset-0 z-20 flex flex-col justify-end p-8 md:p-16 w-full md:w-3/4">
                            <span class="px-3 py-1 bg-[#a52a2a] text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-md w-max mb-4 shadow-md">Featured</span>
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-2 leading-tight drop-shadow-lg line-clamp-2">{{ $material->title }}</h1>
                            
                            {{-- INSTRUCTOR ICON --}}
                            <p class="text-white font-bold text-xs md:text-sm uppercase tracking-widest mb-4 flex items-center gap-1.5 drop-shadow-md">
                                <i class="fas fa-chalkboard-user"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                            </p>

                            <p class="text-gray-100 text-sm md:text-lg mb-8 line-clamp-2 max-w-2xl drop-shadow-md">{{ $material->description }}</p>
                            
                            <div class="flex items-center gap-4">
                                <button class="bg-white text-black hover:bg-gray-200 font-bold py-3 px-8 rounded-lg transition-all flex items-center gap-2 shadow-xl">
                                    <i class="fas fa-play"></i> Start Learning
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Controls (Only show if there is more than 1 featured item) --}}
            @if($featuredMaterials->count() > 1)
                {{-- Arrows --}}
                <button onclick="window.moveCarousel(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center bg-black/40 hover:bg-[#a52a2a] text-white rounded-full backdrop-blur-md transition-all opacity-0 group-hover:opacity-100 shadow-lg">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="window.moveCarousel(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center bg-black/40 hover:bg-[#a52a2a] text-white rounded-full backdrop-blur-md transition-all opacity-0 group-hover:opacity-100 shadow-lg">
                    <i class="fas fa-chevron-right"></i>
                </button>

                {{-- Dots --}}
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex gap-2">
                    @foreach($featuredMaterials as $index => $material)
                        <button onclick="window.goToSlide({{ $index }})" class="carousel-dot h-2.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-[#a52a2a] w-8' : 'bg-white/50 hover:bg-white w-2.5' }}"></button>
                    @endforeach
                </div>

                {{-- AJAX-Proof Javascript for Carousel --}}
                <script>
                    window.currentSlide = 0;
                    window.totalSlides = {{ $featuredMaterials->count() }};
                    
                    // Clear interval if it already exists from a previous page visit
                    if (window.carouselInterval) clearInterval(window.carouselInterval);

                    window.updateCarousel = function() {
                        const track = document.getElementById('carousel-track');
                        const dots = document.querySelectorAll('.carousel-dot');
                        
                        if (!track) return; // Safety check
                        
                        track.style.transform = `translateX(-${window.currentSlide * 100}%)`;
                        dots.forEach((dot, index) => {
                            if (index === window.currentSlide) {
                                dot.classList.remove('bg-white/50', 'w-2.5');
                                dot.classList.add('bg-[#a52a2a]', 'w-8');
                            } else {
                                dot.classList.remove('bg-[#a52a2a]', 'w-8');
                                dot.classList.add('bg-white/50', 'w-2.5');
                            }
                        });
                    }

                    window.moveCarousel = function(direction) {
                        window.currentSlide = (window.currentSlide + direction + window.totalSlides) % window.totalSlides;
                        window.updateCarousel();
                        window.resetInterval();
                    }

                    window.goToSlide = function(index) {
                        window.currentSlide = index;
                        window.updateCarousel();
                        window.resetInterval();
                    }

                    window.startInterval = function() {
                        window.carouselInterval = setInterval(() => { window.moveCarousel(1); }, 5000); 
                    }

                    window.resetInterval = function() {
                        clearInterval(window.carouselInterval);
                        window.startInterval();
                    }

                    // Initialize the carousel
                    setTimeout(() => {
                        window.updateCarousel();
                        window.startInterval();
                    }, 50);
                </script>
            @endif
        </div>
    @endif

    {{-- 2. DYNAMIC SECTIONS (Admin Controlled Categories) --}}
    @foreach($dynamicSections as $section)
        @if($section->materials->isNotEmpty())
            <section class="mb-0">
                <div class="flex items-center justify-between mb-6 px-2">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $section->title }}</h2>
                        @if($section->subtitle)
                            <p class="text-sm text-gray-500">{{ $section->subtitle }}</p>
                        @endif
                    </div>
                    <a href="{{ route('dashboard.explore.tag', json_decode($section->tag_name)[0] ?? $section->tag_name) }}" class="text-xs font-bold text-[#a52a2a] uppercase tracking-widest hover:underline">See All</a>
                </div>

                <div class="flex overflow-x-auto no-scrollbar gap-6 pb-6 snap-x px-2">
                    @foreach($section->materials as $material)
                        <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col cursor-pointer"
                             onclick="window.location.href = '{{ route('dashboard.materials.show', $material->id) }}';">
                            <div class="relative w-full aspect-[4/3] overflow-hidden">
                                <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors duration-300">{{ $material->title }}</h3>
                                
                                {{-- INSTRUCTOR ICON --}}
                                <p class="text-[10px] text-[#a52a2a] font-bold uppercase tracking-wider mt-1.5 truncate flex items-center gap-1.5">
                                    <i class="fas fa-chalkboard-user"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $material->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    {{-- 3. CATEGORY: POPULAR MATERIALS (Ranked by Views) --}}
    <section class="py-4">
        <div class="flex items-center justify-between mb-6 px-2">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Popular Materials</h2>
        </div>
        <div class="flex overflow-x-auto no-scrollbar pb-4 px-2">
            @foreach($popularMaterials as $index => $material)
            <div class="flex-none flex items-center gap-4 group cursor-pointer"
                 onclick="window.location.href = '{{ route('dashboard.materials.show', $material->id) }}';">
                <span class="text-7xl md:text-8xl font-black text-gray-200 group-hover:text-[#a52a2a]/20 transition-colors italic leading-none">
                    {{ $index + 1 }}
                </span>
                <div class="h-64 rounded-xl overflow-hidden -translate-x-6 shadow-lg relative">
                    <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=300' }}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent p-4 flex flex-col justify-end">
                        <p class="text-white font-bold text-sm leading-tight line-clamp-2">{{ $material->title }}</p>
                        
                        {{-- INSTRUCTOR ICON --}}
                        <p class="text-gray-300 text-[10px] font-medium mt-1.5 truncate flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-user text-gray-400"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                        </p>

                        <p class="text-gray-400 text-[10px] mt-1"><i class="fas fa-eye mr-1"></i>{{ number_format($material->views) }} views</p>
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
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-2">
            @forelse($schoolMaterials as $material)
                <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-xl border border-transparent hover:border-gray-100 transition-all cursor-pointer group bg-gray-50/50"
                     onclick="window.location.href = '{{ route('dashboard.materials.show', $material->id) }}';">
                    <div class="h-24 w-24 flex-none rounded-xl bg-gray-200 overflow-hidden relative shadow-sm">
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?q=80&w=200' }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-base leading-tight truncate group-hover:text-[#a52a2a] transition mb-1">
                            {{ $material->title }}
                        </h3>
                        
                        {{-- INSTRUCTOR ICON --}}
                        <p class="text-xs text-gray-500 font-medium truncate mb-2 flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-user text-gray-400"></i> {{ $material->instructor->first_name }} {{ $material->instructor->last_name }}
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