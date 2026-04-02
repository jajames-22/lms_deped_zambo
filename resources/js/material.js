window.MaterialBuilder = window.MaterialBuilder || {};

// Scope all state variables to the MaterialBuilder namespace
MaterialBuilder.state = {
    title: "",
    description: "",
    categories: [],
};

MaterialBuilder.hasChanged = false;
MaterialBuilder.catCount = 0;
MaterialBuilder.isInitializing = false;
MaterialBuilder.autosaveTimer = null;
MaterialBuilder.SYNC_DELAY = 3000;
MaterialBuilder.lastPayload = "";
MaterialBuilder.currentMediaUploadQId = null;
MaterialBuilder.selectedMediaFile = null;
MaterialBuilder.activityListenersAdded = false;

MaterialBuilder.initBuilder = function () {
    MaterialBuilder.isInitializing = true;
    MaterialBuilder.catCount = 0;
    MaterialBuilder.lastPayload = "";
    MaterialBuilder.hasChanged = false;
    clearTimeout(MaterialBuilder.autosaveTimer);

    const wrapper = document.getElementById("material-wrapper");
    const container = document.getElementById("builder-container");

    if (!wrapper || !container) return;

    container.innerHTML = "";

    const existingDataEl = document.getElementById("existing-data");
    let existingData = [];

    if (existingDataEl && existingDataEl.value) {
        try {
            existingData = JSON.parse(existingDataEl.value);
        } catch (e) {
            console.error("Failed to parse existing data", e);
        }
    }

    const serverDraftEl = document.getElementById("server-draft-data");
    if (serverDraftEl && serverDraftEl.value) {
        try {
            const serverDraft = JSON.parse(serverDraftEl.value);
            if (serverDraft && serverDraft.categories && serverDraft.categories.length > 0) {
                existingData = serverDraft.categories;
                if (serverDraft.title) document.getElementById("setup-title").value = serverDraft.title;
                if (serverDraft.description) document.getElementById("setup-desc").value = serverDraft.description;
            }
        } catch (e) {
            console.warn("Invalid server draft JSON");
        }
    }

    if (existingData && existingData.length > 0) {
        existingData.forEach((cat) => {
            MaterialBuilder.renderExistingCategory(cat);
        });
        MaterialBuilder.updateCategoryNumbers(); 
    }

    wrapper.addEventListener("input", (e) => {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) {
            MaterialBuilder.handleAutosaveTrigger();
        }
    });

    if (!MaterialBuilder.activityListenersAdded) {
        const resetTimer = () => MaterialBuilder.resetIdleTimer();
        window.addEventListener('mousemove', resetTimer);
        window.addEventListener('keydown', resetTimer);
        window.addEventListener('mousedown', resetTimer);
        window.addEventListener('touchstart', resetTimer);
        window.addEventListener('scroll', resetTimer, true);
        MaterialBuilder.activityListenersAdded = true;
    }

    MaterialBuilder.updateAutosaveIndicator("Ready");
    setTimeout(() => {
        MaterialBuilder.isInitializing = false;
        MaterialBuilder.hasChanged = false; 
    }, 500);
};

MaterialBuilder.renderExistingCategory = function (catData) {
    const sectionType = catData.section_type || 'lesson'; 
    MaterialBuilder.addSection(sectionType);
    
    const latestCat = document.querySelector(".category-block:last-child");

    latestCat.querySelector(".c-title").value = catData.title || "";
    latestCat.querySelector(".category-display-title").innerText = catData.title || (sectionType === 'exam' ? "Final Exam" : "New Lesson");

    const qContainer = latestCat.querySelector('[id^="q-container-"]');
    qContainer.innerHTML = "";

    if (catData.questions && catData.questions.length > 0) {
        catData.questions.forEach((q) => {
            const subType = q.type || "content"; 
            
            let mainType = 'quiz';
            if (sectionType === 'exam') mainType = 'exam';
            if (subType === 'content' || subType === 'instruction') mainType = 'content';

            MaterialBuilder.addItem(qContainer.id.split("-").pop(), mainType, subType);
            
            const latestQ = qContainer.querySelector(".question-block:last-child");
            latestQ.querySelector(".q-text").value = q.text || q.question_text || "";

            const mediaUrl = q.media_url || q.image_url; 
            if (mediaUrl) {
                MaterialBuilder.setMediaPreview(latestQ.id, mediaUrl);
            }
            
            latestQ.querySelector(".options-list").innerHTML = "";

            if (q.options && q.options.length > 0 && mainType !== 'content') {
                const isCaseSensitive = q.is_case_sensitive == 1 || q.is_case_sensitive === true;
                q.options.forEach((opt) => {
                    MaterialBuilder.addOptionToQuestion(
                        latestQ.id,
                        subType,
                        opt.is_correct == 1 || opt.is_correct === true,
                        opt.text || opt.option_text || "",
                        isCaseSensitive
                    );
                });
            } else if (subType === "true_false" && mainType !== 'content') {
                MaterialBuilder.addOptionToQuestion(latestQ.id, "true_false", false, "True");
                MaterialBuilder.addOptionToQuestion(latestQ.id, "true_false", false, "False");
            }
        });
    }
};

MaterialBuilder.addSection = function (type = 'lesson') {
    const container = document.getElementById("builder-container");
    if (!container) return;

    MaterialBuilder.catCount++;
    const catId = `cat-${MaterialBuilder.catCount}`;

    let badgeColor = type === 'exam' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600';
    let borderColor = type === 'exam' ? 'border-red-200' : 'border-blue-200';
    let titleDefault = type === 'exam' ? 'Final Exam' : 'New Lesson';
    let icon = type === 'exam' ? 'fa-file-signature' : 'fa-book-open';

    let controlsHtml = '';
    if(type === 'lesson') {
        controlsHtml = `
            <div class="flex flex-col md:flex-row items-center gap-3">
                <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'content')" class="flex-1 w-full py-3 text-blue-600 bg-blue-50 text-sm font-bold hover:bg-blue-100 transition rounded-xl border border-blue-100 flex items-center justify-center shadow-sm">
                    <i class="fas fa-align-left mr-2"></i> Add Lesson Content
                </button>
                
                <div class="relative flex-1 w-full group/dropdown">
                    <button type="button" onclick="MaterialBuilder.toggleDropdown(this)" class="w-full py-3 text-purple-600 bg-purple-50 text-sm font-bold hover:bg-purple-100 transition rounded-xl border border-purple-100 flex items-center justify-center shadow-sm">
                        <i class="fas fa-question-circle mr-2"></i> Add Practice Quiz <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div class="hidden absolute bottom-full right-0 mb-2 w-full bg-white border border-gray-100 rounded-xl shadow-lg z-20 py-1 overflow-hidden dropdown-menu">
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'quiz', 'mcq'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600"><i class="fas fa-dot-circle w-5 text-center text-gray-400 mr-2"></i> Multiple Choice</button>
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'quiz', 'checkbox'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600"><i class="fas fa-check-square w-5 text-center text-gray-400 mr-2"></i> Checkboxes</button>
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'quiz', 'text'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600"><i class="fas fa-pencil-alt w-5 text-center text-gray-400 mr-2"></i> Short Text</button>
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'quiz', 'true_false'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600"><i class="fas fa-adjust w-5 text-center text-gray-400 mr-2"></i> True or False</button>
                    </div>
                </div>
            </div>`;
    } else {
        controlsHtml = `
            <div class="relative w-full group/dropdown">
                <button type="button" onclick="MaterialBuilder.toggleDropdown(this)" class="w-full py-3 text-red-600 bg-red-50 text-sm font-bold hover:bg-red-100 transition rounded-xl border border-red-100 flex items-center justify-center shadow-sm">
                    <i class="fas fa-plus-circle mr-2"></i> Add Exam Question <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                <div class="hidden absolute bottom-full right-0 mb-2 w-full bg-white border border-gray-100 rounded-xl shadow-lg z-20 py-1 overflow-hidden dropdown-menu">
                    <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'exam', 'mcq'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600"><i class="fas fa-dot-circle w-5 text-center text-gray-400 mr-2"></i> Multiple Choice</button>
                    <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'exam', 'checkbox'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600"><i class="fas fa-check-square w-5 text-center text-gray-400 mr-2"></i> Checkboxes</button>
                    <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'exam', 'text'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600"><i class="fas fa-pencil-alt w-5 text-center text-gray-400 mr-2"></i> Short Text</button>
                    <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'exam', 'true_false'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600"><i class="fas fa-adjust w-5 text-center text-gray-400 mr-2"></i> True or False</button>
                </div>
            </div>`;
    }

    // NEW: Only render move buttons if it is NOT an exam
    let moveButtonsHtml = '';
    if (type !== 'exam') {
        moveButtonsHtml = `
            <button type="button" onclick="MaterialBuilder.moveCategoryUp(this, event)" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200 rounded transition" title="Move Section Up"><i class="fas fa-arrow-up"></i></button>
            <button type="button" onclick="MaterialBuilder.moveCategoryDown(this, event)" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200 rounded transition" title="Move Section Down"><i class="fas fa-arrow-down"></i></button>
        `;
    }

    const html = `
        <div class="bg-white rounded-2xl shadow-sm border ${borderColor} category-block overflow-hidden transition-all mb-4" id="${catId}" data-section-type="${type}">
            <div class="p-4 bg-gray-50/50 flex items-center justify-between cursor-pointer group" onclick="MaterialBuilder.toggleCategory('${catId}', event)">
                <div class="flex items-center gap-4 flex-1">
                    <div class="h-8 w-8 rounded-lg ${badgeColor} flex items-center justify-center font-bold text-sm cat-number-badge">
                        <i class="fas ${icon}"></i>
                    </div>
                    <span class="font-bold text-gray-700 category-display-title">${titleDefault}</span>
                </div>
                
                <div class="flex items-center gap-1">
                    ${moveButtonsHtml}
                    <button type="button" onclick="MaterialBuilder.removeElement('${catId}')" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition ml-2" title="Delete Section"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 category-body">
                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">${type === 'exam' ? 'Exam Title' : 'Lesson Title'}</label>
                    <input type="text" class="c-title w-full px-4 py-2 border border-gray-200 rounded-xl outline-none focus:border-[#a52a2a] transition font-medium" placeholder="e.g., ${type === 'exam' ? 'Final Examination' : 'Introduction to the Topic'}" onkeyup="MaterialBuilder.updateCatDisplay(this, '${titleDefault}')">
                </div>
                
                <div id="q-container-${MaterialBuilder.catCount}" class="space-y-4 mb-4"></div>
                
                ${controlsHtml}
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    MaterialBuilder.handleAutosaveTrigger();
};

MaterialBuilder.toggleDropdown = function (btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll(".dropdown-menu").forEach((el) => {
        if (el !== menu) el.classList.add("hidden");
    });
    menu.classList.toggle("hidden");
};

MaterialBuilder.addItem = function (cId, mainType, subType = 'content') {
    const container = document.getElementById(`q-container-${cId}`);
    if (!container) return;

    const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

    let placeholder = "Enter Text...";
    let icon = "fa-question-circle";
    let bgClass = "bg-gray-50";
    let typeDisplay = "";
    
    if (mainType === "content") {
        placeholder = "Write your lesson content here...";
        icon = "fa-align-left";
        bgClass = "bg-blue-50/30 border-blue-100";
        typeDisplay = "Lesson Content Block";
        subType = "content"; 
    } else if (mainType === "quiz") {
        bgClass = "bg-purple-50/30 border-purple-100";
        typeDisplay = "Practice Quiz: " + (subType === "mcq" ? "Multiple Choice" : subType === "checkbox" ? "Checkboxes" : subType === "text" ? "Short Text" : "True or False");
    } else if (mainType === "exam") {
        bgClass = "bg-red-50/30 border-red-100";
        typeDisplay = "Exam Question: " + (subType === "mcq" ? "Multiple Choice" : subType === "checkbox" ? "Checkboxes" : subType === "text" ? "Short Text" : "True or False");
    }

    const html = `
        <div class="p-4 rounded-xl border border-gray-100 question-block relative group ${bgClass}" id="${qId}" data-main-type="${mainType}" data-sub-type="${subType}">
            
            <div class="flex justify-between items-start mb-2 cursor-pointer" onclick="MaterialBuilder.toggleQuestion('${qId}', event)">
                <div class="flex items-center gap-2 overflow-hidden pr-2">
                    <div class="h-6 w-6 flex items-center justify-center text-gray-400 group-hover:text-gray-600 transition q-chevron-icon shrink-0">
                        <i class="fas fa-chevron-up text-xs"></i>
                    </div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1 shrink-0">
                        <i class="fas ${icon}"></i> ${typeDisplay}
                    </span>
                    <span class="text-xs text-gray-500 font-medium truncate ml-2 q-preview-text hidden"></span>
                </div>
                
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" onclick="MaterialBuilder.moveQuestionUp(this)" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-gray-600 transition rounded-md hover:bg-gray-200" title="Move Up"><i class="fas fa-arrow-up"></i></button>
                    <button type="button" onclick="MaterialBuilder.moveQuestionDown(this)" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-gray-600 transition rounded-md hover:bg-gray-200" title="Move Down"><i class="fas fa-arrow-down"></i></button>
                    <button type="button" onclick="MaterialBuilder.removeElement('${qId}')" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition rounded-md hover:bg-red-50 ml-1" title="Delete Block"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="question-body">
                <div class="relative mb-3">
                    <textarea class="q-text w-full pl-3 pr-10 py-3 bg-white border border-gray-200 rounded-lg outline-none font-medium text-sm focus:border-[#a52a2a] resize-y min-h-[60px]" placeholder="${placeholder}"></textarea>
                    <input type="hidden" class="q-media-url" value="">
                    
                    <button type="button" onclick="MaterialBuilder.openMediaModal('${qId}')" title="Attach Media" class="absolute right-2 top-2 h-8 w-8 flex items-center justify-center text-gray-400 hover:text-[#a52a2a] hover:bg-gray-100 rounded transition">
                        <i class="fas fa-photo-video"></i>
                    </button>
                </div>
                
                <div id="preview-${qId}" class="hidden"></div>
                
                <div class="options-list space-y-2 mb-3"></div>
                
                ${(mainType !== 'content' && (subType === "mcq" || subType === "checkbox")) ? `
                    <button type="button" onclick="MaterialBuilder.addOptionToQuestion('${qId}', '${subType}')" class="text-[10px] font-bold text-[#a52a2a] hover:underline uppercase flex items-center mt-2">
                        <i class="fas fa-plus mr-1"></i> Add Choice
                    </button>
                ` : ""}
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);

    if (mainType !== "content") {
        if (subType === "mcq" || subType === "checkbox") {
            MaterialBuilder.addOptionToQuestion(qId, subType, true, "");
            MaterialBuilder.addOptionToQuestion(qId, subType, false, "");
        } else if (subType === "text") {
            MaterialBuilder.addOptionToQuestion(qId, "text", true, "");
        } else if (subType === "true_false") {
            MaterialBuilder.addOptionToQuestion(qId, "true_false", false, "True");
            MaterialBuilder.addOptionToQuestion(qId, "true_false", false, "False");
        }
    }
};

MaterialBuilder.addOptionToQuestion = function (qId, type, isCorrect = false, text = "", isCaseSensitive = false) {
    const list = document.querySelector(`#${qId} .options-list`);
    if (!list) return;

    const optCount = list.querySelectorAll(".option-row").length + 1;
    let optHtml = "";

    if (type === "mcq") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm" placeholder="Choice ${optCount}..." value="${text}">
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition">
                        <input type="radio" name="correct-${qId}" class="is-correct-input cursor-pointer text-green-600 h-4 w-4" ${isCorrect ? "checked" : ""}>
                        <span class="text-[10px] font-bold uppercase">Correct</span>
                    </label>
                    <button type="button" onclick="MaterialBuilder.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle"></i></button>
                </div>
            </div>`;
    } else if (type === "checkbox") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm" placeholder="Choice ${optCount}..." value="${text}">
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition">
                        <input type="checkbox" class="is-correct-input cursor-pointer text-green-600 rounded h-4 w-4" ${isCorrect ? "checked" : ""}>
                        <span class="text-[10px] font-bold uppercase">Correct</span>
                    </label>
                    <button type="button" onclick="MaterialBuilder.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle"></i></button>
                </div>
            </div>`;
    } else if (type === "text") {
        optHtml = `
            <div class="flex items-center gap-3 bg-green-50/50 px-3 py-2 rounded-lg border border-green-200 option-row transition">
                <span class="text-[10px] font-bold text-green-600 uppercase shrink-0"><i class="fas fa-check mr-1"></i> Exact Match:</span>
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm font-medium" placeholder="Type exact answer..." value="${text}">
                <input type="hidden" class="is-correct-input" value="true" checked>
                <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 border-l border-green-200 pl-3 shrink-0">
                    <input type="checkbox" class="case-sensitive-input cursor-pointer h-4 w-4" ${isCaseSensitive ? "checked" : ""}>
                    <span class="text-[10px] font-bold uppercase">Case Sensitive</span>
                </label>
            </div>`;
    } else if (type === "true_false") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm font-bold text-gray-700 cursor-default" value="${text}" readonly>
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition">
                        <input type="radio" name="correct-${qId}" class="is-correct-input cursor-pointer text-green-600 h-4 w-4" ${isCorrect ? "checked" : ""}>
                        <span class="text-[10px] font-bold uppercase">Correct</span>
                    </label>
                </div>
            </div>`;
    }

    list.insertAdjacentHTML("beforeend", optHtml);
    MaterialBuilder.handleAutosaveTrigger();
};

MaterialBuilder.moveCategoryUp = function(btn, event) {
    event.stopPropagation();
    const catBlock = btn.closest('.category-block');
    const prev = catBlock.previousElementSibling;
    if (prev && prev.classList.contains('category-block')) {
        prev.insertAdjacentElement('beforebegin', catBlock);
        MaterialBuilder.handleAutosaveTrigger();
    }
};

MaterialBuilder.moveCategoryDown = function(btn, event) {
    event.stopPropagation();
    const catBlock = btn.closest('.category-block');
    const next = catBlock.nextElementSibling;
    if (next && next.classList.contains('category-block')) {
        next.insertAdjacentElement('afterend', catBlock);
        MaterialBuilder.handleAutosaveTrigger();
    }
};

MaterialBuilder.moveQuestionUp = function(btn) {
    const qBlock = btn.closest('.question-block');
    const prev = qBlock.previousElementSibling;
    if (prev && prev.classList.contains('question-block')) {
        prev.insertAdjacentElement('beforebegin', qBlock);
        MaterialBuilder.handleAutosaveTrigger();
    }
};

MaterialBuilder.moveQuestionDown = function(btn) {
    const qBlock = btn.closest('.question-block');
    const next = qBlock.nextElementSibling;
    if (next && next.classList.contains('question-block')) {
        next.insertAdjacentElement('afterend', qBlock);
        MaterialBuilder.handleAutosaveTrigger();
    }
};

MaterialBuilder.updateCategoryNumbers = function() {};

MaterialBuilder.removeOption = function (btnElement, qId) {
    btnElement.closest(".option-row").remove();
    MaterialBuilder.handleAutosaveTrigger();
};

MaterialBuilder.removeElement = function (id) {
    const el = document.getElementById(id);
    if (el) el.remove();
    MaterialBuilder.handleAutosaveTrigger();
};

MaterialBuilder.toggleCategory = function (id, event) {
    if (["INPUT", "BUTTON", "I"].includes(event.target.tagName)) return;
    const body = document.querySelector(`#${id} .category-body`);
    body.classList.toggle("hidden");
};

MaterialBuilder.toggleQuestion = function (id, event) {
    if (event && event.target.closest("button")) return;
    
    const block = document.getElementById(id);
    const body = block.querySelector(".question-body");
    const icon = block.querySelector(".q-chevron-icon i");
    const preview = block.querySelector(".q-preview-text");
    const textarea = block.querySelector(".q-text");

    body.classList.toggle("hidden");
    icon.classList.toggle("fa-chevron-down");
    icon.classList.toggle("fa-chevron-up");
    
    if (body.classList.contains("hidden")) {
        let text = textarea.value.trim();
        preview.innerText = text ? "- " + text : "- (Empty Block)";
        preview.classList.remove("hidden");
    } else {
        preview.classList.add("hidden");
    }
};

MaterialBuilder.updateCatDisplay = function (input, fallback) {
    const title = input.closest(".category-block").querySelector(".category-display-title");
    title.innerText = input.value || fallback;
};

MaterialBuilder.getPayload = function (status) {
    const categories = [];
    document.querySelectorAll(".category-block").forEach((cat) => {
        const questions = [];
        cat.querySelectorAll(".question-block").forEach((q) => {
            const options = [];
            
            if (q.dataset.mainType !== 'content') {
                q.querySelectorAll(".option-row").forEach((opt) => {
                    const isCorrectInput = opt.querySelector(".is-correct-input");
                    let isCorrect = (isCorrectInput.type === "radio" || isCorrectInput.type === "checkbox") ? isCorrectInput.checked : true;
                    options.push({ text: opt.querySelector(".option-input").value, is_correct: isCorrect ? 1 : 0 });
                });
            }

            questions.push({
                type: q.dataset.subType, 
                text: q.querySelector(".q-text").value,
                media_url: q.querySelector(".q-media-url") ? q.querySelector(".q-media-url").value : null,
                is_case_sensitive: q.querySelector(".case-sensitive-input") ? q.querySelector(".case-sensitive-input").checked : false,
                options: options,
            });
        });
        
        categories.push({
            section_type: cat.dataset.sectionType, 
            title: cat.querySelector(".c-title").value,
            time_limit: 0, 
            questions: questions,
        });
    });

    const titleEl = document.getElementById("setup-title");
    const descEl = document.getElementById("setup-desc");

    return {
        status,
        title: titleEl ? titleEl.value : "",
        description: descEl ? descEl.value : "",
        categories,
    };
};


MaterialBuilder.handleAutosaveTrigger = function () {
    if (MaterialBuilder.isInitializing) return; 
    
    MaterialBuilder.hasChanged = true;
    MaterialBuilder.updateAutosaveIndicator('<i class="fas fa-circle-notch text-amber-500"></i> Unsaved changes...');
    MaterialBuilder.resetIdleTimer();
};

MaterialBuilder.resetIdleTimer = function () {
    clearTimeout(MaterialBuilder.autosaveTimer);
    
    if (MaterialBuilder.hasChanged && !MaterialBuilder.isInitializing) {
        MaterialBuilder.autosaveTimer = setTimeout(() => { 
            MaterialBuilder.autosaveToServer(); 
        }, MaterialBuilder.SYNC_DELAY);
    }
};

MaterialBuilder.autosaveToServer = async function () {
    const wrapper = document.getElementById("material-wrapper"); 
    if (!wrapper) return; 

    const payload = MaterialBuilder.getPayload("draft");
    const payloadString = JSON.stringify(payload);
    
    if (payloadString === MaterialBuilder.lastPayload) {
        MaterialBuilder.hasChanged = false; // Reset if nothing changed
        return;
    }
    MaterialBuilder.lastPayload = payloadString;

    try {
        await fetch(wrapper.dataset.autosaveUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": wrapper.dataset.csrf, Accept: "application/json" },
            body: payloadString,
        });
        MaterialBuilder.updateAutosaveIndicator('<i class="fas fa-check-circle text-green-500"></i> Synced');
        MaterialBuilder.hasChanged = false; // Reset flag after successful sync
    } catch (e) {
        MaterialBuilder.updateAutosaveIndicator('<i class="fas fa-wifi-slash text-amber-500"></i> Offline');
    }
};

MaterialBuilder.updateAutosaveIndicator = function (html) {
    const el = document.getElementById("autosave-indicator");
    if (el) el.innerHTML = html;
};

MaterialBuilder.openMediaModal = function (qId) {
    MaterialBuilder.currentMediaUploadQId = qId;
    MaterialBuilder.clearSelectedMedia();
    document.getElementById("media-upload-modal").classList.remove("hidden");
};

MaterialBuilder.closeMediaModal = function () {
    document.getElementById("media-upload-modal").classList.add("hidden");
    MaterialBuilder.currentMediaUploadQId = null;
};

MaterialBuilder.handleMediaFileSelect = function (input) {
    if (!input.files || input.files.length === 0) return;

    MaterialBuilder.selectedMediaFile = input.files[0];
    document.getElementById("selected-media-name").innerText = MaterialBuilder.selectedMediaFile.name;

    document.getElementById("media-dropzone").classList.add("hidden");
    document.getElementById("selected-media-display").classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("flex");

    document.getElementById("start-media-upload-btn").disabled = false;
};

MaterialBuilder.clearSelectedMedia = function () {
    MaterialBuilder.selectedMediaFile = null;
    document.getElementById("media-file-input").value = "";

    document.getElementById("media-dropzone").classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("hidden");
    document.getElementById("selected-media-display").classList.remove("flex");

    document.getElementById("start-media-upload-btn").disabled = true;
};

MaterialBuilder.executeMediaUpload = async function () {
    if (!MaterialBuilder.selectedMediaFile || !MaterialBuilder.currentMediaUploadQId) return;

    const wrapper = document.getElementById("material-wrapper"); 
    if (!wrapper) return; // Dataset Guard

    const btn = document.getElementById("start-media-upload-btn");
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Uploading...</span>';
    btn.disabled = true;

    const formData = new FormData();
    formData.append("media_file", MaterialBuilder.selectedMediaFile);

    try {
        const response = await fetch(wrapper.dataset.uploadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            MaterialBuilder.setMediaPreview(
                MaterialBuilder.currentMediaUploadQId,
                data.media_url,
                data.media_type,
            );
            MaterialBuilder.closeMediaModal();
            MaterialBuilder.handleAutosaveTrigger();
        } else {
            alert(data.message || "Failed to upload media.");
        }
    } catch (e) {
        console.error(e);
        alert("Upload failed. Check console.");
    } finally {
        btn.innerHTML = originalHtml;
    }
};

MaterialBuilder.setMediaPreview = function (qId, url, explicitType = null) {
    const block = document.getElementById(qId);

    const mediaInput = block.querySelector(".q-media-url") || block.querySelector(".q-image-url");
    if (mediaInput) mediaInput.value = url;

    const previewDiv = document.getElementById(`preview-${qId}`);

    let type = explicitType;
    if (!type) {
        const cleanUrl = url.split("?")[0];
        const extension = cleanUrl.split(".").pop().toLowerCase();
        if (["mp3", "wav", "ogg"].includes(extension)) type = "audio";
        else if (["mp4", "webm"].includes(extension)) type = "video";
        else type = "image";
    }

    let mediaHtml = "";
    if (type === "audio") {
        mediaHtml = `<audio controls src="${url}" class="w-full mt-2 outline-none"></audio>`;
    } else if (type === "video") {
        mediaHtml = `<video controls src="${url}" class="max-h-64 w-full object-contain bg-black rounded-lg"></video>`;
    } else {
        mediaHtml = `<img src="${url}" class="max-h-64 w-auto object-contain rounded-lg">`;
    }

    previewDiv.className = "relative mt-3 mb-4 rounded-lg overflow-hidden border border-gray-200 flex flex-col items-center justify-center w-full bg-gray-50 p-2";

    previewDiv.innerHTML = `
        ${mediaHtml}
        <button type="button" onclick="MaterialBuilder.removeQuestionMedia('${qId}')" class="absolute top-2 right-2 h-8 w-8 bg-red-500/80 hover:bg-red-600 text-white rounded flex items-center justify-center backdrop-blur-sm transition z-10 shadow-sm">
            <i class="fas fa-trash text-sm"></i>
        </button>
    `;
};

MaterialBuilder.removeQuestionMedia = function (qId) {
    const block = document.getElementById(qId);
    block.querySelector(".q-media-url").value = "";

    const previewDiv = document.getElementById(`preview-${qId}`);
    if (previewDiv) {
        previewDiv.innerHTML = "";
        previewDiv.className = "hidden";
    }

    MaterialBuilder.handleAutosaveTrigger();
};

MaterialBuilder.collectCategoriesData = function () {
    return MaterialBuilder.getPayload("draft").categories;
};

MaterialBuilder.saveCompleteMaterial = async function (btn, status) { 
    clearTimeout(MaterialBuilder.autosaveTimer);
    MaterialBuilder.lastPayload = "";

    const wrapper = document.getElementById("material-wrapper");
    if (!wrapper) return; // Dataset Guard

    const payload = MaterialBuilder.getPayload(status);

    if (!payload.title) {
        return MaterialBuilder.showModal("warning", "Missing Information", "Please fill out the Module Title before saving.");
    }

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

    const formData = new FormData();
    formData.append('title', payload.title);
    formData.append('description', payload.description);
    formData.append('status', status);
    formData.append('categories', JSON.stringify(payload.categories));
    
    const thumbInput = document.getElementById('thumbnail-upload');
    if (thumbInput && thumbInput.files.length > 0) {
        formData.append('thumbnail', thumbInput.files[0]);
    }

    try {
        const response = await fetch(wrapper.dataset.saveUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
            body: formData,
        });
        const result = await response.json();

        if (response.ok && result.success) {
            localStorage.removeItem("material_draft_" + wrapper.dataset.materialId);

            const title = status === "published" ? "Module Published!" : "Draft Saved!";
            const msg = status === "published" ? "Your material is live and ready." : "Your progress has been safely stored.";

            MaterialBuilder.showModal("success", title, msg, () => {
                MaterialBuilder.goToUrl(wrapper.dataset.manageUrl); 
            });
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (e) {
        MaterialBuilder.showModal("error", "Save Failed", e.message);
        MaterialBuilder.resetBtn(btn, originalText);
    }
};

MaterialBuilder.resetBtn = (btn, txt) => {
    btn.disabled = false;
    btn.innerHTML = txt;
};

window.addEventListener("beforeunload", (event) => {
    if (MaterialBuilder.lastPayload !== "") {
        event.preventDefault();
        event.returnValue = "";
    }
});

MaterialBuilder.discardChangesAndExit = function (btn) {
    MaterialBuilder.showModal(
        "confirm",
        "Discard Unsaved Changes?",
        "Are you sure you want to discard your unsaved work and exit? This cannot be undone.",
        async () => {
            const wrapper = document.getElementById("material-wrapper");
            if (!wrapper) return; // Dataset Guard

            const backModal = document.getElementById("back-modal");
            if(backModal) backModal.classList.add("hidden");

            clearTimeout(MaterialBuilder.autosaveTimer);
            MaterialBuilder.lastPayload = "";

            const isNew = wrapper.dataset.isNew === 'true'; 

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Discarding...';
            btn.disabled = true;

            localStorage.removeItem("material_draft_" + wrapper.dataset.materialId);

            try {
                if (isNew) {
                    await MaterialBuilder.silentlyDeleteAndExit();
                    MaterialBuilder.goToUrl(wrapper.dataset.redirectUrl);
                } else {
                    await fetch(wrapper.dataset.autosaveUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": wrapper.dataset.csrf,
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ clear_draft: true }),
                    });
                    
                    MaterialBuilder.goToUrl(wrapper.dataset.manageUrl); 
                }
            } catch (e) {
                console.warn("Failed to clear drafts or delete:", e);
                MaterialBuilder.goToUrl(isNew ? wrapper.dataset.redirectUrl : wrapper.dataset.manageUrl); 
            }
        },
    );
};

const backModal = document.getElementById("back-modal");
if (backModal) {
    backModal.addEventListener("click", function (e) {
        if (e.target.classList.contains("backdrop-blur-sm")) {
            this.classList.add("hidden");
        }
    });
}

MaterialBuilder.handleBackButton = async function(btn) {
    const wrapper = document.getElementById("material-wrapper");
    if (!wrapper) return; // Dataset Guard

    const isNew = wrapper.dataset.isNew === 'true';

    if (MaterialBuilder.hasChanged) {
        const backModal = document.getElementById('back-modal');
        if(backModal) backModal.classList.remove('hidden');
    } else {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        if (isNew) {
            await MaterialBuilder.silentlyDeleteAndExit();
            MaterialBuilder.goToUrl(wrapper.dataset.redirectUrl);
        } else {
            MaterialBuilder.goToUrl(wrapper.dataset.manageUrl); 
        }
    }
};

MaterialBuilder.showModal = function (type, title, message, callback = null) {
    const modal = document.getElementById("status-modal");
    if (!modal) {
        alert(`${title}\n${message}`);
        if (callback && type !== "confirm") callback();
        return;
    }

    const iconContainer = document.getElementById("status-modal-icon");
    const titleEl = document.getElementById("status-modal-title");
    const msgEl = document.getElementById("status-modal-message");
    const btn = document.getElementById("status-modal-btn");
    const cancelBtn = document.getElementById("status-modal-cancel-btn");

    titleEl.innerText = title;
    msgEl.innerText = message;

    iconContainer.className = "h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl";
    btn.className = "w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md";
    cancelBtn.classList.add("hidden");
    btn.innerText = "OK";
    cancelBtn.onclick = null;
    btn.onclick = null;

    if (type === "success") {
        iconContainer.classList.add("bg-green-50", "text-green-500");
        iconContainer.innerHTML = '<i class="fas fa-check-circle"></i>';
        btn.classList.add("bg-green-600", "hover:bg-green-700", "shadow-green-600/20");
    } else if (type === "error") {
        iconContainer.classList.add("bg-red-50", "text-red-500");
        iconContainer.innerHTML = '<i class="fas fa-times-circle"></i>';
        btn.classList.add("bg-red-600", "hover:bg-red-700", "shadow-red-600/20");
    } else if (type === "warning") {
        iconContainer.classList.add("bg-amber-50", "text-amber-500");
        iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        btn.classList.add("bg-amber-500", "hover:bg-amber-600", "shadow-amber-500/20");
    } else if (type === "confirm") {
        iconContainer.classList.add("bg-red-50", "text-red-500");
        iconContainer.innerHTML = '<i class="fas fa-trash-alt"></i>';
        btn.classList.add("bg-red-600", "hover:bg-red-700", "shadow-red-600/20");
        btn.innerText = "Yes, Discard";
        cancelBtn.classList.remove("hidden");
        cancelBtn.onclick = function () { modal.classList.add("hidden"); };
    }

    modal.classList.remove("hidden");
    btn.onclick = function () {
        modal.classList.add("hidden");
        if (callback && typeof callback === "function") callback();
    };
};

MaterialBuilder.goToUrl = function(url) {
    if (typeof loadPartial === "function") {
        loadPartial(url);
    } else {
        window.location.href = url;
    }
};

MaterialBuilder.silentlyDeleteAndExit = async function() {
    const wrapper = document.getElementById("material-wrapper"); 
    if (!wrapper) return; // Dataset Guard
    try {
        await fetch(wrapper.dataset.deleteUrl, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
        });
    } catch (e) {
        console.warn("Failed to delete empty module");
    }
};