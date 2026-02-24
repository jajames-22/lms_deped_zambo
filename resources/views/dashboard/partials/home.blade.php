<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <main>
            <!-- Dashboard Body -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8">
                <!-- Welcome Section -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome back, Alex! 👋</h2>
                    <p class="text-gray-500 mt-1">You've completed <span class="text-[#a52a2a] font-semibold">80%</span> of your weekly goal. Keep it up!</p>
                </section>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i class="fas fa-book text-xl"></i></div>
                            <div><p class="text-xs text-gray-500 font-medium">Enrolled</p><p class="text-xl font-bold">12</p></div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-green-50 text-green-600 rounded-xl"><i class="fas fa-check-circle text-xl"></i></div>
                            <div><p class="text-xs text-gray-500 font-medium">Completed</p><p class="text-xl font-bold">04</p></div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-yellow-50 text-yellow-600 rounded-xl"><i class="fas fa-clock text-xl"></i></div>
                            <div><p class="text-xs text-gray-500 font-medium">Hours Spent</p><p class="text-xl font-bold">58h</p></div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-[#a52a2a]/10 text-[#a52a2a] rounded-xl"><i class="fas fa-certificate text-xl"></i></div>
                            <div><p class="text-xs text-gray-500 font-medium">Certificates</p><p class="text-xl font-bold">02</p></div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Continue Learning -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800">Continue Learning</h3>
                            <a href="#" class="text-sm text-[#a52a2a] font-semibold hover:underline">View All</a>
                        </div>
                        
                        <!-- Course Card -->
                        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center gap-4 hover:border-[#a52a2a]/20 transition group">
                            <div class="w-full sm:w-24 h-24 rounded-xl overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=300" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Course">
                            </div>
                            <div class="flex-1 w-full">
                                <h4 class="font-bold text-gray-800">UI/UX Design Masterclass</h4>
                                <p class="text-xs text-gray-500 mb-3">Module 4: High-Fidelity Prototyping</p>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-[#a52a2a] h-2 rounded-full shadow-[0_0_8px_rgba(165,42,42,0.3)]" style="width: 75%"></div>
                                </div>
                            </div>
                            <a href="#" class="bg-[#a52a2a] text-white px-6 py-2 rounded-lg text-sm font-semibold hover:opacity-90 transition w-full sm:w-auto text-center shadow-lg shadow-[#a52a2a]/20">
                                Continue
                            </a>
                        </div>
                    </div>

                    <!-- Side Widgets -->
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Upcoming Deadlines</h3>
                            <div class="space-y-4">
                                <div class="flex items-center p-3 bg-[#a52a2a]/5 rounded-xl border border-[#a52a2a]/10">
                                    <div class="w-10 h-10 bg-[#a52a2a] text-white rounded-lg flex items-center justify-center mr-3 font-bold text-[10px] leading-tight flex-shrink-0 text-center uppercase">
                                        24<br>MAR
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-800 truncate">Final Exam: UI Design</p>
                                        <p class="text-xs text-[#a52a2a] font-medium">Due: 05:00 PM</p>
                                    </div>
                                </div>
                                <div class="flex items-center p-3 bg-gray-50 rounded-xl border border-transparent">
                                    <div class="w-10 h-10 bg-gray-200 text-gray-600 rounded-lg flex items-center justify-center mr-3 font-bold text-[10px] leading-tight flex-shrink-0 text-center uppercase">
                                        28<br>MAR
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-800 truncate">Python Project Submission</p>
                                        <p class="text-xs text-gray-500">Due: 11:59 PM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
</body>
</html>