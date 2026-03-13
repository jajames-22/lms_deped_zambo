<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Instructor Overview</h1>
    <p class="text-gray-500 text-sm">Welcome back, Prof. {{ auth()->user()->last_name }}. Here is what's happening in
        your classes today.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-xl"></i>
            </div>
            <span class="text-green-500 text-xs font-bold">+3 new</span>
        </div>
        <p class="text-gray-500 text-sm font-medium">Total Students</p>
        <h3 class="text-2xl font-bold text-gray-900">142</h3>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-book text-xl"></i>
            </div>
        </div>
        <p class="text-gray-500 text-sm font-medium">Active Materialss</p>
        <h3 class="text-2xl font-bold text-gray-900">5</h3>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <span class="bg-amber-100 text-amber-700 text-[10px] px-2 py-1 rounded-full font-bold">URGENT</span>
        </div>
        <p class="text-gray-500 text-sm font-medium">To Grade</p>
        <h3 class="text-2xl font-bold text-gray-900">28</h3>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-star text-xl"></i>
            </div>
        </div>
        <p class="text-gray-500 text-sm font-medium">Avg. Attendance</p>
        <h3 class="text-2xl font-bold text-gray-900">94%</h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Submissions to Grade</h3>
                <button class="text-[#a52a2a] text-sm font-semibold hover:underline">View All</button>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <i class="fas fa-file-alt text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Midterm Quiz - CS 101</p>
                            <p class="text-xs text-gray-500">12 new submissions today</p>
                        </div>
                    </div>
                    <button
                        class="px-4 py-2 bg-[#a52a2a] text-white text-xs font-bold rounded-lg hover:bg-red-800 transition">
                        Grade Now
                    </button>
                </div>

                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <i class="fas fa-project-diagram text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Final Project Proposal</p>
                            <p class="text-xs text-gray-500">16 submissions pending</p>
                        </div>
                    </div>
                    <button
                        class="px-4 py-2 bg-[#a52a2a] text-white text-xs font-bold rounded-lg hover:bg-red-800 transition">
                        Grade Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-calendar-day mr-2 text-[#a52a2a]"></i> Today's Classes
            </h3>

            <div class="space-y-6">
                <div class="relative pl-6 border-l-2 border-[#a52a2a]">
                    <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-[#a52a2a]"></div>
                    <p class="text-xs font-bold text-[#a52a2a]">09:00 AM - 10:30 AM</p>
                    <p class="text-sm font-bold text-gray-900">Introduction to Computer Science</p>
                    <p class="text-xs text-gray-500">Room 302 • Section A</p>
                </div>

                <div class="relative pl-6 border-l-2 border-gray-200">
                    <p class="text-xs font-bold text-gray-400">01:00 PM - 02:30 PM</p>
                    <p class="text-sm font-bold text-gray-900">Web Development (Django)</p>
                    <p class="text-xs text-gray-500">Online • Section B</p>
                </div>
            </div>

            <button
                class="w-full mt-8 py-3 bg-gray-50 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-100 transition">
                View Weekly Schedule
            </button>
        </div>
    </div>
</div>