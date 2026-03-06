<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Student Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="p-6 max-w-4xl mx-auto pb-20">
    
    <div class="bg-[#a52a2a]/10 border-2 border-[#a52a2a]/30 rounded-2xl p-6 text-center mb-8 relative overflow-hidden">
        <i class="fas fa-key absolute -right-4 -bottom-4 text-8xl text-[#a52a2a]/10"></i>
        <h3 class="text-[#a52a2a] font-bold uppercase tracking-wider text-sm mb-1">Student Access Key</h3>
        <h1 class="text-5xl font-mono font-black text-gray-900 tracking-[0.2em]">{{ $assessment->access_key }}</h1>
        <p class="text-gray-600 mt-2 text-sm">Share this 6-digit key with your students to access: <b>{{ $assessment->title }}</b></p>
    </div>

    <div id="builder-container" class="space-y-6">
        </div>

    <button onclick="addCategory()" class="w-full mt-6 px-4 py-4 border-2 border-dashed border-gray-300 text-gray-500 font-semibold rounded-xl hover:bg-gray-50 hover:border-[#a52a2a] hover:text-[#a52a2a] transition flex items-center justify-center gap-2">
        <i class="fas fa-plus-circle"></i> Add Exam Category (e.g., English, Math)
    </button>

    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end">
        <button onclick="saveCompleteExam()" class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-600/30">
            Publish Complete Exam <i class="fas fa-check-double ml-2"></i>
        </button>
    </div>
</div>

<script>
    let catCount = 0;

    // Load initial category on start
    setTimeout(addCategory, 100);

    function addCategory() {
        catCount++;
        const html = `
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 category-block" id="cat-${catCount}">
                <div class="flex gap-4 mb-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category Title</label>
                        <input type="text" class="c-title w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-[#a52a2a]" placeholder="e.g., Part 1: English">
                    </div>
                    <div class="w-1/3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Timer (Minutes)</label>
                        <input type="number" class="c-time w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-[#a52a2a]" placeholder="e.g., 5" min="1">
                    </div>
                </div>
                
                <div id="q-container-${catCount}" class="space-y-4 mb-4 pl-4 border-l-2 border-gray-100"></div>
                
                <button onclick="addQuestion(${catCount})" class="text-sm text-[#a52a2a] font-semibold hover:underline">
                    + Add Question to this Category
                </button>
            </div>
        `;
        document.getElementById('builder-container').insertAdjacentHTML('beforeend', html);
        addQuestion(catCount);
    }

    function addQuestion(cId) {
        const container = document.getElementById(`q-container-${cId}`);
        const html = `
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 question-block">
                <input type="text" class="q-text w-full px-3 py-2 mb-3 border border-gray-300 rounded-lg outline-none focus:border-[#a52a2a]" placeholder="Type your question here...">
                
                <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                    <div class="flex items-center gap-2"><span class="font-bold text-gray-400">A</span><input type="text" class="q-opt-a w-full px-2 py-1 border rounded" placeholder="Option A"></div>
                    <div class="flex items-center gap-2"><span class="font-bold text-gray-400">B</span><input type="text" class="q-opt-b w-full px-2 py-1 border rounded" placeholder="Option B"></div>
                    <div class="flex items-center gap-2"><span class="font-bold text-gray-400">C</span><input type="text" class="q-opt-c w-full px-2 py-1 border rounded" placeholder="Option C"></div>
                    <div class="flex items-center gap-2"><span class="font-bold text-gray-400">D</span><input type="text" class="q-opt-d w-full px-2 py-1 border rounded" placeholder="Option D"></div>
                </div>
                
                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-gray-500 uppercase">Correct Answer:</label>
                    <select class="q-correct px-3 py-1 border border-gray-300 rounded outline-none text-sm">
                        <option value="A">A</option><option value="B">B</option>
                        <option value="C">C</option><option value="D">D</option>
                    </select>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    async function saveCompleteExam() {
        const payload = { categories: [] };

        document.querySelectorAll('.category-block').forEach(cat => {
            const category = {
                title: cat.querySelector('.c-title').value,
                time_limit: cat.querySelector('.c-time').value,
                questions: []
            };
            cat.querySelectorAll('.question-block').forEach(q => {
                category.questions.push({
                    text: q.querySelector('.q-text').value,
                    optA: q.querySelector('.q-opt-a').value,
                    optB: q.querySelector('.q-opt-b').value,
                    optC: q.querySelector('.q-opt-c').value,
                    optD: q.querySelector('.q-opt-d').value,
                    correct: q.querySelector('.q-correct').value
                });
            });
            payload.categories.push(category);
        });

        try {
            const response = await fetch("{{ route('dashboard.assessments.store_questions', $assessment->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            if (response.ok) {
                alert('Exam successfully published!');
                loadPartial("{{ route('dashboard.home') }}", document.querySelector('.nav-btn')); // Send back to home
            }
        } catch (error) {
            console.error(error);
            alert('Server error.');
        }
    }
</script>

</body>
</html>