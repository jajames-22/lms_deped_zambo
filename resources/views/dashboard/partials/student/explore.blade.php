<style>
    /* Hide scrollbar for Chrome, Safari and Opera (Legacy) */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox (Legacy) */
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Elegant horizontal scrollbar for sliders (Appears only when necessary) */
    .slider-scrollbar {
        overflow-x: auto;
    }
    .slider-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .slider-scrollbar::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 10px;
    }
    .slider-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e5e7eb; /* Tailwind gray-200 */
        border-radius: 10px;
    }
    .slider-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #a52a2a; /* Brand hover color */
    }
</style>

{{-- CONTAINER A: MAIN EXPLORE VIEW --}}
<div id="main-explore-content" class="max-w-7xl mx-auto space-y-10 pb-24 relative transition-opacity duration-300">
    
    {{-- 1. FEATURED BANNER CAROUSEL (Admin Selected) --}}
    @if($featuredMaterials->isNotEmpty())
        <div class="relative w-full h-80 md:h-[450px] rounded-2xl overflow-hidden shadow-2xl group" id="featured-carousel">
            
            {{-- Carousel Track --}}
            <div class="flex transition-transform duration-700 ease-in-out h-full w-full" id="carousel-track">
                @foreach($featuredMaterials as $index => $material)
                    <div class="w-full h-full flex-shrink-0 relative cursor-pointer"
                         onclick="window.location.href = '{{ route('dashboard.materials.show', $material->hashid) }}';">
                        
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10"></div>
                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=1200' }}" 
                             class="w-full h-full object-cover">
                        
                        <div class="absolute bottom-0 left-0 p-8 md:p-12 z-20 w-full md:w-2/3">
                            <span class="px-3 py-1 bg-[#a52a2a] text-white text-[10px] font-black uppercase tracking-widest rounded-md mb-4 inline-block shadow-sm">Featured Module</span>
                            <h2 class="text-3xl md:text-5xl font-black text-white mb-3 leading-tight drop-shadow-lg">{{ $material->title }}</h2>
                            <p class="text-gray-300 text-sm md:text-base line-clamp-2 md:line-clamp-3 mb-6 max-w-xl drop-shadow-md">{{ $material->description }}</p>
                            
                            <div class="flex items-center gap-4">
                                <button class="px-6 py-3 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition shadow-lg flex items-center gap-2">
                                    <i class="fas fa-play-circle text-[#a52a2a]"></i> Start Learning
                                </button>
                                <div class="text-white text-xs font-medium flex items-center gap-2 drop-shadow-md">
                                    <i class="fas fa-chalkboard-user opacity-70"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Carousel Controls --}}
            @if($featuredMaterials->count() > 1)
                <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/30 backdrop-blur border border-white/20 text-white flex items-center justify-center transition z-30 opacity-0 group-hover:opacity-100">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/30 backdrop-blur border border-white/20 text-white flex items-center justify-center transition z-30 opacity-0 group-hover:opacity-100">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                {{-- Dots --}}
                <div class="absolute bottom-6 right-8 flex items-center gap-2 z-30" id="carousel-dots">
                    @foreach($featuredMaterials as $index => $material)
                        <button onclick="goToSlide({{ $index }})" class="w-2.5 h-2.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/60' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- GRID LAYOUT: LEFT CONTENT (Popular/Sections) vs RIGHT SIDEBAR (Grade Levels) --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        {{-- LEFT COLUMN: 3/4 Width --}}
        <div class="lg:col-span-3 ">
            
            {{-- 2. CATEGORY: POPULAR MATERIALS (Ranked by Views) --}}
            <section class="py-4">
                <div class="flex items-center justify-between mb-6 px-2">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Popular Materials</h2>
                </div>
                <div class="flex overflow-x-auto slider-scrollbar pb-4 px-2">
                    @foreach($popularMaterials as $index => $material)
                    <div class="flex-none flex items-center gap-4 group cursor-pointer"
                         onclick="window.location.href = '{{ route('dashboard.materials.show', $material->hashid) }}';">
                        <span class="text-7xl md:text-8xl font-black text-gray-200 group-hover:text-[#a52a2a]/20 transition-colors italic leading-none">
                            {{ $index + 1 }}
                        </span>
                        
                        <div class="w-64 aspect-[4/3] rounded-xl overflow-hidden -translate-x-6 shadow-lg relative">
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

            {{-- 3. CATEGORY: FROM YOUR REGISTERED SCHOOL --}}
            @if(isset($schoolMaterials) && $schoolMaterials->isNotEmpty())
            <section class="py-4 border-t border-gray-100 pt-8">
                <div class="flex items-center justify-between mb-6 px-2">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <i class="fas fa-school text-[#a52a2a]"></i> From Your Registered School
                    </h2>
                </div>
                <div class="flex overflow-x-auto slider-scrollbar pb-4 px-2 space-x-6">
                    @foreach($schoolMaterials as $material)
                        <div class="flex-none w-64 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-[#a52a2a]/30 transition-all duration-300 group cursor-pointer flex flex-col overflow-hidden"
                             onclick="window.location.href = '{{ route('dashboard.materials.show', $material->hashid) }}';">
                            <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden shrink-0">
                                <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors">{{ $material->title }}</h3>
                                <p class="text-[10px] text-[#a52a2a] font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1.5">
                                    <i class="fas fa-chalkboard-user"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- 4. CATEGORY: DYNAMIC SECTIONS --}}
            @if(isset($dynamicSections) && $dynamicSections->isNotEmpty())
                @foreach($dynamicSections as $section)
                    @if($section->materials->isNotEmpty())
                    <section class="py-4 border-t border-gray-100 pt-8">
                        <div class="flex items-center justify-between mb-6 px-2">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $section->title }}</h2>
                                @if($section->subtitle)
                                    <p class="text-gray-500 text-sm mt-1">{{ $section->subtitle }}</p>
                                @endif
                            </div>
                            {{-- See All Button --}}
                            <button onclick="filterByCategory('{{ addslashes($section->title) }}')" class="text-sm font-bold text-[#a52a2a] hover:text-[#8a2323] transition-colors flex items-center gap-1 group whitespace-nowrap">
                                See All <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                        <div class="flex overflow-x-auto slider-scrollbar pb-4 px-2 space-x-6">
                            @foreach($section->materials as $material)
                                <div class="flex-none w-64 bg-white rounded-2xl border border-gray-100 shadow-sm hover:border-[#a52a2a]/30 transition-all duration-300 group cursor-pointer flex flex-col overflow-hidden"
                                     onclick="window.location.href = '{{ route('dashboard.materials.show', $material->hashid) }}';">
                                    <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden shrink-0">
                                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                                    </div>
                                    <div class="p-5 flex-1 flex flex-col">
                                        <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors">{{ $material->title }}</h3>
                                        <p class="text-[10px] text-[#a52a2a] font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1.5">
                                            <i class="fas fa-chalkboard-user"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif
                @endforeach
            @endif

        </div>

        {{-- RIGHT COLUMN: 1/4 Width (Grade Levels Sidebar) --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-black text-gray-900 flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-[#a52a2a]"></i> Grade Levels
                    </h3>
                </div>
                
                <p class="text-xs text-gray-500 mb-4">Filter learning materials by specific educational levels.</p>

                <div class="flex flex-col gap-2 max-h-[calc(100vh-14rem)] overflow-y-auto custom-scrollbar pr-1">
                    {{-- Kindergarten --}}
                    <button onclick="filterByCategory('KINDERGARTEN')" 
                        class="text-left px-4 py-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-[#a52a2a]/10 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] text-sm font-bold text-gray-700 transition-colors flex items-center justify-between group">
                        <span>Kindergarten</span>
                        <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-[#a52a2a] transition-colors"></i>
                    </button>

                    {{-- Grades 1 to 12 --}}
                    @for($i = 1; $i <= 12; $i++)
                        <button onclick="filterByCategory('GRADE {{ $i }}')" 
                            class="text-left px-4 py-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-[#a52a2a]/10 hover:border-[#a52a2a]/30 hover:text-[#a52a2a] text-sm font-bold text-gray-700 transition-colors flex items-center justify-between group">
                            <span>Grade {{ $i }}</span>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-[#a52a2a] transition-colors"></i>
                        </button>
                    @endfor
                </div>
            </div>
        </div>
        
    </div>
</div>

{{-- CONTAINER B: FILTERED RESULTS VIEW (Hidden by default) --}}
<div id="filtered-explore-content" class="max-w-7xl mx-auto hidden pb-24 relative transition-opacity duration-300 opacity-0">
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 border-b border-gray-200 pb-6 px-2">
        <div>
            <button onclick="resetExploreView()" class="text-sm font-bold text-gray-500 hover:text-[#a52a2a] flex items-center gap-2 mb-3 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Explore
            </button>
            <h1 class="text-3xl font-black text-gray-900 flex items-center gap-3">
                <span id="filter-title-label">Search Results</span>
                <span id="filter-count-badge" class="text-sm px-3 py-1 bg-gray-100 text-gray-600 rounded-lg font-bold border border-gray-200">0</span>
            </h1>
        </div>
        
        <div class="relative w-full sm:w-64">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="live-filter-search" placeholder="Filter these results..." 
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm shadow-sm">
        </div>
    </div>

    <div id="filtered-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-2">
        {{-- Filled by AJAX --}}
    </div>
</div>

<script>
    // CAROUSEL LOGIC
    let currentSlide = 0;
    const track = document.getElementById('carousel-track');
    const dotsContainer = document.getElementById('carousel-dots');
    let dots = dotsContainer ? dotsContainer.children : [];
    let slideCount = {{ $featuredMaterials->count() }};
    let autoPlayInterval;

    function updateCarousel() {
        if (!track) return;
        track.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        Array.from(dots).forEach((dot, index) => {
            if (index === currentSlide) {
                dot.className = 'w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white w-8';
            } else {
                dot.className = 'w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white/40 hover:bg-white/60';
            }
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slideCount;
        updateCarousel();
        resetAutoPlay();
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slideCount) % slideCount;
        updateCarousel();
        resetAutoPlay();
    }

    function goToSlide(index) {
        currentSlide = index;
        updateCarousel();
        resetAutoPlay();
    }

    function startAutoPlay() {
        if (slideCount > 1) {
            autoPlayInterval = setInterval(nextSlide, 5000);
        }
    }

    function resetAutoPlay() {
        clearInterval(autoPlayInterval);
        startAutoPlay();
    }

    if (slideCount > 1) startAutoPlay();

    // ==========================================
    // FILTERING LOGIC
    // ==========================================
    let currentFilteredData = [];

    function filterByCategory(category) {
        const mainContent = document.getElementById('main-explore-content');
        const filterContent = document.getElementById('filtered-explore-content');
        const titleLabel = document.getElementById('filter-title-label');
        const materialsGrid = document.getElementById('filtered-grid');
        
        // Reset search bar value on new category load
        document.getElementById('live-filter-search').value = '';
        
        titleLabel.innerHTML = `<i class="fas fa-tag text-[#a52a2a] mr-2"></i> ${category}`;
        materialsGrid.innerHTML = '<div class="col-span-full py-20 text-center"><i class="fas fa-circle-notch fa-spin text-4xl text-gray-300"></i><p class="mt-4 text-gray-500 font-medium">Loading materials...</p></div>';
        
        mainContent.classList.add('opacity-0');
        setTimeout(() => {
            mainContent.classList.add('hidden');
            filterContent.classList.remove('hidden');
            setTimeout(() => filterContent.classList.remove('opacity-0'), 50);
        }, 300);

        window.scrollTo({ top: 0, behavior: 'smooth' });

        fetch(`{{ url('/dashboard/explore/filter') }}?category=${encodeURIComponent(category)}`)
            .then(res => res.json())
            .then(data => {
                currentFilteredData = data.materials;
                renderFilteredMaterials(currentFilteredData);
            })
            .catch(error => {
                materialsGrid.innerHTML = '<div class="col-span-full py-20 text-center text-red-500">Failed to load materials. Please try again.</div>';
            });
    }

    // Extracted Card Rendering function
    function renderFilteredMaterials(materials) {
        const materialsGrid = document.getElementById('filtered-grid');
        const countBadge = document.getElementById('filter-count-badge');
        
        countBadge.textContent = materials.length;
        
        if (materials.length === 0) {
            materialsGrid.innerHTML = `
                <div class="col-span-full py-20 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <i class="fas fa-box-open text-2xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">No Materials Found</h3>
                    <p class="text-gray-500 text-sm">There are currently no public modules matching your criteria.</p>
                </div>
            `;
            return;
        }

        materialsGrid.innerHTML = materials.map(material => {
            const imgUrl = material.thumbnail ? `/storage/${material.thumbnail}` : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400';
            const instName = material.instructor ? `${material.instructor.first_name} ${material.instructor.last_name}` : 'Instructor';
            const desc = material.description ? material.description : 'No description provided.';
            
            // Build visual tags if any exist (limit to 3 for clean UI)
            let tagsHtml = '';
            if(material.tags && material.tags.length > 0) {
                tagsHtml = `<div class="flex flex-wrap gap-1.5 mt-2">` + 
                    material.tags.slice(0, 3).map(t => `<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-bold uppercase tracking-wider">${t.name}</span>`).join('') 
                    + `</div>`;
            }

            return `
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-[#a52a2a]/30 transition-all duration-300 group cursor-pointer flex flex-col overflow-hidden"
                     onclick="window.location.href = '/dashboard/materials/${material.hashid}/show';">
                    <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden shrink-0">
                        <img src="${imgUrl}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors">${material.title}</h3>
                        <p class="text-[10px] text-[#a52a2a] font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-user"></i> ${instName}
                        </p>
                        ${tagsHtml}
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">${desc}</p>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ==========================================
    // LIVE SEARCH / FILTER LISTENER
    // ==========================================
    document.getElementById('live-filter-search').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();

        // If search is empty, just render the full cached array
        if (!query) {
            renderFilteredMaterials(currentFilteredData);
            return;
        }

        // Filter the cached array locally without hitting the database
        const filtered = currentFilteredData.filter(material => {
            const titleMatch = material.title && material.title.toLowerCase().includes(query);
            
            // Check if ANY tag matches the search text
            const tagMatch = material.tags && material.tags.some(tag => tag.name.toLowerCase().includes(query));
            
            const instructorMatch = material.instructor && `${material.instructor.first_name} ${material.instructor.last_name}`.toLowerCase().includes(query);

            return titleMatch || tagMatch || instructorMatch;
        });

        // Re-render the grid using the newly filtered subset
        renderFilteredMaterials(filtered);
    });

    function resetExploreView() {
        const mainContent = document.getElementById('main-explore-content');
        const filterContent = document.getElementById('filtered-explore-content');

        // Start fading out the filter content
        filterContent.classList.add('opacity-0');
        
        setTimeout(() => {
            // Once faded out, hide it and show the main content
            filterContent.classList.add('hidden');
            mainContent.classList.remove('hidden');
            
            // Allow browser to render display:block before fading it back in
            setTimeout(() => {
                mainContent.classList.remove('opacity-0');
            }, 50);
            
        }, 300); // 300ms matches the transition-duration
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>