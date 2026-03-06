<div class="space-y-6 pb-20 w-full max-w-5xl mx-auto">
    
    <div class="flex items-center justify-between mb-2">
        <button onclick="loadPartial('{{ route('dashboard.assessment') }}', this)" class="text-gray-500 hover:text-[#a52a2a] transition flex items-center gap-2 font-semibold">
            <i class="fas fa-arrow-left"></i> Back to Assessments
        </button>
        <h2 class="text-xl font-bold text-gray-800">Assessment Builder</h2>
    </div>

    <div class="bg-[#a52a2a]/10 border-2 border-[#a52a2a]/30 rounded-2xl p-6 text-center relative overflow-hidden shadow-sm">
        <i class="fas fa-key absolute -right-4 -bottom-4 text-8xl text-[#a52a2a]/10"></i>
        <h3 class="text-[#a52a2a] font-bold uppercase tracking-wider text-sm mb-1">Student Access Key</h3>
        <h1 class="text-5xl font-mono font-black text-gray-900 tracking-[0.2em]">{{ $assessment->access_key }}</h1>
        <p class="text-gray-600 mt-2 text-sm">Share this 6-digit key with your students to access: <b>{{ $assessment->title }}</b></p>
    </div>

    <div id="builder-container" class="space-y-6">
        </div>

    <button onclick="window.addCategory()" class="w-full mt-6 px-4 py-4 border-2 border-dashed border-gray-300 text-gray-500 font-semibold rounded-xl hover:bg-[#a52a2a]/5 hover:border-[#a52a2a] hover:text-[#a52a2a] transition flex items-center justify-center gap-2 group">
        <i class="fas fa-plus-circle text-lg group-hover:scale-110 transition-transform"></i> Add Exam Category (e.g., English, Math, Logic)
    </button>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end">
        <button onclick="window.saveCompleteExam(this)" class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-600/30 flex items-center gap-2 active:scale-95">
            <span>Publish Complete Exam</span>
            <i class="fas fa-check-double"></i>
        </button>
    </div>
</div>

<script>
    window.catCount = 0;

    // Load initial category on start automatically
    setTimeout(() => {
        if(document.querySelectorAll('.category-block').length === 0) {
            window.addCategory();
        }
    }, 100);

    window.addCategory = function() {
        window.catCount++;
        const html = `
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 category-block relative transition-all" id="cat-${window.catCount}">
                <button onclick="window.removeElement('cat-${window.catCount}')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition" title="Delete Category">
                    <i class="fas fa-trash"></i>
                </button>

                <div class="flex gap-4 mb-4 pr-8">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category Title</label>
                        <input type="text" class="c-title w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none focus:bg-white focus:border-[#a52a2a] focus:ring-2 focus:ring-[#a52a2a]/20 transition" placeholder="e.g., Part 1: Multiple Choice">
                    </div>
                    <div class="w-1/3 max-w-[150px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Timer (Mins)</label>
                        <input type="number" class="c-time w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none focus:bg-white focus:border-[#a52a2a] focus:ring-2 focus:ring-[#a52a2a]/20 transition" placeholder="e.g., 15" min="1">
                    </div>
                </div>
                
                <div id="q-container-${window.catCount}" class="space-y-4 mb-4 pl-4 border-l-2 border-[#a52a2a]/20"></div>
                
                <button onclick="window.addQuestion(${window.catCount})" class="text-sm text-[#a52a2a] font-bold hover:underline flex items-center gap-1 mt-2">
                    <i class="fas fa-plus"></i> Add Question to Category
                </button>
            </div>
        `;
        document.getElementById('builder-container').insertAdjacentHTML('beforeend', html);
        window.addQuestion(window.catCount);
    };

    window.addQuestion = function(cId) {
        const container = document.getElementById(`q-container-${cId}`);
        const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        
        const html = `
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 question-block relative" id="${qId}">
                <button onclick="window.removeElement('${qId}')" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition text-sm">
                    <i class="fas fa-times"></i>
                </button>

                <input type="text" class="q-text w-full px-3 py-2 mb-4 border border-gray-300 rounded-lg outline-none focus:border-[#a52a2a] focus:ring-1 focus:ring-[#a52a2a] font-medium" placeholder="Type your question here...">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 text-sm">
                    <div class="flex items-center gap-2 bg-white p-1 rounded-lg border border-gray-200 focus-within:border-[#a52a2a]">
                        <span class="font-bold text-gray-400 pl-2">A</span>
                        <input type="text" class="q-opt-a w-full px-2 py-1 outline-none bg-transparent" placeholder="Option A">
                    </div>
                    <div class="flex items-center gap-2 bg-white p-1 rounded-lg border border-gray-200 focus-within:border-[#a52a2a]">
                        <span class="font-bold text-gray-400 pl-2">B</span>
                        <input type="text" class="q-opt-b w-full px-2 py-1 outline-none bg-transparent" placeholder="Option B">
                    </div>
                    <div class="flex items-center gap-2 bg-white p-1 rounded-lg border border-gray-200 focus-within:border-[#a52a2a]">
                        <span class="font-bold text-gray-400 pl-2">C</span>
                        <input type="text" class="q-opt-c w-full px-2 py-1 outline-none bg-transparent" placeholder="Option C">
                    </div>
                    <div class="flex items-center gap-2 bg-white p-1 rounded-lg border border-gray-200 focus-within:border-[#a52a2a]">
                        <span class="font-bold text-gray-400 pl-2">D</span>
                        <input type="text" class="q-opt-d w-full px-2 py-1 outline-none bg-transparent" placeholder="Option D">
                    </div>
                </div>
                
                <div class="flex items-center gap-3 bg-white p-2 rounded-lg border border-gray-200 w-fit">
                    <label class="text-xs font-bold text-gray-600 uppercase ml-2">Correct Answer:</label>
                    <select class="q-correct px-3 py-1 border-none bg-gray-50 rounded outline-none text-sm font-bold text-[#a52a2a] cursor-pointer">
                        <option value="option_a">Option A</option>
                        <option value="option_b">Option B</option>
                        <option value="option_c">Option C</option>
                        <option value="option_d">Option D</option>
                    </select>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    };

    window.removeElement = function(id) {
        const el = document.getElementById(id);
        if(el) {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 200);
        }
    };

    window.saveCompleteExam = async function(btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';

        const payload = { categories: [] };
        let isValid = true;
        
        const categoryBlocks = document.querySelectorAll('.category-block');
        if(categoryBlocks.length === 0) {
            alert("Please add at least one category.");
            window.resetBtn(btn);
            return;
        }

        categoryBlocks.forEach(cat => {
            const category = {
                title: cat.querySelector('.c-title').value,
                time_limit: cat.querySelector('.c-time').value,
                questions: []
            };

            if(!category.title || !category.time_limit) isValid = false;

            cat.querySelectorAll('.question-block').forEach(q => {
                const questionData = {
                    text: q.querySelector('.q-text').value,
                    optA: q.querySelector('.q-opt-a').value,
                    optB: q.querySelector('.q-opt-b').value,
                    optC: q.querySelector('.q-opt-c').value,
                    optD: q.querySelector('.q-opt-d').value,
                    correct: q.querySelector('.q-correct').value
                };

                if(!questionData.text || !questionData.optA || !questionData.optB) isValid = false;
                category.questions.push(questionData);
            });
            
            if(category.questions.length === 0) isValid = false;
            payload.categories.push(category);
        });

        if (!isValid) {
            alert("Validation Failed: Please ensure all categories have titles and timers, and all questions have text and options filled out.");
            window.resetBtn(btn);
            return;
        }

        try {
            const response = await fetch("{{ route('dashboard.assessments.store_questions', $assessment->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            const result = await response.json();

            if (response.ok && result.success) {
                alert('Exam successfully published to database!');
                loadPartial("{{ route('dashboard.assessment') }}"); 
            } else {
                throw new Error(result.message || 'Failed to save');
            }
        } catch (error) {
            console.error(error);
            alert('Server error: Could not publish exam. ' + error.message);
            window.resetBtn(btn);
        }
    };
    
    window.resetBtn = function(btn) {
        btn.disabled = false;
        btn.innerHTML = '<span>Publish Complete Exam</span> <i class="fas fa-check-double"></i>';
    };
</script>