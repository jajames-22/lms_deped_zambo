<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Administrator Portal</h1>
        <p class="text-gray-500 text-sm">Real-time overview of the DepEd Zamboanga LMS infrastructure.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-bold">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            System Online
        </span>
        <button class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-users-cog text-xl"></i>
            </div>
            <div class="text-right">
                <span class="text-green-500 text-xs font-bold font-mono">↑ 12%</span>
            </div>
        </div>
        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Users</p>
        <h3 class="text-2xl font-black text-gray-900">8,432</h3>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-400">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-xl"></i>
            </div>
            <span class="bg-amber-100 text-amber-700 text-[10px] px-2 py-1 rounded-md font-bold italic">ACTION REQUIRED</span>
        </div>
        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Pending Teachers</p>
        <h3 class="text-2xl font-black text-gray-900">14</h3>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-database text-xl"></i>
            </div>
        </div>
        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Storage Used</p>
        <h3 class="text-2xl font-black text-gray-900">64.2 <span class="text-sm font-normal text-gray-400">GB</span></h3>
        <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3">
            <div class="bg-rose-500 h-1.5 rounded-full" style="width: 64%"></div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-server text-xl"></i>
            </div>
        </div>
        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">System Uptime</p>
        <h3 class="text-2xl font-black text-gray-900">99.98%</h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm h-full">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">System Activity Audit</h3>
                <button class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">Download Logs</button>
            </div>
            <div class="p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 text-[10px] uppercase text-gray-400 font-bold">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Action</th>
                            <th class="px-6 py-3">Time</th>
                            <th class="px-6 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name=J+Rojas&background=random" class="w-7 h-7 rounded-full">
                                <span class="text-sm font-semibold text-gray-700">James Rojas</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 italic">Created Course "PHP Advanced"</td>
                            <td class="px-6 py-4 text-xs text-gray-400">2 mins ago</td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold">SUCCESS</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold">System</div>
                                <span class="text-sm font-semibold text-gray-700">Backup Engine</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 italic">Weekly DB Backup Created</td>
                            <td class="px-6 py-4 text-xs text-gray-400">1 hour ago</td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold">SUCCESS</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 text-center border-t border-gray-50">
                <button class="text-[#a52a2a] text-xs font-bold hover:underline">View Full Audit Trail</button>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-[#a52a2a] rounded-2xl p-6 text-white shadow-lg shadow-red-900/20">
            <h3 class="font-bold mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <button class="bg-white/10 hover:bg-white/20 p-4 rounded-xl transition flex flex-col items-center gap-2 border border-white/10">
                    <i class="fas fa-user-plus text-lg"></i>
                    <span class="text-[10px] font-bold uppercase">Add User</span>
                </button>
                <button class="bg-white/10 hover:bg-white/20 p-4 rounded-xl transition flex flex-col items-center gap-2 border border-white/10">
                    <i class="fas fa-envelope-open-text text-lg"></i>
                    <span class="text-[10px] font-bold uppercase">Broadcast</span>
                </button>
                <button class="bg-white/10 hover:bg-white/20 p-4 rounded-xl transition flex flex-col items-center gap-2 border border-white/10">
                    <i class="fas fa-shield-alt text-lg"></i>
                    <span class="text-[10px] font-bold uppercase">Perms</span>
                </button>
                <button class="bg-white/10 hover:bg-white/20 p-4 rounded-xl transition flex flex-col items-center gap-2 border border-white/10">
                    <i class="fas fa-file-export text-lg"></i>
                    <span class="text-[10px] font-bold uppercase">Reports</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border-2 border-dashed border-amber-200 p-6">
            <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-amber-500"></i> Teacher Approvals
            </h4>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Maria+Santos&background=a52a2a&color=fff" class="w-8 h-8 rounded-lg">
                        <div>
                            <p class="text-xs font-bold text-gray-800">Maria Santos</p>
                            <p class="text-[10px] text-gray-400">Zamboanga NHS</p>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button class="p-1.5 bg-green-50 text-green-600 rounded hover:bg-green-100 transition"><i class="fas fa-check text-[10px]"></i></button>
                        <button class="p-1.5 bg-red-50 text-red-600 rounded hover:bg-red-100 transition"><i class="fas fa-times text-[10px]"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>