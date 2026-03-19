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

<div class="max-w-7xl mx-auto space-y-10 pb-24">
    
    <div class="relative w-full h-72 md:h-96 rounded-xl overflow-hidden shadow-xl group cursor-pointer">
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-800/90 to-transparent z-10"></div>
        <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=1000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-700" alt="Featured">
        
        <div class="absolute inset-0 z-20 flex flex-col justify-end p-8 md:p-12 w-full md:w-2/3">
            <span class="px-3 py-1 bg-[#a52a2a] text-white text-xs font-bold uppercase tracking-wider rounded-md w-max shadow-md">Featured • Sciences</span>
            <h1 class="text-3xl md:text-5xl font-black text-white mb-2 leading-tight drop-shadow-md">Advanced Cellular Biology</h1>
            <p class="text-gray-300 text-sm font-medium mb-4 mt-4 flex items-center gap-2">
                <i class="fas fa-chalkboard-teacher"></i> Dr. Maria Clara
            </p>
            <p class="text-gray-400 text-sm md:text-base mb-8 line-clamp-2 max-w-xl">Explore the microscopic world of cells, their functions, and the fundamental building blocks of all living organisms in this comprehensive 8-week masterclass.</p>
            
            <div class="flex items-center gap-4">
                <button class="bg-[#a52a2a] hover:bg-red-800 text-white font-bold py-3 px-8 rounded-lg transition-transform active:scale-95 shadow-lg shadow-[#a52a2a]/30 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Enroll Now
                </button>
                <button class="h-12 w-12 rounded-lg border-2 border-white/30 text-white flex items-center justify-center hover:bg-white/10 transition backdrop-blur-sm">
                    <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    </div>

    <section>
        <div class="flex items-end justify-between mb-5 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Sciences</h2>
                <p class="text-sm text-gray-500 mt-1">Biology, Chemistry, Physics, and Earth Sciences</p>
            </div>
            <a href="#" class="text-sm font-bold text-[#a52a2a] hover:underline uppercase tracking-wider">See All</a>
        </div>
        
        <div class="flex overflow-x-auto no-scrollbar gap-6 pb-6 snap-x px-2">
            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-play text-xl ml-1"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-bold px-2 py-1 rounded backdrop-blur-md">Module • 45m</div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Organic Chemistry 101</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Prof. Juan Dela Cruz</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">A comprehensive introduction to the structure, properties, and reactions of organic compounds and materials.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>

            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-atom text-5xl text-white/50"></i>
                    </div>
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-file-pdf text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-bold px-2 py-1 rounded backdrop-blur-md">PDF • 120 Pages</div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Quantum Mechanics</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Dr. Albert Einstein</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">Understand the fundamental theory in physics that provides a description of the physical properties of nature.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>

            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <img src="https://images.unsplash.com/photo-1614935151651-0bea6508ab6b?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-play text-xl ml-1"></i>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Earth & Space Science</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Prof. Galileo Galilei</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">A visual journey through the solar system, planetary geology, and the fundamentals of astronomy.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="flex items-end justify-between mb-5 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Languages</h2>
                <p class="text-sm text-gray-500 mt-1">English, Filipino, and Foreign Literature</p>
            </div>
            <a href="#" class="text-sm font-bold text-[#a52a2a] hover:underline uppercase tracking-wider">See All</a>
        </div>
        
        <div class="flex overflow-x-auto no-scrollbar gap-6 pb-6 snap-x px-2">
            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <img src="https://images.unsplash.com/photo-1456223340514-f58c73d9e075?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-book-reader text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">English Literature</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Prof. William S.</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">Analyze classic works of literature, focusing on themes, narrative structure, and historical context.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>

            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <div class="w-full h-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                        <i class="fas fa-language text-5xl text-white/50"></i>
                    </div>
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-file-pdf text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Komunikasyon at Pananaliksik</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Dr. Jose Rizal</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">Pag-aaral tungo sa pananaliksik ukol sa kalikasan, katangian, at pag-unlad ng wikang pambansa.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>
            
            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <img src="https://images.unsplash.com/photo-1546410531-bea5aadcb6ce?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-play text-xl ml-1"></i>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Grammar & Syntax</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Prof. Grace Hopper</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">Master the rules of English grammar, sentence construction, and effective communication techniques.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="flex items-end justify-between mb-5 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">MAPEH</h2>
                <p class="text-sm text-gray-500 mt-1">Music, Arts, Physical Education, and Health</p>
            </div>
            <a href="#" class="text-sm font-bold text-[#a52a2a] hover:underline uppercase tracking-wider">See All</a>
        </div>
        
        <div class="flex overflow-x-auto no-scrollbar gap-6 pb-6 snap-x px-2">
            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <img src="https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-music text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Traditional Phil. Music</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Maestro Levi Celerio</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">Discover the rich history of indigenous instruments and traditional vocal styles of the Philippines.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>

            <div class="w-72 flex-none snap-start group bg-white border border-gray-200 hover:border-[#a52a2a]/30 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col cursor-pointer">
                <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                    <img src="https://images.unsplash.com/photo-1513364776144-60967b0f800f?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <div class="h-14 w-14 bg-[#a52a2a] rounded-full flex items-center justify-center text-white shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="fas fa-palette text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-1 group-hover:text-[#a52a2a] transition-colors mb-1">Visual Arts & Design</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-2"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Mr. Juan Luna</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">Learn fundamental techniques in painting, sketching, and digital art creation for beginners.</p>
                    <button class="w-full py-2.5 bg-gray-50 text-gray-700 border border-gray-200 font-bold rounded-lg group-hover:bg-[#a52a2a] group-hover:text-white group-hover:border-[#a52a2a] transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-user-plus"></i> Enroll
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="flex items-end justify-between mb-5 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">From WMSU</h2>
                <p class="text-sm text-gray-500 mt-1">Materials created by instructors at your school</p>
            </div>
            <a href="#" class="text-sm font-bold text-[#a52a2a] hover:underline uppercase tracking-wider">See All</a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 px-2">
            <div class="flex items-start md:items-center gap-5 p-4 rounded-xl hover:bg-white hover:shadow-md border border-transparent hover:border-gray-200 transition cursor-pointer group bg-gray-50/50">
                <div class="h-24 w-32 md:h-28 md:w-36 flex-none rounded-lg bg-gray-200 overflow-hidden relative shadow-sm border border-gray-100">
                    <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=300&auto=format&fit=crop" class="w-full h-full object-cover" alt="Thumbnail">
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition flex items-center justify-center">
                        <i class="fas fa-play text-white opacity-0 group-hover:opacity-100 transition transform scale-75 group-hover:scale-100"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0 py-1">
                    <span class="text-[10px] font-bold text-[#a52a2a] uppercase tracking-wider mb-1 block">Computer Science</span>
                    <h3 class="font-bold text-gray-900 text-lg leading-tight truncate group-hover:text-[#a52a2a] transition mb-1">BSCS Midterm Reviewer</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-1"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Prof. Dela Cruz</p>
                    <p class="text-xs text-gray-500 line-clamp-1 md:line-clamp-2">Complete reviewer covering data structures, algorithms, and basic networking concepts for the upcoming midterm examination.</p>
                </div>
                <button class="px-5 py-2.5 bg-white text-gray-700 border border-gray-200 hover:text-white hover:bg-[#a52a2a] hover:border-[#a52a2a] font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap self-center hidden md:block">
                    Enroll
                </button>
            </div>

            <div class="flex items-start md:items-center gap-5 p-4 rounded-xl hover:bg-white hover:shadow-md border border-transparent hover:border-gray-200 transition cursor-pointer group bg-gray-50/50">
                <div class="h-24 w-32 md:h-28 md:w-36 flex-none rounded-lg bg-gray-200 overflow-hidden relative shadow-sm border border-gray-100">
                    <div class="w-full h-full bg-blue-50 text-blue-500 flex items-center justify-center text-3xl"><i class="fas fa-file-pdf"></i></div>
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition flex items-center justify-center">
                        <i class="fas fa-book-reader text-white opacity-0 group-hover:opacity-100 transition transform scale-75 group-hover:scale-100"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0 py-1">
                    <span class="text-[10px] font-bold text-[#a52a2a] uppercase tracking-wider mb-1 block">Health & Safety</span>
                    <h3 class="font-bold text-gray-900 text-lg leading-tight truncate group-hover:text-[#a52a2a] transition mb-1">First Aid Fundamentals</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-1"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Dr. Maria Clara</p>
                    <p class="text-xs text-gray-500 line-clamp-1 md:line-clamp-2">A comprehensive guide to emergency response, CPR, and treating minor workplace injuries effectively.</p>
                </div>
                <button class="px-5 py-2.5 bg-white text-gray-700 border border-gray-200 hover:text-white hover:bg-[#a52a2a] hover:border-[#a52a2a] font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap self-center hidden md:block">
                    Enroll
                </button>
            </div>
            
            <div class="flex items-start md:items-center gap-5 p-4 rounded-xl hover:bg-white hover:shadow-md border border-transparent hover:border-gray-200 transition cursor-pointer group bg-gray-50/50">
                <div class="h-24 w-32 md:h-28 md:w-36 flex-none rounded-lg bg-gray-200 overflow-hidden relative shadow-sm border border-gray-100">
                    <img src="https://images.unsplash.com/photo-1526379095098-d400fd0bf935?q=80&w=300&auto=format&fit=crop" class="w-full h-full object-cover" alt="Thumbnail">
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition flex items-center justify-center">
                        <i class="fas fa-desktop text-white opacity-0 group-hover:opacity-100 transition transform scale-75 group-hover:scale-100"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0 py-1">
                    <span class="text-[10px] font-bold text-[#a52a2a] uppercase tracking-wider mb-1 block">History</span>
                    <h3 class="font-bold text-gray-900 text-lg leading-tight truncate group-hover:text-[#a52a2a] transition mb-1">Contemporary History</h3>
                    <p class="text-sm text-gray-600 font-medium truncate mb-1"><i class="fas fa-user-circle mr-1 text-gray-400"></i> Prof. Andres Bonifacio</p>
                    <p class="text-xs text-gray-500 line-clamp-1 md:line-clamp-2">Dive into the major global events from 1945 to the present day and their impact on modern society.</p>
                </div>
                <button class="px-5 py-2.5 bg-white text-gray-700 border border-gray-200 hover:text-white hover:bg-[#a52a2a] hover:border-[#a52a2a] font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap self-center hidden md:block">
                    Enroll
                </button>
            </div>
        </div>
    </section>
</div>
