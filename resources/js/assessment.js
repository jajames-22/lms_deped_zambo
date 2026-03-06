window.submitAssessmentSetup = async function (btn) {
    const title = document.getElementById("setup-title").value;
    const year = document.getElementById("setup-year").value;
    const desc = document.getElementById("setup-desc").value;

    // 1. Manual Validation
    if (!title || !year) {
        alert("Please fill out the Assessment Title and Year/Grade Level.");
        return;
    }

    const originalText = btn.innerHTML;

    // 2. Lock button
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proceeding...';

    try {
        // 3. Send to Laravel
        const response = await fetch(
            "{{ route('dashboard.assessments.store_setup') }}",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    title: title,
                    year_level: year,
                    description: desc,
                }),
            },
        );

        const data = await response.json();

        // 4. Smooth Transition (Keeps CSS Intact!)
        if (response.ok && data.success) {
            loadPartial(data.redirect_url);
        } else {
            alert("Error: " + (data.message || "Validation failed."));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error("Network Error:", error);
        alert("Server error. Check your console.");
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

//builder

window.catCount = 0;

// Load initial category on start automatically
setTimeout(() => {
    if (document.querySelectorAll(".category-block").length === 0) {
        window.addCategory();
    }
}, 100);

window.addCategory = function () {
    window.catCount++;
    const html = `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 category-block relative transition-all" id="cat-${window.catCount}">
            <button type="button" onclick="window.removeElement('cat-${window.catCount}')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition" title="Delete Category">
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
            
            <button type="button" onclick="window.addQuestion(${window.catCount})" class="text-sm text-[#a52a2a] font-bold hover:underline flex items-center gap-1 mt-2">
                <i class="fas fa-plus"></i> Add Question to Category
            </button>
        </div>
    `;
    document
        .getElementById("builder-container")
        .insertAdjacentHTML("beforeend", html);
    window.addQuestion(window.catCount);
};

window.addQuestion = function (cId) {
    const container = document.getElementById(`q-container-${cId}`);
    const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

    const html = `
        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 question-block relative" id="${qId}">
            <button type="button" onclick="window.removeElement('${qId}')" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition text-sm">
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
    container.insertAdjacentHTML("beforeend", html);
};

window.removeElement = function (id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 200);
    }
};

window.submitAssessmentSetup = async function (btn) {
    const title = document.getElementById("setup-title")?.value;
    const year = document.getElementById("setup-year")?.value;
    const desc = document.getElementById("setup-desc")?.value;

    if (!title || !year) {
        alert("Please fill out the Assessment Title and Year/Grade Level.");
        return;
    }

    const wrapper = document.getElementById("setup-wrapper");
    if (!wrapper) {
        alert("System Error: Route setup missing. Please refresh.");
        return;
    }

    const setupUrl = wrapper.dataset.setupUrl;
    const csrfToken = wrapper.dataset.csrf;
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proceeding...';

    try {
        const response = await fetch(setupUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                title: title,
                year_level: year,
                description: desc,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            if(typeof loadPartial === 'function') {
                loadPartial(data.redirect_url, document.querySelector('.nav-btn.active'));
            } else {
                window.location.href = data.redirect_url;
            }
        } else {
            alert("Error: " + (data.message || "Validation failed."));
            window.resetBtn(btn, originalText);
        }
    } catch (error) {
        console.error("Network Error:", error);
        alert("Server error. Check your console.");
        window.resetBtn(btn, originalText);
    }
};

// Builder Phase 
window.catCount = 0;

window.initBuilder = function() {
    const container = document.getElementById("builder-container");
    if (container && document.querySelectorAll(".category-block").length === 0) {
        window.addCategory();
    }
};

window.addCategory = function () {
    const container = document.getElementById("builder-container");
    if (!container) return;

    window.catCount++;
    const html = `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 category-block relative transition-all" id="cat-${window.catCount}">
            <button type="button" onclick="window.removeElement('cat-${window.catCount}')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition" title="Delete Category">
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
            
            <button type="button" onclick="window.addQuestion(${window.catCount})" class="text-sm text-[#a52a2a] font-bold hover:underline flex items-center gap-1 mt-2">
                <i class="fas fa-plus"></i> Add Question to Category
            </button>
        </div>
    `;
    container.insertAdjacentHTML("beforeend", html);
    window.addQuestion(window.catCount);
};

window.addQuestion = function (cId) {
    const container = document.getElementById(`q-container-${cId}`);
    if(!container) return;

    const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

    const html = `
        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 question-block relative" id="${qId}">
            <button type="button" onclick="window.removeElement('${qId}')" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition text-sm">
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
    container.insertAdjacentHTML("beforeend", html);
};

window.removeElement = function (id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 200);
    }
};

window.saveCompleteExam = async function (btn, examStatus) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return;

    const saveUrl = wrapper.dataset.saveUrl;
    const csrfToken = wrapper.dataset.csrf;
    const redirectUrl = wrapper.dataset.redirectUrl;

    const payload = {
        status: examStatus, 
        categories: [],
    };
    
    let isValid = true;
    const categoryBlocks = document.querySelectorAll(".category-block");
    if (categoryBlocks.length === 0) {
        alert("Please add at least one category.");
        window.resetBtn(btn, originalText);
        return;
    }

    categoryBlocks.forEach((cat) => {
        const category = {
            title: cat.querySelector(".c-title").value,
            time_limit: cat.querySelector(".c-time").value,
            questions: [],
        };

        if (!category.title || !category.time_limit) isValid = false;

        cat.querySelectorAll(".question-block").forEach((q) => {
            const questionData = {
                text: q.querySelector(".q-text").value,
                optA: q.querySelector(".q-opt-a").value,
                optB: q.querySelector(".q-opt-b").value,
                optC: q.querySelector(".q-opt-c").value,
                optD: q.querySelector(".q-opt-d").value,
                correct: q.querySelector(".q-correct").value,
            };

            if (!questionData.text || !questionData.optA || !questionData.optB) isValid = false;
            category.questions.push(questionData);
        });

        if (category.questions.length === 0) isValid = false;
        payload.categories.push(category);
    });

    if (!isValid) {
        alert("Validation Failed: Please ensure all categories have titles/timers, and all questions have text/options filled out.");
        window.resetBtn(btn, originalText);
        return;
    }

    try {
        const response = await fetch(saveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json",
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert(examStatus === "published" ? "Exam published and opened!" : "Exam saved as draft!");
            if(typeof loadPartial === 'function') {
                loadPartial(redirectUrl, document.querySelector('.nav-btn.active'));
            } else {
                window.location.href = redirectUrl;
            }
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (error) {
        console.error(error);
        alert("Server error: Could not save exam. " + error.message);
        window.resetBtn(btn, originalText);
    }
};

window.resetBtn = function (btn, originalText) {
    btn.disabled = false;
    btn.innerHTML = originalText;
};

window.saveCompleteExam = async function (btn, examStatus) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    // Retrieve Laravel variables from the DOM wrapper
    const wrapper = document.getElementById("assessment-wrapper");
    const saveUrl = wrapper.dataset.saveUrl;
    const csrfToken = wrapper.dataset.csrf;
    const redirectUrl = wrapper.dataset.redirectUrl;

    const payload = {
        status: examStatus, // Will be 'draft' or 'published'
        categories: [],
    };
    let isValid = true;

    const categoryBlocks = document.querySelectorAll(".category-block");
    if (categoryBlocks.length === 0) {
        alert("Please add at least one category.");
        window.resetBtn(btn, originalText);
        return;
    }

    categoryBlocks.forEach((cat) => {
        const category = {
            title: cat.querySelector(".c-title").value,
            time_limit: cat.querySelector(".c-time").value,
            questions: [],
        };

        if (!category.title || !category.time_limit) isValid = false;

        cat.querySelectorAll(".question-block").forEach((q) => {
            const questionData = {
                text: q.querySelector(".q-text").value,
                optA: q.querySelector(".q-opt-a").value,
                optB: q.querySelector(".q-opt-b").value,
                optC: q.querySelector(".q-opt-c").value,
                optD: q.querySelector(".q-opt-d").value,
                correct: q.querySelector(".q-correct").value,
            };

            if (!questionData.text || !questionData.optA || !questionData.optB)
                isValid = false;
            category.questions.push(questionData);
        });

        if (category.questions.length === 0) isValid = false;
        payload.categories.push(category);
    });

    if (!isValid) {
        alert(
            "Validation Failed: Please ensure all categories have titles and timers, and all questions have text and options filled out.",
        );
        window.resetBtn(btn, originalText);
        return;
    }

    try {
        const response = await fetch(saveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            const successMsg =
                examStatus === "published"
                    ? "Exam published and opened!"
                    : "Exam saved as draft!";
            alert(successMsg);
            loadPartial(redirectUrl);
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (error) {
        console.error(error);
        alert("Server error: Could not save exam. " + error.message);
        window.resetBtn(btn, originalText);
    }
};

window.resetBtn = function (btn, originalText) {
    btn.disabled = false;
    btn.innerHTML = originalText;
};
