<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Explore Materials - DepEd Zamboanga</title>
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
</head>
<body class="bg-gray-50 text-gray-800 relative">

    {{-- ORIGINAL RED HEADER (Made Smaller/Sleeker) --}}
    <header style="background-color: #a52a2a;" class="fixed top-0 left-0 p-2 md:p-3 flex justify-center z-50 w-full items-center shadow-md">
        <a href="{{ route('index') }}" class="h-10 md:h-12 block">
            <img src="{{ asset('storage/images/deped_zambo_header.png') }}" class="h-full w-auto object-contain block" alt="DepEd Zamboanga Header">
        </a>
    </header>

    <main class="pt-24 md:pt-32 pb-24">
        
        {{-- CONTAINER A: MAIN EXPLORE VIEW --}}
        <div id="main-explore-content" class="max-w-7xl mx-auto space-y-10 pb-24 relative px-4 sm:px-6 transition-opacity duration-300">

            {{-- BACK BUTTON --}}
            <button onclick="navigateBack()" class="cursor-pointer flex items-center w-fit text-gray-500 hover:text-[#a52a2a] font-bold transition-colors group px-2 py-1 rounded-lg hover:bg-red-50 relative z-10 mb-2"> 
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> <span class="hidden sm:inline">Back</span> 
            </button>
            
            {{-- PAGE HEADER & SEARCH BAR --}}
            <div class="px-2 flex flex-col md:flex-row md:items-end justify-between gap-6 mb-4">
                <div>
                    <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight">Public Materials</h1>
                    <p class="text-gray-500 mt-2 text-base md:text-lg">Explore open-access learning resources from DepEd Zamboanga.</p>
                </div>
                
                {{-- SEARCH BAR --}}
                <div class="relative w-full md:w-96 group shrink-0">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-[#a52a2a] transition-colors"></i>
                    </div>
                    <input type="text" id="public-search-input" placeholder="Search public materials..." 
                           class="w-full pl-11 pr-24 py-3.5 bg-white border border-gray-200 rounded-xl shadow-sm outline-none focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] text-gray-900 text-sm transition-all"
                           onkeydown="if(event.key === 'Enter') executePublicSearch()">
                    <button onclick="executePublicSearch()" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-[#a52a2a] hover:bg-red-800 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition-all text-xs active:scale-95">
                        Search
                    </button>
                </div>
            </div>
            
            {{-- 1. FEATURED BANNER CAROUSEL --}}
            @if($featuredMaterials->isNotEmpty())
                <div class="relative w-full h-80 md:h-[450px] rounded-2xl overflow-hidden shadow-xl group" id="featured-carousel">
                    <div class="flex transition-transform duration-700 ease-in-out h-full w-full" id="carousel-track">
                        @foreach($featuredMaterials as $index => $material)
                            <div class="w-full h-full flex-shrink-0 relative cursor-pointer material-search-item"
                                 data-id="{{ $material->id }}" 
                                 data-title="{{ $material->title }}" 
                                 data-desc="{{ $material->description }}" 
                                 data-instructor="{{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}" 
                                 data-img="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=1000' }}"
                                 onclick="window.location.href = '{{ route('explore.materials.show', $material->id) }}';">
                                
                                <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10"></div>
                                <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=1000' }}" 
                                     class="absolute inset-0 w-full h-full object-cover opacity-80">
                                
                                <div class="absolute inset-0 z-20 flex flex-col justify-end p-8 md:p-16 w-full md:w-3/4">
                                    <span class="px-3 py-1 bg-[#a52a2a] text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-md w-max mb-4 shadow-md">Featured</span>
                                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4 leading-tight drop-shadow-lg line-clamp-2">{{ $material->title }}</h1>
                                    
                                    <p class="text-white font-bold text-xs md:text-sm uppercase tracking-widest mb-2 flex items-center gap-1.5 drop-shadow-md">
                                        <i class="fas fa-chalkboard-user"></i> {{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}
                                    </p>

                                    <p class="text-gray-100 text-sm md:text-lg mb-4 line-clamp-2 max-w-2xl drop-shadow-md">{{ $material->description }}</p>
                                    
                                    <div class="flex items-center gap-4">
                                        <button class="bg-white text-black hover:bg-gray-200 font-bold py-3 px-8 rounded-lg transition-all flex items-center gap-2 shadow-xl">
                                            <i class="fas fa-play"></i> Start Learning
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($featuredMaterials->count() > 1)
                        <button onclick="window.moveCarousel(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center bg-black/40 hover:bg-[#a52a2a] text-white rounded-full backdrop-blur-md transition-all opacity-0 group-hover:opacity-100 shadow-lg"><i class="fas fa-chevron-left"></i></button>
                        <button onclick="window.moveCarousel(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center bg-black/40 hover:bg-[#a52a2a] text-white rounded-full backdrop-blur-md transition-all opacity-0 group-hover:opacity-100 shadow-lg"><i class="fas fa-chevron-right"></i></button>
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex gap-2">
                            @foreach($featuredMaterials as $index => $material)
                                <button onclick="window.goToSlide({{ $index }})" class="carousel-dot h-2.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-[#a52a2a] w-8' : 'bg-white/50 hover:bg-white w-2.5' }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- 2. DYNAMIC SECTIONS --}}
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
                            
                            <button onclick="showCategory('{{ addslashes($section->tag_name) }}', '{{ addslashes($section->title) }}')"
                                class="text-xs font-bold text-[#a52a2a] uppercase tracking-widest hover:underline cursor-pointer">
                                See All
                            </button>
                        </div>

                        <div class="flex overflow-x-auto no-scrollbar gap-6 pb-6 snap-x px-2">
                            @foreach($section->materials as $material)
                                <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col cursor-pointer material-search-item"
                                     data-id="{{ $material->id }}" 
                                     data-title="{{ $material->title }}" 
                                     data-desc="{{ $material->description }}" 
                                     data-instructor="{{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}" 
                                     data-img="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}"
                                     onclick="window.location.href = '{{ route('explore.materials.show', $material->id) }}';">
                                    <div class="relative w-full aspect-[4/3] overflow-hidden">
                                        <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400' }}" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                                    </div>
                                    <div class="p-5">
                                        <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors duration-300">{{ $material->title }}</h3>
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

            {{-- 3. CATEGORY: POPULAR MATERIALS --}}
            <section class="py-4">
                <div class="flex items-center justify-between mb-6 px-2">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Popular Materials</h2>
                </div>
                <div class="flex overflow-x-auto no-scrollbar pb-4 px-2">
                    @foreach($popularMaterials as $index => $material)
                    <div class="flex-none flex items-center gap-4 group cursor-pointer material-search-item"
                         data-id="{{ $material->id }}" 
                         data-title="{{ $material->title }}" 
                         data-desc="{{ $material->description }}" 
                         data-instructor="{{ $material->instructor->first_name ?? 'Instructor' }} {{ $material->instructor->last_name ?? '' }}" 
                         data-img="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=300' }}"
                         onclick="window.location.href = '{{ route('explore.materials.show', $material->id) }}';">
                        <span class="text-7xl md:text-8xl font-black text-gray-200 group-hover:text-[#a52a2a]/20 transition-colors italic leading-none">{{ $index + 1 }}</span>
                        <div class="h-64 rounded-xl overflow-hidden -translate-x-6 shadow-lg relative">
                            <img src="{{ $material->thumbnail ? asset('storage/' . $material->thumbnail) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=300' }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent p-4 flex flex-col justify-end">
                                <p class="text-white font-bold text-sm leading-tight line-clamp-2">{{ $material->title }}</p>
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

            {{-- 4. GUEST CTA --}}
            <section class="mt-8 bg-[#a52a2a]/5 border border-[#a52a2a]/20 rounded-2xl p-6 md:p-8 text-center max-w-4xl mx-auto">
                <i class="fas fa-school text-3xl text-[#a52a2a] mb-3"></i>
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 tracking-tight mb-2">Want to see materials specific to your school?</h2>
                <p class="text-gray-600 max-w-xl mx-auto mb-6 text-sm md:text-base">Create a free student account today to access tailored modules, track your learning progress, and earn certificates from instructors at your registered school.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="{{ route('register') }}" class="px-6 py-2.5 bg-[#a52a2a] hover:bg-red-800 text-white text-sm font-bold rounded-lg shadow-md transition-all">Create Account</a>
                    <a href="{{ route('login') }}" class="px-6 py-2.5 bg-white text-[#a52a2a] border border-[#a52a2a]/30 hover:bg-gray-50 text-sm font-bold rounded-lg shadow-sm transition-all">I already have an account</a>
                </div>
            </section>

        </div>

        {{-- CONTAINER B: FILTERED "SEE ALL" / SEARCH RESULTS VIEW --}}
        <div id="filtered-explore-content" class="max-w-7xl mx-auto hidden px-4 sm:px-6 transition-opacity duration-300">
            <div class="flex flex-col md:flex-row md:items-center gap-4 mb-10">
                <button onclick="resetExploreView()" class="h-12 w-12 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:text-[#a52a2a] transition shadow-sm group shrink-0">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                </button>
                <div>
                    <p id="browsing-label" class="text-[10px] text-[#a52a2a] font-black uppercase tracking-[0.2em] mb-1">Browsing Category</p>
                    <h1 id="selected-category-title" class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight"></h1>
                </div>
            </div>

            <div id="filtered-materials-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                {{-- Populated dynamically via JS --}}
            </div>
        </div>

    </main>

    {{-- SCRIPTS --}}
    <script>
        function navigateBack() {
            const wrapper = document.getElementById('page-wrapper');
            if (wrapper) {
                wrapper.classList.remove('animate-slide-in');
                wrapper.classList.add('animate-slide-out');
            }
            setTimeout(() => { window.location.href = "/"; }, 300);
        }

        // --- DOM SEARCH LOGIC ---
        function executePublicSearch() {
            const query = document.getElementById('public-search-input').value.toLowerCase().trim();
            const mainContent = document.getElementById('main-explore-content');
            const filteredContent = document.getElementById('filtered-explore-content');
            const categoryTitle = document.getElementById('selected-category-title');
            const materialsGrid = document.getElementById('filtered-materials-grid');
            const browsingLabel = document.getElementById('browsing-label');

            if (query === '') {
                resetExploreView();
                return;
            }

            // Swap visibility
            mainContent.classList.add('hidden');
            filteredContent.classList.remove('hidden');
            browsingLabel.innerText = 'Search Results For';
            categoryTitle.innerText = `"${query}"`;

            // Collect all unique material cards from the page using the data attributes
            const items = document.querySelectorAll('.material-search-item');
            const uniqueMaterials = new Map();

            items.forEach(item => {
                const id = item.getAttribute('data-id');
                if (!uniqueMaterials.has(id)) {
                    uniqueMaterials.set(id, {
                        id: id,
                        title: item.getAttribute('data-title') || '',
                        desc: item.getAttribute('data-desc') || '',
                        instructor: item.getAttribute('data-instructor') || '',
                        img: item.getAttribute('data-img') || ''
                    });
                }
            });

            // Filter results
            const results = Array.from(uniqueMaterials.values()).filter(mat => {
                return mat.title.toLowerCase().includes(query) || 
                       mat.desc.toLowerCase().includes(query) || 
                       mat.instructor.toLowerCase().includes(query);
            });

            // Render Results
            if (results.length === 0) {
                materialsGrid.innerHTML = `
                    <div class="col-span-full py-20 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200">
                            <i class="fas fa-search text-gray-300 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No results found</h3>
                        <p class="text-gray-500">We couldn't find anything matching "${query}".</p>
                    </div>
                `;
                return;
            }

            materialsGrid.innerHTML = results.map(material => `
                <div class="group bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer flex flex-col"
                     onclick="window.location.href = '/explore/materials/${material.id}/show';">
                    <div class="relative aspect-[4/3] overflow-hidden w-full">
                        <img src="${material.img}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors">${material.title}</h3>
                        <p class="text-[10px] text-[#a52a2a] font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-user"></i> ${material.instructor}
                        </p>
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">${material.desc}</p>
                    </div>
                </div>
            `).join('');
        }

        // Carousel Logic
        @if(isset($featuredMaterials) && $featuredMaterials->count() > 1)
            window.currentSlide = 0;
            window.totalSlides = {{ $featuredMaterials->count() }};
            
            if (window.carouselInterval) clearInterval(window.carouselInterval);

            window.updateCarousel = function() {
                const track = document.getElementById('carousel-track');
                const dots = document.querySelectorAll('.carousel-dot');
                if (!track) return;
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

            setTimeout(() => {
                window.updateCarousel();
                window.startInterval();
            }, 50);
        @endif

        // See All Category Logic
        function showCategory(tagName, displayName) {
            const mainContent = document.getElementById('main-explore-content');
            const filteredContent = document.getElementById('filtered-explore-content');
            const categoryTitle = document.getElementById('selected-category-title');
            const materialsGrid = document.getElementById('filtered-materials-grid');
            const browsingLabel = document.getElementById('browsing-label');

            mainContent.classList.add('hidden');
            filteredContent.classList.remove('hidden');

            browsingLabel.innerText = 'Browsing Category';
            categoryTitle.innerText = displayName;
            materialsGrid.innerHTML = `
                <div class="col-span-full py-20 text-center">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-[#a52a2a]/30"></i>
                    <p class="mt-4 text-gray-400 font-medium">Loading materials...</p>
                </div>
            `;

            window.scrollTo({ top: 0, behavior: 'smooth' });

            fetch(`/explore/tags/${encodeURIComponent(tagName)}/json`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        materialsGrid.innerHTML = '<div class="col-span-full py-20 text-center"><p class="text-gray-500">No materials found in this category.</p></div>';
                        return;
                    }

                    materialsGrid.innerHTML = data.map(material => {
                        const imgUrl = material.thumbnail ? '/storage/' + material.thumbnail : 'https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=400';
                        const instName = material.instructor ? material.instructor.first_name + ' ' + (material.instructor.last_name || '') : 'Instructor';
                        const desc = material.description || '';
                        
                        return `
                            <div class="group bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer flex flex-col"
                                 onclick="window.location.href = '/explore/materials/${material.id}/show';">
                                <div class="relative aspect-[4/3] overflow-hidden w-full">
                                    <img src="${imgUrl}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-500"></div>
                                </div>
                                <div class="p-5 flex-1 flex flex-col">
                                    <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#a52a2a] transition-colors">${material.title}</h3>
                                    <p class="text-[10px] text-[#a52a2a] font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1.5">
                                        <i class="fas fa-chalkboard-user"></i> ${instName}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2 line-clamp-2">${desc}</p>
                                </div>
                            </div>
                        `;
                    }).join('');
                })
                .catch(error => {
                    materialsGrid.innerHTML = '<div class="col-span-full py-20 text-center text-red-500">Failed to load materials. Please try again.</div>';
                });
        }

        function resetExploreView() {
            document.getElementById('public-search-input').value = '';
            document.getElementById('main-explore-content').classList.remove('hidden');
            document.getElementById('filtered-explore-content').classList.add('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>