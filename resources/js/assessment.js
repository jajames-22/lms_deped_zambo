window.builderState = {
    title: "",
    year_level: "",
    description: "",
    categories: [],
};

window.hasChanged = false; // TRACKS IF USER TYPED ANYTHING

window.submitAssessmentSetup = async function (btn) {
    const title = document.getElementById("setup-title").value;
    const year = document.getElementById("setup-year").value;
    const desc = document.getElementById("setup-desc").value;

    if (!title || !year) {
        alert("Please fill out the Assessment Title and Year/Grade Level.");
        return;
    }

    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proceeding...';

    try {
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

window.catCount = 0;

let autosaveTimer;
const SYNC_DELAY = 3000;
let lastPayload = "";

/* =========================================
   BUILDER INITIALIZATION
========================================= */

window.initBuilder = function () {
    window.catCount = 0;
    lastPayload = "";
    window.hasChanged = false; // Reset tracker on load
    clearTimeout(autosaveTimer);

    const wrapper = document.getElementById("assessment-wrapper");
    const container = document.getElementById("builder-container");

    if (!wrapper || !container) return;

    container.innerHTML = "";
    const id = wrapper.dataset.assessmentId;

    const existingDataEl = document.getElementById("existing-data");
    let existingData = [];

    if (existingDataEl && existingDataEl.value) {
        existingData = JSON.parse(existingDataEl.value);
    }

    const serverDraftEl = document.getElementById("server-draft-data");
    if (serverDraftEl && serverDraftEl.value) {
        try {
            const serverDraft = JSON.parse(serverDraftEl.value);
            if (serverDraft) {
                if (serverDraft.categories) {
                    existingData = serverDraft.categories;
                    if (serverDraft.title)
                        document.getElementById("setup-title").value =
                            serverDraft.title;
                    if (serverDraft.year_level)
                        document.getElementById("setup-year").value =
                            serverDraft.year_level;
                    if (serverDraft.description)
                        document.getElementById("setup-desc").value =
                            serverDraft.description;
                } else if (serverDraft.length > 0) {
                    existingData = serverDraft; 
                }
            }
        } catch (e) {
            console.warn("Invalid server draft JSON");
        }
    }

    const localDraft = localStorage.getItem("assessment_draft_" + id);
    if (localDraft) {
        try {
            const parsed = JSON.parse(localDraft);
            if (parsed) {
                if (parsed.categories && parsed.categories.length > 0) {
                    existingData = parsed.categories;
                }
                if (parsed.title)
                    document.getElementById("setup-title").value = parsed.title;
                if (parsed.year_level)
                    document.getElementById("setup-year").value =
                        parsed.year_level;
                if (parsed.description)
                    document.getElementById("setup-desc").value =
                        parsed.description;
            }
        } catch (e) {
            console.warn("Invalid local draft");
        }
    }

    if (existingData.length > 0) {
        container.innerHTML = "";

        existingData.forEach((cat) => {
            window.renderExistingCategory(cat);
        });
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

    window.hasChanged = false;
    clearTimeout(autosaveTimer);
    window.updateAutosaveIndicator('Ready');
};

/* =========================================
   INTELLIGENT BACK BUTTON LOGIC
========================================= */

window.handleBackButton = async function() {
    const title = document.getElementById("setup-title").value;
    const year = document.getElementById("setup-year").value;
    const wrapper = document.getElementById("assessment-wrapper");
    
    // Scenario 1: They made NO changes during this session
    if (!window.hasChanged) {
        
        // If it's also a completely untouched default exam, clean it up from the DB
        if (title === "Untitled Assessment" && year === "") {
            try {
                await fetch(wrapper.dataset.deleteUrl, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': wrapper.dataset.csrf, 'Accept': 'application/json' }
                });
            } catch(e) {}
        }
        
        // Leave the page silently without showing the warning modal
        if (typeof loadPartial === 'function') {
            loadPartial(wrapper.dataset.redirectUrl);
        } else {
            window.location.href = wrapper.dataset.redirectUrl;
        }
        return; // Stop the function here
    }

    // Scenario 2: Changes WERE made this session, so show the warning modal
    document.getElementById('back-modal').classList.remove('hidden');
};
/* =========================================
   AUTOSAVE TRIGGER
========================================= */

window.handleAutosaveTrigger = function () {
    window.hasChanged = true; // Mark that they started typing
    window.updateAutosaveIndicator(
        '<i class="fas fa-pencil-alt fa-spin"></i> Typing...',
    );

    clearTimeout(autosaveTimer);

    autosaveTimer = setTimeout(() => {
        window.autosaveToServer();
    }, SYNC_DELAY);
};

/* =========================================
   SAVE TO LOCAL STORAGE
========================================= */

function saveToLocal(payload) {
    const wrapper = document.getElementById("assessment-wrapper");
    const id = wrapper.dataset.assessmentId;
    localStorage.setItem("assessment_draft_" + id, JSON.stringify(payload));
}

/* =========================================
   AUTOSAVE TO SERVER
========================================= */

window.autosaveToServer = async function () {
    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return;

    const payload = window.getPayload("draft");
    const payloadString = JSON.stringify(payload);

    if (payloadString === lastPayload) return;
    lastPayload = payloadString;

    saveToLocal(payload);

    if (!payload.categories || payload.categories.length === 0) return;

    window.updateAutosaveIndicator(
        '<i class="fas fa-cloud-upload-alt fa-spin"></i> Syncing...',
    );

    try {
        const response = await fetch(wrapper.dataset.autosaveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
            body: payloadString,
        });

        const responseData = await response.json();

        if (response.ok && responseData.success) {
            window.updateAutosaveIndicator(
                '<i class="fas fa-check-circle text-green-500"></i> Synced',
            );
        } else {
            throw new Error(responseData.message || "Server rejected the save");
        }
    } catch (e) {
        window.updateAutosaveIndicator(
            '<i class="fas fa-wifi-slash text-amber-500"></i> Offline',
        );
        console.error("Autosave error details:", e.message);
    }
};

/* =========================================
   AUTOSAVE STATUS INDICATOR
========================================= */

window.updateAutosaveIndicator = function (html) {
    const el = document.getElementById("autosave-indicator");
    if (el) {
        el.innerHTML = html;
    }
};

// --- BUILDER UI LOGIC ---

window.addCategory = function () {
    const container = document.getElementById("builder-container");
    if (!container) return;

    window.catCount++;
    const catId = `cat-${window.catCount}`;

    const html = `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 category-block overflow-hidden transition-all mb-4" id="${catId}">
            <div class="p-4 bg-gray-50/50 flex items-center justify-between cursor-pointer group" onclick="window.toggleCategory('${catId}', event)">
                <div class="flex items-center gap-4 flex-1">
                    <div class="h-8 w-8 rounded-lg bg-[#a52a2a]/10 text-[#a52a2a] flex items-center justify-center font-bold text-sm">
                        ${window.catCount}
                    </div>
                    <span class="font-bold text-gray-700 category-display-title">New Section</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.removeElement('${catId}')" class="h-8 w-8 text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <div class="h-8 w-8 flex items-center justify-center text-gray-400 group-hover:text-gray-600 transition chevron-icon">
                        <i class="fas fa-chevron-up"></i>
                    </div>
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
                            <div class="border-t border-gray-100 my-1"></div>
                            <button type="button" onclick="window.addQuestion(${window.catCount}, 'instruction'); window.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-[#a52a2a] bg-[#a52a2a]/5 hover:bg-[#a52a2a]/10 font-bold"><i class="fas fa-info-circle w-5 text-center mr-1"></i> Add Instruction</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    window.addQuestion(window.catCount, "mcq");
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
            
            <div class="flex justify-between items-start mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1">
                    <i class="fas ${icon}"></i> 
                    ${type === "mcq" ? "Multiple Choice" : type === "checkbox" ? "Checkboxes" : type === "text" ? "Short Text" : "Instruction Block"}
                </span>
                
                <button type="button" onclick="window.removeElement('${qId}')" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition rounded-md hover:bg-red-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="relative mb-3">
                <textarea class="q-text w-full pl-3 pr-10 py-2 bg-white border border-gray-200 rounded-lg outline-none font-medium text-sm focus:border-[#a52a2a] resize-y min-h-[44px]" placeholder="${placeholder}" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                
                <button type="button" onclick="document.getElementById('file-${qId}').click()" title="Upload Image" class="absolute right-2 top-2 h-7 w-7 flex items-center justify-center text-gray-400 hover:text-[#a52a2a] hover:bg-gray-100 rounded transition">
                    <i class="fas fa-image"></i>
                </button>
                <input type="file" id="file-${qId}" class="hidden" accept="image/*" onchange="window.handleImageUpload(this, '${qId}')">
                <input type="hidden" class="q-image-url" value="">
            </div>

            <div id="preview-${qId}" class="hidden relative mb-4 rounded-lg overflow-hidden border border-gray-200 inline-block">
                <img src="" class="max-h-48 w-auto object-contain bg-white">
                <button type="button" onclick="window.removeQuestionImage('${qId}')" class="absolute top-1 right-1 h-6 w-6 bg-red-500/80 hover:bg-red-600 text-white rounded flex items-center justify-center backdrop-blur-sm transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            
            <div class="options-list space-y-2 mb-3"></div>
            
            ${
                type === "mcq" || type === "checkbox"
                    ? `
                <button type="button" onclick="window.addOptionToQuestion('${qId}', '${type}')" class="text-[10px] font-bold text-[#a52a2a] hover:underline uppercase flex items-center">
                    <i class="fas fa-plus mr-1"></i> Add Choice
                </button>
            `
                    : ""
            }
        </div>`;

    container.insertAdjacentHTML("beforeend", html);

    if (type === "mcq" || type === "checkbox") {
        window.addOptionToQuestion(qId, type, true, "");
        window.addOptionToQuestion(qId, type, false, "");
    } else if (type === "text") {
        window.addOptionToQuestion(qId, "text", true, "");
    }
};

window.addOptionToQuestion = function (
    qId,
    type,
    isCorrect = false,
    text = "",
    isCaseSensitive = false
) {
    const list = document.querySelector(`#${qId} .options-list`);
    if (!list) return;

    const optCount = list.querySelectorAll(".option-row").length + 1;
    let optHtml = "";

    if (type === "mcq") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 focus-within:border-[#a52a2a] option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm" placeholder="Choice ${optCount}..." value="${text}">
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition">
                        <input type="radio" name="correct-${qId}" class="is-correct-input cursor-pointer text-green-600 focus:ring-green-600 h-4 w-4" ${isCorrect ? "checked" : ""}>
                        <span class="text-[10px] font-bold uppercase">Correct</span>
                    </label>
                    <button type="button" onclick="window.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle text-base"></i></button>
                </div>
            </div>`;
    } else if (type === "checkbox") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 focus-within:border-[#a52a2a] option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm" placeholder="Choice ${optCount}..." value="${text}">
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition">
                        <input type="checkbox" class="is-correct-input cursor-pointer text-green-600 rounded focus:ring-green-600 h-4 w-4" ${isCorrect ? "checked" : ""}>
                        <span class="text-[10px] font-bold uppercase">Correct</span>
                    </label>
                    <button type="button" onclick="window.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle text-base"></i></button>
                </div>
            </div>`;
    } else if (type === "text") {
        optHtml = `
            <div class="flex items-center gap-3 bg-green-50/50 px-3 py-2 rounded-lg border border-green-200 focus-within:border-green-400 option-row transition">
                <span class="text-[10px] font-bold text-green-600 uppercase shrink-0"><i class="fas fa-check mr-1"></i> Exact Match:</span>
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm font-medium" placeholder="Type the exact correct answer..." value="${text}">
                <input type="hidden" class="is-correct-input" value="true" checked>
                
                <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition border-l border-green-200 pl-3 shrink-0" title="Check this if uppercase/lowercase letters must match exactly.">
                    <input type="checkbox" class="case-sensitive-input cursor-pointer text-green-600 rounded focus:ring-green-600 h-4 w-4" ${isCaseSensitive ? "checked" : ""}>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Case Sensitive</span>
                </label>
            </div>`;
    }

    list.insertAdjacentHTML("beforeend", optHtml);
    window.handleAutosaveTrigger();
};

window.removeOption = function (btnElement, qId) {
    const block = document.getElementById(qId);
    const type = block.dataset.type;
    const list = block.querySelector(".options-list");

    if (
        (type === "mcq" || type === "checkbox") &&
        list.querySelectorAll(".option-row").length <= 2
    ) {
        window.showModal(
            "warning",
            "Action Prevented",
            "This question type must have at least two choices.",
        );
        return;
    }

    btnElement.closest(".option-row").remove();
    window.handleAutosaveTrigger();
};

// --- IMAGE UPLOAD LOGIC ---
window.handleImageUpload = async function (input, qId) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const wrapper = document.getElementById("assessment-wrapper");
    const csrf = wrapper.dataset.csrf;
    const uploadUrl = wrapper.dataset.uploadUrl;

    const formData = new FormData();
    formData.append("image", file);

    const btnIcon = input.previousElementSibling;
    const originalHtml = btnIcon.innerHTML;
    btnIcon.innerHTML = '<i class="fas fa-spinner fa-spin text-[#a52a2a]"></i>';

    try {
        const response = await fetch(uploadUrl, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf, Accept: "application/json" },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            window.setImagePreview(qId, data.image_url);
        } else {
            alert(data.message || "Failed to upload image.");
        }
    } catch (e) {
        console.error(e);
        alert("Upload failed. Check console.");
    } finally {
        btnIcon.innerHTML = originalHtml;
        input.value = ""; 
    }
};

window.setImagePreview = function (qId, url) {
    const block = document.getElementById(qId);
    block.querySelector(".q-image-url").value = url;

    const previewDiv = document.getElementById(`preview-${qId}`);
    previewDiv.querySelector("img").src = url;
    previewDiv.classList.remove("hidden");

    window.handleAutosaveTrigger();
};

window.removeQuestionImage = function (qId) {
    const block = document.getElementById(qId);
    block.querySelector(".q-image-url").value = "";

    const previewDiv = document.getElementById(`preview-${qId}`);
    previewDiv.querySelector("img").src = "";
    previewDiv.classList.add("hidden");

    window.handleAutosaveTrigger();
};

// --- DATA COLLECTION UPDATE ---

window.getPayload = function (status) {
    const categories = [];
    document.querySelectorAll(".category-block").forEach((cat) => {
        const questions = [];
        cat.querySelectorAll(".question-block").forEach((q) => {
            const options = [];

            q.querySelectorAll(".option-row").forEach((opt) => {
                const isCorrectInput = opt.querySelector(".is-correct-input");
                let isCorrect = false;

                if (
                    isCorrectInput.type === "radio" ||
                    isCorrectInput.type === "checkbox"
                ) {
                    isCorrect = isCorrectInput.checked;
                } else if (isCorrectInput.type === "hidden") {
                    isCorrect = true; 
                }

                options.push({
                    text: opt.querySelector(".option-input").value,
                    is_correct: isCorrect ? 1 : 0,
                });
            });

            questions.push({
                type: q.dataset.type,
                text: q.querySelector(".q-text").value,
                image_url: q.querySelector(".q-image-url").value,
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

// --- RENDER EXISTING DATA UPDATE ---

window.renderExistingCategory = function (catData) {
    window.addCategory();
    const latestCat = document.querySelector(".category-block:last-child");
    
    // Set Category/Section Details
    latestCat.querySelector(".c-title").value = catData.title || "";
    latestCat.querySelector(".c-time").value = catData.time_limit || "";
    latestCat.querySelector(".category-display-title").innerText = catData.title || "New Section";

    const qContainer = latestCat.querySelector('[id^="q-container-"]');
    
    // Clear the default question added by window.addCategory() 
    // to prevent having an extra empty MCQ at the top
    qContainer.innerHTML = "";

    if (catData.questions && catData.questions.length > 0) {
        catData.questions.forEach((q) => {
            const type = q.type || "mcq";

            // 1. Add the question block shell with the CORRECT type
            window.addQuestion(qContainer.id.split("-").pop(), type);
            const latestQ = qContainer.querySelector(".question-block:last-child");

            // 2. Set the question text (handle both possible naming conventions)
            latestQ.querySelector(".q-text").value = q.text || q.question_text || "";

            // 3. Handle Image Preview
            const imgUrl = q.image_url;
            if (imgUrl) {
                window.setImagePreview(latestQ.id, imgUrl);
            }

            // 4. Clear the default options created by window.addQuestion
            latestQ.querySelector(".options-list").innerHTML = "";

            // 5. Render Options with specific type logic
            if (q.options && q.options.length > 0) {
                const isCaseSensitive = q.is_case_sensitive == 1 || q.is_case_sensitive === true;

                q.options.forEach((opt) => {
                    // CRITICAL: We pass 'type' here so the UI knows to render 
                    // radio buttons (mcq), checkboxes, or the green text input.
                    window.addOptionToQuestion(
                        latestQ.id,
                        type, 
                        opt.is_correct == 1 || opt.is_correct === true,
                        opt.text || opt.option_text || "",
                        isCaseSensitive
                    );
                });
            }
        });
    }
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
            localStorage.removeItem(
                "assessment_draft_" + wrapper.dataset.assessmentId,
            );

            const title =
                status === "published" ? "Test Published!" : "Draft Saved!";
            const msg =
                status === "published"
                    ? "Your test is live and ready for students."
                    : "Your progress has been safely stored.";

            window.showModal("success", title, msg, () => {
                if (typeof loadPartial === "function") {
                    loadPartial(wrapper.dataset.redirectUrl);
                } else {
                    window.location.href = wrapper.dataset.redirectUrl;
                }
            });
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (e) {
        window.showModal("error", "Save Failed", e.message);
        window.resetBtn(btn, originalText);
    }
};


// --- UTILITIES ---

window.toggleCategory = (id, e) => {
    if (e.target.closest("button") || e.target.closest("input")) return;
    const body = document.querySelector(`#${id} .category-body`);
    const icon = document.querySelector(`#${id} .chevron-icon i`);
    body.classList.toggle("hidden");
    icon.classList.toggle("fa-chevron-down");
    icon.classList.toggle("fa-chevron-up");
};

window.removeElement = (id) => {
    document.getElementById(id).remove();
    window.handleAutosaveTrigger();
};

window.updateCatDisplay = (input) => {
    input
        .closest(".category-block")
        .querySelector(".category-display-title").innerText =
        input.value || "New Section";
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
                    if (typeof loadPartial === "function") {
                        loadPartial(wrapper.dataset.redirectUrl);
                    } else {
                        window.location.href = wrapper.dataset.redirectUrl;
                    }
                }
            } catch (e) {
                window.showModal("error", "Error", "Failed to discard assessment.");
            }
        }
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

            const originalText = btn.innerHTML;
            btn.innerHTML =
                '<i class="fas fa-spinner fa-spin mr-2"></i> Discarding...';
            btn.disabled = true;

            localStorage.removeItem(
                "assessment_draft_" + wrapper.dataset.assessmentId,
            );

            try {
                await fetch(wrapper.dataset.autosaveUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": wrapper.dataset.csrf,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ clear_draft: true }),
                });
            } catch (e) {
                console.warn("Failed to clear drafts:", e);
            }

            if (typeof loadPartial === "function") {
                loadPartial(wrapper.dataset.redirectUrl);
            } else {
                window.location.href = wrapper.dataset.redirectUrl;
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

// UTILITIES

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
        btn.classList.add(
            "bg-green-600",
            "hover:bg-green-700",
            "shadow-green-600/20",
        );
    } else if (type === "error") {
        iconContainer.classList.add("bg-red-50", "text-red-500");
        iconContainer.innerHTML = '<i class="fas fa-times-circle"></i>';
        btn.classList.add(
            "bg-red-600",
            "hover:bg-red-700",
            "shadow-red-600/20",
        );
    } else if (type === "warning") {
        iconContainer.classList.add("bg-amber-50", "text-amber-500");
        iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        btn.classList.add(
            "bg-amber-500",
            "hover:bg-amber-600",
            "shadow-amber-500/20",
        );
    } else if (type === "confirm") {
        iconContainer.classList.add("bg-red-50", "text-red-500");
        iconContainer.innerHTML = '<i class="fas fa-trash-alt"></i>';
        btn.classList.add(
            "bg-red-600",
            "hover:bg-red-700",
            "shadow-red-600/20",
        );
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