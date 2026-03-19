window.builderState = {
    title: "",
    year_level: "",
    description: "",
    categories: [],
};

window.hasChanged = false;
window.catCount = 0;
window.isInitializing = false;

let autosaveTimer;
const SYNC_DELAY = 3000;
let lastPayload = "";

// Initialize the Builder
window.initBuilder = function () {
    window.isInitializing = true;
    window.catCount = 0;
    lastPayload = "";
    window.hasChanged = false;
    clearTimeout(autosaveTimer);

    const wrapper = document.getElementById("assessment-wrapper");
    const container = document.getElementById("builder-container");

    if (!wrapper || !container) return;

    container.innerHTML = "";
    const id = wrapper.dataset.assessmentId;

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
            if (serverDraft && serverDraft.categories) {
                existingData = serverDraft.categories;
                if (serverDraft.title) document.getElementById("setup-title").value = serverDraft.title;
                if (serverDraft.year_level) document.getElementById("setup-year").value = serverDraft.year_level;
                if (serverDraft.description) document.getElementById("setup-desc").value = serverDraft.description;
            }
        } catch (e) {
            console.warn("Invalid server draft JSON");
        }
    }

    if (existingData && existingData.length > 0) {
        existingData.forEach((cat) => {
            window.renderExistingCategory(cat);
        });
        window.updateCategoryNumbers(); 
    } else {
        if (document.querySelectorAll(".category-block").length === 0) {
            window.addCategory();
        }
    }

    wrapper.addEventListener("input", (e) => {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) {
            window.handleAutosaveTrigger();
        }
    });

    window.updateAutosaveIndicator("Ready");
    setTimeout(() => {
        window.isInitializing = false;
        window.hasChanged = false; 
    }, 500);
};

// Render Existing Categories (Like from an Excel Import)
window.renderExistingCategory = function (catData) {
    window.addCategory();
    const latestCat = document.querySelector(".category-block:last-child");

    latestCat.querySelector(".c-title").value = catData.title || "";
    latestCat.querySelector(".c-time").value = catData.time_limit || "";
    latestCat.querySelector(".category-display-title").innerText = catData.title || "New Section";

    const qContainer = latestCat.querySelector('[id^="q-container-"]');
    qContainer.innerHTML = "";

    if (catData.questions && catData.questions.length > 0) {
        catData.questions.forEach((q) => {
            const type = q.type || "mcq";
            window.addQuestion(qContainer.id.split("-").pop(), type);
            
            const latestQ = qContainer.querySelector(".question-block:last-child");
            latestQ.querySelector(".q-text").value = q.text || q.question_text || "";

            const mediaUrl = q.media_url || q.image_url; 
            if (mediaUrl) {
                window.setMediaPreview(latestQ.id, mediaUrl);
            }
            
            latestQ.querySelector(".options-list").innerHTML = "";

            if (q.options && q.options.length > 0) {
                const isCaseSensitive = q.is_case_sensitive == 1 || q.is_case_sensitive === true;
                q.options.forEach((opt) => {
                    window.addOptionToQuestion(
                        latestQ.id,
                        type,
                        opt.is_correct == 1 || opt.is_correct === true,
                        opt.text || opt.option_text || "",
                        isCaseSensitive
                    );
                });
            } else if (type === "true_false") {
                window.addOptionToQuestion(latestQ.id, "true_false", false, "True");
                window.addOptionToQuestion(latestQ.id, "true_false", false, "False");
            }
        });
    }
};

window.addCategory = function () {
    const container = document.getElementById("builder-container");
    if (!container) return;

    window.catCount++;
    const catId = `cat-${window.catCount}`;

    const html = `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 category-block overflow-hidden transition-all mb-4" id="${catId}">
            <div class="p-4 bg-gray-50/50 flex items-center justify-between cursor-pointer group" onclick="window.toggleCategory('${catId}', event)">
                <div class="flex items-center gap-4 flex-1">
                    <div class="h-8 w-8 rounded-lg bg-[#a52a2a]/10 text-[#a52a2a] flex items-center justify-center font-bold text-sm cat-number-badge">
                        ${window.catCount}
                    </div>
                    <span class="font-bold text-gray-700 category-display-title">New Section</span>
                </div>
                
                <div class="flex items-center gap-1">
                    <button type="button" onclick="window.moveCategoryUp(this, event)" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200 rounded transition" title="Move Section Up"><i class="fas fa-arrow-up"></i></button>
                    <button type="button" onclick="window.moveCategoryDown(this, event)" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200 rounded transition" title="Move Section Down"><i class="fas fa-arrow-down"></i></button>
                    <button type="button" onclick="window.removeElement('${catId}')" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition ml-2" title="Delete Section"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 category-body">
                <div class="flex gap-4 mb-6">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Section Title</label>
                        <input type="text" class="c-title w-full px-4 py-2 border border-gray-200 rounded-xl outline-none" placeholder="e.g., Mathematics" onkeyup="window.updateCatDisplay(this)">
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Mins</label>
                        <input type="number" class="c-time w-full px-4 py-2 border border-gray-200 rounded-xl outline-none" placeholder="0">
                    </div>
                </div>
                
                <div id="q-container-${window.catCount}" class="space-y-4 mb-4"></div>
                
                <div class="relative flex items-center w-full rounded-xl border border-dashed border-gray-200 group/dropdown">
                    <button type="button" onclick="window.addQuestion(${window.catCount}, 'mcq')" class="flex-1 py-3 text-gray-500 text-sm font-bold hover:bg-gray-50 hover:text-[#a52a2a] transition flex items-center justify-center rounded-l-xl">
                        <i class="fas fa-plus-circle mr-2"></i> Add Question
                    </button>
                    
                    <div class="relative h-full border-l border-gray-200">
                        <button type="button" onclick="window.toggleDropdown(this)" class="px-4 py-3 text-gray-400 hover:bg-gray-50 hover:text-[#a52a2a] transition rounded-r-xl h-full flex items-center">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg z-20 py-1 overflow-hidden dropdown-menu">
                            <button type="button" onclick="window.addQuestion(${window.catCount}, 'mcq'); window.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-dot-circle w-5 text-center text-gray-400 mr-1"></i> Multiple Choice</button>
                            <button type="button" onclick="window.addQuestion(${window.catCount}, 'checkbox'); window.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-check-square w-5 text-center text-gray-400 mr-1"></i> Checkboxes</button>
                            <button type="button" onclick="window.addQuestion(${window.catCount}, 'text'); window.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-align-left w-5 text-center text-gray-400 mr-1"></i> Short Text</button>
                            <button type="button" onclick="window.addQuestion(${window.catCount}, 'true_false'); window.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-adjust w-5 text-center text-gray-400 mr-1"></i> True or False</button>
                            <div class="border-t border-gray-100 my-1"></div>
                            <button type="button" onclick="window.addQuestion(${window.catCount}, 'instruction'); window.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-[#a52a2a] bg-[#a52a2a]/5 hover:bg-[#a52a2a]/10 font-bold"><i class="fas fa-info-circle w-5 text-center mr-1"></i> Add Instruction</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    window.updateCategoryNumbers();
};

window.toggleDropdown = function (btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll(".dropdown-menu").forEach((el) => {
        if (el !== menu) el.classList.add("hidden");
    });
    menu.classList.toggle("hidden");
};

window.addQuestion = function (cId, type = "mcq") {
    const container = document.getElementById(`q-container-${cId}`);
    if (!container) return;

    const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

    let placeholder = "Enter Question...";
    let icon = "fa-question-circle";
    let bgClass = "bg-gray-50";
    if (type === "instruction") {
        placeholder = "Enter Instructions / Context block...";
        icon = "fa-info-circle";
        bgClass = "bg-amber-50/30 border-amber-100";
    }

    const html = `
        <div class="p-4 rounded-xl border border-gray-100 question-block relative group ${bgClass}" id="${qId}" data-type="${type}">
            
            <div class="flex justify-between items-start mb-2 cursor-pointer" onclick="window.toggleQuestion('${qId}', event)">
                <div class="flex items-center gap-2 overflow-hidden pr-2">
                    <div class="h-6 w-6 flex items-center justify-center text-gray-400 group-hover:text-gray-600 transition q-chevron-icon shrink-0">
                        <i class="fas fa-chevron-up text-xs"></i>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1 shrink-0">
                        <i class="fas ${icon}"></i> 
                        ${type === "mcq" ? "Multiple Choice" : type === "checkbox" ? "Checkboxes" : type === "text" ? "Short Text" : type === "true_false" ? "True or False" : "Instruction Block"}
                    </span>
                    <span class="text-xs text-gray-500 font-medium truncate ml-2 q-preview-text hidden"></span>
                </div>
                
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" onclick="window.moveQuestionUp(this)" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-gray-600 transition rounded-md hover:bg-gray-200" title="Move Question Up"><i class="fas fa-arrow-up"></i></button>
                    <button type="button" onclick="window.moveQuestionDown(this)" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-gray-600 transition rounded-md hover:bg-gray-200" title="Move Question Down"><i class="fas fa-arrow-down"></i></button>
                    <button type="button" onclick="window.removeElement('${qId}')" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition rounded-md hover:bg-red-50 ml-1" title="Delete Question"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="question-body">
                <div class="relative mb-3">
                    <textarea class="q-text w-full pl-3 pr-10 py-2 bg-white border border-gray-200 rounded-lg outline-none font-medium text-sm focus:border-[#a52a2a] resize-y min-h-[44px]" placeholder="${placeholder}"></textarea>
                    <input type="hidden" class="q-media-url" value="">
                    
                    <button type="button" onclick="window.openMediaModal('${qId}')" title="Upload Media" class="absolute right-2 top-2 h-7 w-7 flex items-center justify-center text-gray-400 hover:text-[#a52a2a] hover:bg-gray-100 rounded transition">
                        <i class="fas fa-photo-video"></i>
                    </button>
                </div>
                
                <div id="preview-${qId}" class="hidden relative mb-4 rounded-lg overflow-hidden border border-gray-200 inline-block w-full"></div>
                
                <div class="options-list space-y-2 mb-3"></div>
                
                ${(type === "mcq" || type === "checkbox") ? `
                    <button type="button" onclick="window.addOptionToQuestion('${qId}', '${type}')" class="text-[10px] font-bold text-[#a52a2a] hover:underline uppercase flex items-center">
                        <i class="fas fa-plus mr-1"></i> Add Choice
                    </button>
                ` : ""}
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);

    if (type === "mcq" || type === "checkbox") {
        window.addOptionToQuestion(qId, type, true, "");
        window.addOptionToQuestion(qId, type, false, "");
    } else if (type === "text") {
        window.addOptionToQuestion(qId, "text", true, "");
    } else if (type === "true_false") {
        window.addOptionToQuestion(qId, "true_false", false, "True");
        window.addOptionToQuestion(qId, "true_false", false, "False");
    }
};

window.addOptionToQuestion = function (qId, type, isCorrect = false, text = "", isCaseSensitive = false) {
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
                    <button type="button" onclick="window.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle"></i></button>
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
                    <button type="button" onclick="window.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle"></i></button>
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
    window.handleAutosaveTrigger();
};

window.moveCategoryUp = function(btn, event) {
    event.stopPropagation();
    const catBlock = btn.closest('.category-block');
    const prev = catBlock.previousElementSibling;
    if (prev && prev.classList.contains('category-block')) {
        prev.insertAdjacentElement('beforebegin', catBlock);
        window.updateCategoryNumbers();
        window.handleAutosaveTrigger();
    }
};

window.moveCategoryDown = function(btn, event) {
    event.stopPropagation();
    const catBlock = btn.closest('.category-block');
    const next = catBlock.nextElementSibling;
    if (next && next.classList.contains('category-block')) {
        next.insertAdjacentElement('afterend', catBlock);
        window.updateCategoryNumbers();
        window.handleAutosaveTrigger();
    }
};

window.moveQuestionUp = function(btn) {
    const qBlock = btn.closest('.question-block');
    const prev = qBlock.previousElementSibling;
    if (prev && prev.classList.contains('question-block')) {
        prev.insertAdjacentElement('beforebegin', qBlock);
        window.handleAutosaveTrigger();
    }
};

window.moveQuestionDown = function(btn) {
    const qBlock = btn.closest('.question-block');
    const next = qBlock.nextElementSibling;
    if (next && next.classList.contains('question-block')) {
        next.insertAdjacentElement('afterend', qBlock);
        window.handleAutosaveTrigger();
    }
};

window.updateCategoryNumbers = function() {
    document.querySelectorAll('.category-block').forEach((block, index) => {
        const numberBadge = block.querySelector('.cat-number-badge');
        if (numberBadge) {
            numberBadge.innerText = index + 1;
        }
    });
};

window.removeOption = function (btnElement, qId) {
    btnElement.closest(".option-row").remove();
    window.handleAutosaveTrigger();
};

window.removeElement = function (id) {
    const el = document.getElementById(id);
    if (el) {
        const isCategory = el.classList.contains('category-block');
        el.remove();
        if (isCategory) window.updateCategoryNumbers(); 
    }
    window.handleAutosaveTrigger();
};

window.toggleCategory = function (id, event) {
    if (["INPUT", "BUTTON", "I"].includes(event.target.tagName)) return;
    const body = document.querySelector(`#${id} .category-body`);
    body.classList.toggle("hidden");
};

window.toggleQuestion = function (id, event) {
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
        preview.innerText = text ? "- " + text : "- (Empty Question)";
        preview.classList.remove("hidden");
    } else {
        preview.classList.add("hidden");
    }
};

window.updateCatDisplay = function (input) {
    const title = input.closest(".category-block").querySelector(".category-display-title");
    title.innerText = input.value || "New Section";
};

window.getPayload = function (status) {
    const categories = [];
    document.querySelectorAll(".category-block").forEach((cat) => {
        const questions = [];
        cat.querySelectorAll(".question-block").forEach((q) => {
            const options = [];
            q.querySelectorAll(".option-row").forEach((opt) => {
                const isCorrectInput = opt.querySelector(".is-correct-input");
                let isCorrect = (isCorrectInput.type === "radio" || isCorrectInput.type === "checkbox") ? isCorrectInput.checked : true;
                options.push({ text: opt.querySelector(".option-input").value, is_correct: isCorrect ? 1 : 0 });
            });
            questions.push({
                type: q.dataset.type,
                text: q.querySelector(".q-text").value,
                media_url: q.querySelector(".q-media-url") ? q.querySelector(".q-media-url").value : null,
                is_case_sensitive: q.querySelector(".case-sensitive-input") ? q.querySelector(".case-sensitive-input").checked : false,
                options: options,
            });
        });
        categories.push({
            title: cat.querySelector(".c-title").value,
            time_limit: cat.querySelector(".c-time").value,
            questions: questions,
        });
    });

    return {
        status,
        title: document.getElementById("setup-title").value,
        year_level: document.getElementById("setup-year").value,
        description: document.getElementById("setup-desc").value,
        categories,
    };
};

window.handleAutosaveTrigger = function () {
    if (window.isInitializing) return; 
    
    window.hasChanged = true;
    window.updateAutosaveIndicator('<i class="fas fa-pencil-alt fa-spin"></i> Typing...');
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(() => { window.autosaveToServer(); }, SYNC_DELAY);
};

window.autosaveToServer = async function () {
    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return;

    const payload = window.getPayload("draft");
    const payloadString = JSON.stringify(payload);
    if (payloadString === lastPayload) return;
    lastPayload = payloadString;

    try {
        await fetch(wrapper.dataset.autosaveUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": wrapper.dataset.csrf, Accept: "application/json" },
            body: payloadString,
        });
        window.updateAutosaveIndicator('<i class="fas fa-check-circle text-green-500"></i> Synced');
    } catch (e) {
        window.updateAutosaveIndicator('<i class="fas fa-wifi-slash text-amber-500"></i> Offline');
    }
};

window.updateAutosaveIndicator = function (html) {
    const el = document.getElementById("autosave-indicator");
    if (el) el.innerHTML = html;
};

// --- MEDIA UPLOAD LOGIC ---
window.currentMediaUploadQId = null;
window.selectedMediaFile = null;

window.openMediaModal = function (qId) {
    window.currentMediaUploadQId = qId;
    window.clearSelectedMedia();
    document.getElementById("media-upload-modal").classList.remove("hidden");
};

window.closeMediaModal = function () {
    document.getElementById("media-upload-modal").classList.add("hidden");
    window.currentMediaUploadQId = null;
};

window.handleMediaFileSelect = function (input) {
    if (!input.files || input.files.length === 0) return;

    window.selectedMediaFile = input.files[0];
    document.getElementById("selected-media-name").innerText = window.selectedMediaFile.name;

    document.getElementById("media-dropzone").classList.add("hidden");
    document.getElementById("selected-media-display").classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("flex");

    document.getElementById("start-media-upload-btn").disabled = false;
};

window.clearSelectedMedia = function () {
    window.selectedMediaFile = null;
    document.getElementById("media-file-input").value = "";

    document.getElementById("media-dropzone").classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("hidden");
    document.getElementById("selected-media-display").classList.remove("flex");

    document.getElementById("start-media-upload-btn").disabled = true;
};

window.executeMediaUpload = async function () {
    if (!window.selectedMediaFile || !window.currentMediaUploadQId) return;

    const btn = document.getElementById("start-media-upload-btn");
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Uploading...</span>';
    btn.disabled = true;

    const wrapper = document.getElementById("assessment-wrapper");
    const formData = new FormData();
    formData.append("media_file", window.selectedMediaFile);

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
            window.setMediaPreview(
                window.currentMediaUploadQId,
                data.media_url,
                data.media_type,
            );
            window.closeMediaModal();
            window.handleAutosaveTrigger();
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

window.setMediaPreview = function (qId, url, explicitType = null) {
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
        mediaHtml = `<img src="${url}" class="max-h-48 w-auto object-contain bg-white rounded-lg">`;
    }

    previewDiv.className = "relative mb-4 rounded-lg overflow-hidden border border-gray-200 block w-full";

    previewDiv.innerHTML = `
        ${mediaHtml}
        <button type="button" onclick="window.removeQuestionMedia('${qId}')" class="absolute top-1 right-1 h-6 w-6 bg-red-500/80 hover:bg-red-600 text-white rounded flex items-center justify-center backdrop-blur-sm transition z-10 shadow-sm">
            <i class="fas fa-times text-xs"></i>
        </button>
    `;
};

window.removeQuestionMedia = function (qId) {
    const block = document.getElementById(qId);
    block.querySelector(".q-media-url").value = "";

    const previewDiv = document.getElementById(`preview-${qId}`);
    if (previewDiv) {
        previewDiv.innerHTML = "";
        previewDiv.classList.add("hidden");
    }

    window.handleAutosaveTrigger();
};

window.collectCategoriesData = function () {
    return window.getPayload("draft").categories;
};

window.saveCompleteExam = async function (btn, status) {
    clearTimeout(autosaveTimer);
    lastPayload = "";

    const wrapper = document.getElementById("assessment-wrapper");
    const payload = window.getPayload(status);

    if (!payload.title || !payload.year_level) {
        return window.showModal(
            "warning",
            "Missing Information",
            "Please fill out the Assessment Title and Year / Grade Level before saving.",
        );
    }

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

    try {
        const response = await fetch(wrapper.dataset.saveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        });
        const result = await response.json();

        if (response.ok && result.success) {
            localStorage.removeItem("assessment_draft_" + wrapper.dataset.assessmentId);

            const title = status === "published" ? "Test Published!" : "Draft Saved!";
            const msg = status === "published"
                ? "Your test is live and ready for students."
                : "Your progress has been safely stored.";

            window.showModal("success", title, msg, () => {
                window.goToUrl(wrapper.dataset.manageUrl, window.currentNavBtn);
            });
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (e) {
        window.showModal("error", "Save Failed", e.message);
        window.resetBtn(btn, originalText);
    }
};

window.resetBtn = (btn, txt) => {
    btn.disabled = false;
    btn.innerHTML = txt;
};

window.deleteAssessmentFromBuilder = async function () {
    window.showModal(
        "confirm",
        "Discard Assessment?",
        "Are you sure you want to discard this entire assessment? This cannot be undone.",
        async () => {
            const wrapper = document.getElementById("assessment-wrapper");
            try {
                const response = await fetch(wrapper.dataset.deleteUrl, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": wrapper.dataset.csrf,
                        Accept: "application/json",
                    },
                });
                if (response.ok) {
                    window.goToUrl(wrapper.dataset.redirectUrl, window.currentNavBtn);
                }
            } catch (e) {
                window.showModal(
                    "error",
                    "Error",
                    "Failed to discard assessment.",
                );
            }
        },
    );
};

window.addEventListener("beforeunload", (event) => {
    if (lastPayload !== "") {
        event.preventDefault();
        event.returnValue = "";
    }
});

window.discardChangesAndExit = function (btn) {
    window.showModal(
        "confirm",
        "Discard Unsaved Changes?",
        "Are you sure you want to discard your unsaved work and exit? This cannot be undone.",
        async () => {
            const wrapper = document.getElementById("assessment-wrapper");
            if (!wrapper) return;

            document.getElementById("back-modal").classList.add("hidden");

            clearTimeout(autosaveTimer);
            lastPayload = "";

            const isNew = wrapper.dataset.isNew === 'true';

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Discarding...';
            btn.disabled = true;

            localStorage.removeItem("assessment_draft_" + wrapper.dataset.assessmentId);

            try {
                if (isNew) {
                    await window.silentlyDeleteAndExit();
                    window.goToUrl(wrapper.dataset.redirectUrl);
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
                    window.goToUrl(wrapper.dataset.manageUrl);
                }
            } catch (e) {
                console.warn("Failed to clear drafts or delete:", e);
                window.goToUrl(wrapper.dataset.redirectUrl); 
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

window.showModal = function (type, title, message, callback = null) {
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

    iconContainer.className =
        "h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl";
    btn.className =
        "w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md";
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
        cancelBtn.onclick = function () {
            modal.classList.add("hidden");
        };
    }

    modal.classList.remove("hidden");

    btn.onclick = function () {
        modal.classList.add("hidden");
        if (callback && typeof callback === "function") {
            callback();
        }
    };
};
window.currentNavBtn = null; // Store it globally for the modals to use

window.goToUrl = function(url) {
    if (typeof loadPartial === "function") {
        try {
            loadPartial(url); // Just load the page! Let the page handle its own sidebar.
        } catch (e) {
            window.location.href = url;
        }
    } else {
        window.location.href = url;
    }
};

window.silentlyDeleteAndExit = async function() {
    const wrapper = document.getElementById("assessment-wrapper");
    try {
        await fetch(wrapper.dataset.deleteUrl, {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": wrapper.dataset.csrf, Accept: "application/json" },
        });
    } catch (e) {
        console.warn("Failed to delete empty assessment");
    }
};

// Accept the nav element directly from the HTML click
window.handleAssessmentBackButton = async function(btn) {
    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return;

    const isNew = wrapper.dataset.isNew === 'true';
    const manageUrl = wrapper.dataset.manageUrl; 
    const redirectUrl = wrapper.dataset.redirectUrl; 

    if (window.hasChanged) {
        document.getElementById('back-modal').classList.remove('hidden');
    } else {
        btn.style.width = btn.offsetWidth + 'px';
        btn.style.height = btn.offsetHeight + 'px';
        btn.innerHTML = '<i class="fas fa-spinner fa-spin text-[#a52a2a]"></i>';
        btn.disabled = true;

        if (isNew) {
            await window.silentlyDeleteAndExit();
            window.goToUrl(redirectUrl); 
        } else {
            window.goToUrl(manageUrl);
        }
    }
};
// --- IMPORT EXCEL LOGIC ADDED HERE ---

let selectedFile = null;

window.openImportModal = function() {
    window.clearSelectedFile(); 
    document.getElementById('excel-import-modal').classList.remove('hidden');
};

window.closeImportModal = function() {
    document.getElementById('excel-import-modal').classList.add('hidden');
};

window.handleFileSelect = function(input) {
    if (!input.files || input.files.length === 0) return;

    selectedFile = input.files[0];
    document.getElementById('selected-file-name').innerText = selectedFile.name;
    document.getElementById('file-dropzone').classList.add('hidden');
    document.getElementById('selected-file-display').classList.remove('hidden');
    document.getElementById('selected-file-display').classList.add('flex'); 
    document.getElementById('start-upload-btn').disabled = false;
};

window.clearSelectedFile = function() {
    selectedFile = null;
    document.getElementById('excel-file-input').value = '';
    document.getElementById('file-dropzone').classList.remove('hidden');
    document.getElementById('selected-file-display').classList.add('hidden');
    document.getElementById('selected-file-display').classList.remove('flex');
    document.getElementById('start-upload-btn').disabled = true;
};

window.executeExcelUpload = function() {
    if (!selectedFile) return;

    let btn = document.getElementById('start-upload-btn');
    let originalHtml = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
    btn.disabled = true;

    let payload = window.getPayload("draft");
    let formData = new FormData();
    formData.append('exam_file', selectedFile);
    formData.append('_token', document.querySelector('[data-csrf]').dataset.csrf);
    formData.append('title', payload.title);
    formData.append('year_level', payload.year_level);
    formData.append('description', payload.description);
    formData.append('categories', JSON.stringify(payload.categories));

    let wrapper = document.getElementById('assessment-wrapper');
    let assessmentId = wrapper.dataset.assessmentId;

    fetch(`/dashboard/assessments/${assessmentId}/import`, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
        .then(async response => {
            if (!response.ok) {
                let errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Server error (${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.closeImportModal();
                window.showStatusModal('Success!', 'Your exam has been updated with the imported questions.', 'success');

                window.hasChanged = false;
                lastPayload = "";
                localStorage.removeItem("assessment_draft_" + assessmentId);

                setTimeout(() => {
                    let buildUrl = `/dashboard/assessments/${assessmentId}/build`;
                    if (typeof loadPartial === 'function') {
                        loadPartial(buildUrl, document.getElementById('nav-assessment-btn'));
                    } else {
                        window.location.href = buildUrl;
                    }
                }, 2000);
            } else {
                throw new Error(data.message || 'Import failed.');
            }
        })
        .catch(error => {
            console.error('Upload Error:', error);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            window.showStatusModal('Import Failed', error.message, 'error');
        });
};