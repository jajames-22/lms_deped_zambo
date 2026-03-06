<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">School Directory</h1>
            <p class="text-gray-500 text-sm">Manage registered schools within the Zamboanga Division.</p>
        </div>
        
        <button onclick="loadPartial('{{ url('/dashboard/schools/create') }}', document.getElementById('nav-schools-btn'))" 
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition-all active:scale-95">
            <i class="fas fa-school"></i>
            <span>Add New School</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Schools</p>
                <h3 class="text-2xl font-black text-gray-900">84</h3>
            </div>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"><i class="fas fa-building text-lg"></i></div>
        </div>
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Elementary</p>
                <h3 class="text-2xl font-black text-gray-900">52</h3>
            </div>
            <div class="w-10 h-10 bg-green-50 text-green-600 rounded-lg flex items-center justify-center"><i class="fas fa-child text-lg"></i></div>
        </div>
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Secondary</p>
                <h3 class="text-2xl font-black text-gray-900">32</h3>
            </div>
            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center"><i class="fas fa-user-graduate text-lg"></i></div>
        </div>
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Pending Sync</p>
                <h3 class="text-2xl font-black text-amber-600">2</h3>
            </div>
            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center"><i class="fas fa-sync fa-spin text-lg"></i></div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="relative w-full md:w-96">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" placeholder="Search by School Name or ID..." class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all shadow-sm">
        </div>
        <select class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 outline-none text-gray-600 font-medium shadow-sm w-full md:w-auto">
            <option value="all">All Districts</option>
            <option value="ayala">Ayala District</option>
            <option value="baliwasan">Baliwasan District</option>
            <option value="putik">Putik District</option>
        </select>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">School ID</th>
                        <th class="px-6 py-4">School Name</th>
                        <th class="px-6 py-4">Level</th>
                        <th class="px-6 py-4">District</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">303792</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-gray-900">Zamboanga City National High School (Main)</p>
                            <p class="text-[10px] text-gray-400">Principal: Dr. Maria Santos</p>
                        </td>
                        <td class="px-6 py-4"><span class="px-3 py-1 bg-purple-50 text-purple-700 text-[10px] font-bold rounded-lg border border-purple-100">Secondary</span></td>
                        <td class="px-6 py-4 text-sm text-gray-600">Baliwasan</td>
                        <td class="px-6 py-4"><span class="flex items-center gap-1.5 text-xs font-bold text-green-600"><span class="w-2 h-2 rounded-full bg-green-500"></span> Active</span></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-edit"></i></button>
                                <button class="p-2 text-gray-400 hover:text-[#a52a2a] hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">125430</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-gray-900">Tetuan Central School</p>
                            <p class="text-[10px] text-gray-400">Principal: Mr. Juan Dela Cruz</p>
                        </td>
                        <td class="px-6 py-4"><span class="px-3 py-1 bg-green-50 text-green-700 text-[10px] font-bold rounded-lg border border-green-100">Elementary</span></td>
                        <td class="px-6 py-4 text-sm text-gray-600">Tetuan</td>
                        <td class="px-6 py-4"><span class="flex items-center gap-1.5 text-xs font-bold text-green-600"><span class="w-2 h-2 rounded-full bg-green-500"></span> Active</span></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-edit"></i></button>
                                <button class="p-2 text-gray-400 hover:text-[#a52a2a] hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-50 flex justify-between items-center bg-gray-50/30">
            <span class="text-xs text-gray-500">Showing 1 to 10 of 84 schools</span>
            <div class="flex gap-1">
                <button class="px-3 py-1 border border-gray-200 rounded text-xs text-gray-400 cursor-not-allowed">Prev</button>
                <button class="px-3 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-white bg-gray-50">1</button>
                <button class="px-3 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-white">2</button>
                <button class="px-3 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-white">Next</button>
            </div>
        </div>
    </div>
</div>