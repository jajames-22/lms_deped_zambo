<div class="flex-1 overflow-y-auto">
    <section class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->first_name }} 👋</h2>
            <p class="text-gray-500 mt-1">You've completed <span class="text-[#a52a2a] font-bold">80%</span> of your weekly goal. Keep it up!</p>
        </div>

        <button onclick="toggleAssessmentModal(true)"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all group">
            <i class="fas fa-edit group-hover:rotate-12 transition-transform"></i>
            <span>Take Assessment</span>
        </button>
    </section>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i class="fas fa-book text-xl"></i></div>
                <div><p class="text-xs text-gray-500 font-medium">Enrolled</p><p class="text-xl font-bold">12</p></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-green-50 text-green-600 rounded-xl"><i class="fas fa-check-circle text-xl"></i></div>
                <div><p class="text-xs text-gray-500 font-medium">Completed</p><p class="text-xl font-bold">04</p></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-xl"><i class="fas fa-clock text-xl"></i></div>
                <div><p class="text-xs text-gray-500 font-medium">Hours Spent</p><p class="text-xl font-bold">58h</p></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-[#a52a2a]/10 text-[#a52a2a] rounded-xl"><i class="fas fa-certificate text-xl"></i></div>
                <div><p class="text-xs text-gray-500 font-medium">Certificates</p><p class="text-xl font-bold">02</p></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Continue Learning</h3>
                <a href="#" class="text-sm text-[#a52a2a] font-semibold hover:underline">View All</a>
            </div>
            
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center gap-4 hover:border-[#a52a2a]/20 transition group">
                <div class="w-full sm:w-24 h-24 rounded-xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=300" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Course">
                </div>
                <div class="flex-1 w-full">
                    <h4 class="font-bold text-gray-800">UI/UX Design Masterclass</h4>
                    <p class="text-xs text-gray-500 mb-3">Module 4: High-Fidelity Prototyping</p>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-[#a52a2a] h-2 rounded-full" style="width: 75%"></div>
                    </div>
                </div>
                <button class="bg-[#a52a2a] text-white px-6 py-2 rounded-lg text-sm font-semibold hover:opacity-90 transition w-full sm:w-auto text-center shadow-lg shadow-[#a52a2a]/20">
                    Continue
                </button>
            </div>
        </div>

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
                </div>
            </div>
        </div>
    </div>
</div>

<div id="assessmentModal" class="fixed inset-0 z-50 opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="toggleAssessmentModal(false)"></div>
    
    <div id="assessmentModalBox" class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform scale-95 transition-all duration-300 border border-gray-100">
        <div class="w-16 h-16 bg-[#a52a2a]/10 text-[#a52a2a] rounded-2xl flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-key text-2xl"></i>
        </div>
        
        <div class="text-center mb-4">
            <h3 class="text-2xl font-bold text-gray-900">Assessment Code</h3>
            <p class="text-gray-500 text-sm">Enter the code provided by your teacher.</p>
        </div>

        <form onsubmit="handleCode(event)" class="space-y-6">
            <input type="text" id="assessment_code" required placeholder="CODE-123"
                class="w-full px-5 py-4 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-2xl transition-all outline-none text-center font-bold uppercase text-gray-800 text-lg">

            <div class="flex gap-3">
                <button type="button" onclick="toggleAssessmentModal(false)" 
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-800 transition">
                    Start
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // CHANGED: Updated toggle to handle opacity and scale instead of 'hidden'
    function toggleAssessmentModal(show) {
        const modal = document.getElementById('assessmentModal');
        const modalBox = document.getElementById('assessmentModalBox');
        
        if (show) {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBox.classList.remove('scale-95');
            modalBox.classList.add('scale-100');
        } else {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');
        }
    }

    function handleCode(e) {
        e.preventDefault();
        const code = document.getElementById('assessment_code').value;
        alert('You entered: ' + code);
        toggleAssessmentModal(false);
    }
</script>