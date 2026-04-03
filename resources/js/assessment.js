window.AssessmentBuilder = window.AssessmentBuilder || {};

AssessmentBuilder.state = {
    title: "",
    year_level: "",
    description: "",
    categories: [],
};

AssessmentBuilder.hasChanged = false;
AssessmentBuilder.catCount = 0;
AssessmentBuilder.isInitializing = false;
AssessmentBuilder.autosaveTimer = null;
AssessmentBuilder.SYNC_DELAY = 3000;
AssessmentBuilder.lastPayload = "";
AssessmentBuilder.activityListenersAdded = false;

// Initialize SortableJS
AssessmentBuilder.initSortable = function () {
    if (typeof Sortable === "undefined") return;

    const container = document.getElementById("builder-container");
    if (container && !container.sortableInstance) {
        container.sortableInstance = new Sortable(container, {
            animation: 150,
            handle: ".drag-handle-cat",
            ghostClass: "opacity-50",
            onEnd: function () {
                AssessmentBuilder.updateCategoryNumbers();
                AssessmentBuilder.handleAutosaveTrigger();
            },
        });
    }

    document.querySelectorAll('[id^="q-container-"]').forEach((qContainer) => {
        if (!qContainer.sortableInstance) {
            qContainer.sortableInstance = new Sortable(qContainer, {
                animation: 150,
                handle: ".drag-handle-q",
                ghostClass: "opacity-50",
                group: "shared-questions",
                onEnd: function () {
                    AssessmentBuilder.handleAutosaveTrigger();
                },
            });
        }
    });
};

// Calculate Total Estimated Time
AssessmentBuilder.calculateTotalTime = function () {
    let total = 0;
    document.querySelectorAll(".c-time").forEach((input) => {
        total += parseInt(input.value) || 0;
    });
    const display = document.getElementById("total-time-display");
    if (display) {
        display.innerHTML = `<i class="far fa-clock mr-1"></i> ${total} mins`;
    }
};

// Initialize the Builder
AssessmentBuilder.initBuilder = function () {
    AssessmentBuilder.isInitializing = true;
    AssessmentBuilder.catCount = 0;
    AssessmentBuilder.lastPayload = "";
    AssessmentBuilder.hasChanged = false;
    clearTimeout(AssessmentBuilder.autosaveTimer);

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
                if (serverDraft.title)
                    document.getElementById("setup-title").value =
                        serverDraft.title;
                if (serverDraft.year_level)
                    document.getElementById("setup-year").value =
                        serverDraft.year_level;
                if (serverDraft.description)
                    document.getElementById("setup-desc").value =
                        serverDraft.description;
            }
        } catch (e) {
            console.warn("Invalid server draft JSON");
        }
    }

    if (existingData && existingData.length > 0) {
        existingData.forEach((cat) => {
            AssessmentBuilder.renderExistingCategory(cat);
        });
        AssessmentBuilder.updateCategoryNumbers();
    } else {
        if (document.querySelectorAll(".category-block").length === 0) {
            AssessmentBuilder.addCategory();
        }
    }

    wrapper.addEventListener("input", (e) => {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) {
            AssessmentBuilder.handleAutosaveTrigger();
        }
    });

    if (!AssessmentBuilder.activityListenersAdded) {
        const resetTimer = () => AssessmentBuilder.resetIdleTimer();
        window.addEventListener("mousemove", resetTimer);
        window.addEventListener("keydown", resetTimer);
        window.addEventListener("mousedown", resetTimer);
        window.addEventListener("touchstart", resetTimer);
        window.addEventListener("scroll", resetTimer, true);
        AssessmentBuilder.activityListenersAdded = true;
    }

    AssessmentBuilder.updateAutosaveIndicator("Ready");
    AssessmentBuilder.initSortable();
    AssessmentBuilder.calculateTotalTime();

    setTimeout(() => {
        AssessmentBuilder.isInitializing = false;
        AssessmentBuilder.hasChanged = false;
    }, 500);
};

// Render Existing Categories
AssessmentBuilder.renderExistingCategory = function (catData) {
    AssessmentBuilder.addCategory();
    const latestCat = document.querySelector(".category-block:last-child");

    latestCat.querySelector(".c-title").value = catData.title || "";
    latestCat.querySelector(".c-time").value = catData.time_limit || "";
    latestCat.querySelector(".category-display-title").innerText =
        catData.title || "New Section";

    const qContainer = latestCat.querySelector('[id^="q-container-"]');
    qContainer.innerHTML = "";

    if (catData.questions && catData.questions.length > 0) {
        catData.questions.forEach((q) => {
            const type = q.type || "mcq";
            AssessmentBuilder.addQuestion(qContainer.id.split("-").pop(), type);

            const latestQ = qContainer.querySelector(
                ".question-block:last-child",
            );
            latestQ.querySelector(".q-text").value =
                q.text || q.question_text || "";

            const mediaUrl = q.media_url || q.image_url;
            if (mediaUrl) {
                AssessmentBuilder.setMediaPreview(latestQ.id, mediaUrl);
            }

            latestQ.querySelector(".options-list").innerHTML = "";

            if (q.options && q.options.length > 0) {
                const isCaseSensitive =
                    q.is_case_sensitive == 1 || q.is_case_sensitive === true;
                q.options.forEach((opt) => {
                    AssessmentBuilder.addOptionToQuestion(
                        latestQ.id,
                        type,
                        opt.is_correct == 1 || opt.is_correct === true,
                        opt.text || opt.option_text || "",
                        isCaseSensitive,
                    );
                });
            } else if (type === "true_false") {
                AssessmentBuilder.addOptionToQuestion(
                    latestQ.id,
                    "true_false",
                    false,
                    "True",
                );
                AssessmentBuilder.addOptionToQuestion(
                    latestQ.id,
                    "true_false",
                    false,
                    "False",
                );
            }
        });
    }
};

AssessmentBuilder.addCategory = function () {
    const container = document.getElementById("builder-container");
    if (!container) return;

    AssessmentBuilder.catCount++;
    const catId = `cat-${AssessmentBuilder.catCount}`;

    const html = `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 category-block overflow-hidden transition-all mb-4" id="${catId}">
            <div class="p-4 bg-gray-50/50 flex items-center justify-between cursor-pointer group" onclick="AssessmentBuilder.toggleCategory('${catId}', event)">
                <div class="flex items-center gap-4 flex-1">
                    <div class="h-8 w-8 rounded-lg bg-[#a52a2a]/10 text-[#a52a2a] flex items-center justify-center font-bold text-sm cat-number-badge">
                        ${AssessmentBuilder.catCount}
                    </div>
                    <span class="font-bold text-gray-700 category-display-title">New Section</span>
                </div>
                
                <div class="flex items-center gap-1">
                    <div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-700 p-2 drag-handle-cat rounded hover:bg-gray-200 transition" title="Drag to reorder Section"><i class="fas fa-grip-vertical"></i></div>
                    <button type="button" onclick="AssessmentBuilder.removeElement('${catId}')" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition ml-2" title="Delete Section"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 category-body">
                <div class="flex gap-4 mb-6">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Section Title</label>
                        <input type="text" class="c-title w-full px-4 py-2 border border-gray-200 rounded-xl outline-none" placeholder="e.g., Mathematics" onkeyup="AssessmentBuilder.updateCatDisplay(this)">
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Mins</label>
                        <input type="number" class="c-time w-full px-4 py-2 border border-gray-200 rounded-xl outline-none text-center" placeholder="0" onchange="AssessmentBuilder.calculateTotalTime(); AssessmentBuilder.handleAutosaveTrigger()">
                    </div>
                </div>
                
                <div id="q-container-${AssessmentBuilder.catCount}" class="space-y-4 mb-4"></div>
                
                <div class="relative flex items-center w-full rounded-xl border border-dashed border-gray-200 group/dropdown">
                    <button type="button" onclick="AssessmentBuilder.addQuestion(${AssessmentBuilder.catCount}, 'mcq')" class="flex-1 py-3 text-gray-500 text-sm font-bold hover:bg-gray-50 hover:text-[#a52a2a] transition flex items-center justify-center rounded-l-xl">
                        <i class="fas fa-plus-circle mr-2"></i> Add Question
                    </button>
                    
                    <div class="relative h-full border-l border-gray-200">
                        <button type="button" onclick="AssessmentBuilder.toggleDropdown(this)" class="px-4 py-3 text-gray-400 hover:bg-gray-50 hover:text-[#a52a2a] transition rounded-r-xl h-full flex items-center">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg z-20 py-1 overflow-hidden dropdown-menu">
                            <button type="button" onclick="AssessmentBuilder.addQuestion(${AssessmentBuilder.catCount}, 'mcq'); AssessmentBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-dot-circle w-5 text-center text-gray-400 mr-1"></i> Multiple Choice</button>
                            <button type="button" onclick="AssessmentBuilder.addQuestion(${AssessmentBuilder.catCount}, 'checkbox'); AssessmentBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-check-square w-5 text-center text-gray-400 mr-1"></i> Checkboxes</button>
                            <button type="button" onclick="AssessmentBuilder.addQuestion(${AssessmentBuilder.catCount}, 'text'); AssessmentBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-align-left w-5 text-center text-gray-400 mr-1"></i> Short Text</button>
                            <button type="button" onclick="AssessmentBuilder.addQuestion(${AssessmentBuilder.catCount}, 'true_false'); AssessmentBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#a52a2a]"><i class="fas fa-adjust w-5 text-center text-gray-400 mr-1"></i> True or False</button>
                            <div class="border-t border-gray-100 my-1"></div>
                            <button type="button" onclick="AssessmentBuilder.addQuestion(${AssessmentBuilder.catCount}, 'instruction'); AssessmentBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-[#a52a2a] bg-[#a52a2a]/5 hover:bg-[#a52a2a]/10 font-bold"><i class="fas fa-info-circle w-5 text-center mr-1"></i> Add Instruction</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    AssessmentBuilder.updateCategoryNumbers();
    AssessmentBuilder.initSortable();
    AssessmentBuilder.calculateTotalTime();
};

AssessmentBuilder.toggleDropdown = function (btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll(".dropdown-menu").forEach((el) => {
        if (el !== menu) el.classList.add("hidden");
    });
    menu.classList.toggle("hidden");
};

AssessmentBuilder.addQuestion = function (cId, type = "mcq") {
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
            
            <div class="flex justify-between items-start mb-2 cursor-pointer" onclick="AssessmentBuilder.toggleQuestion('${qId}', event)">
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
                    <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-600 px-2 drag-handle-q rounded hover:bg-gray-200 transition"><i class="fas fa-grip-vertical"></i></div>
                    <button type="button" onclick="AssessmentBuilder.removeElement('${qId}')" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition rounded-md hover:bg-red-50 ml-1" title="Delete Question"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="question-body">
                <div class="relative mb-3">
                    <textarea class="q-text w-full pl-3 pr-10 py-2 bg-white border border-gray-200 rounded-lg outline-none font-medium text-sm focus:border-[#a52a2a] resize-y min-h-[44px]" placeholder="${placeholder}"></textarea>
                    <input type="hidden" class="q-media-url" value="">
                    
                    <button type="button" onclick="AssessmentBuilder.openMediaModal('${qId}')" title="Upload Media" class="absolute right-2 top-2 h-7 w-7 flex items-center justify-center text-gray-400 hover:text-[#a52a2a] hover:bg-gray-100 rounded transition">
                        <i class="fas fa-photo-video"></i>
                    </button>
                </div>
                
                <div id="preview-${qId}" class="hidden relative mb-4 rounded-lg overflow-hidden border border-gray-200 inline-block w-full"></div>
                
                <div class="options-list space-y-2 mb-3"></div>
                
                ${
                    type === "mcq" || type === "checkbox"
                        ? `
                    <button type="button" onclick="AssessmentBuilder.addOptionToQuestion('${qId}', '${type}')" class="text-[10px] font-bold text-[#a52a2a] hover:underline uppercase flex items-center">
                        <i class="fas fa-plus mr-1"></i> Add Choice
                    </button>
                `
                        : ""
                }
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    AssessmentBuilder.initSortable();

    if (type === "mcq" || type === "checkbox") {
        AssessmentBuilder.addOptionToQuestion(qId, type, true, "");
        AssessmentBuilder.addOptionToQuestion(qId, type, false, "");
    } else if (type === "text") {
        AssessmentBuilder.addOptionToQuestion(qId, "text", true, "");
    } else if (type === "true_false") {
        AssessmentBuilder.addOptionToQuestion(qId, "true_false", false, "True");
        AssessmentBuilder.addOptionToQuestion(
            qId,
            "true_false",
            false,
            "False",
        );
    }
};

AssessmentBuilder.addOptionToQuestion = function (
    qId,
    type,
    isCorrect = false,
    text = "",
    isCaseSensitive = false,
) {
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
                    <button type="button" onclick="AssessmentBuilder.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle"></i></button>
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
                    <button type="button" onclick="AssessmentBuilder.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center"><i class="fas fa-times-circle"></i></button>
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
    AssessmentBuilder.handleAutosaveTrigger();
};

AssessmentBuilder.updateCategoryNumbers = function () {
    document.querySelectorAll(".category-block").forEach((block, index) => {
        const numberBadge = block.querySelector(".cat-number-badge");
        if (numberBadge) {
            numberBadge.innerText = index + 1;
        }
    });
};

AssessmentBuilder.removeOption = function (btnElement, qId) {
    btnElement.closest(".option-row").remove();
    AssessmentBuilder.handleAutosaveTrigger();
};

AssessmentBuilder.removeElement = function (id) {
    const el = document.getElementById(id);
    if (el) {
        const isCategory = el.classList.contains("category-block");
        el.remove();
        if (isCategory) AssessmentBuilder.updateCategoryNumbers();
    }
    AssessmentBuilder.calculateTotalTime();
    AssessmentBuilder.handleAutosaveTrigger();
};

AssessmentBuilder.toggleCategory = function (id, event) {
    if (
        ["INPUT", "BUTTON", "I"].includes(event.target.tagName) ||
        event.target.closest(".drag-handle-cat")
    )
        return;
    const body = document.querySelector(`#${id} .category-body`);
    body.classList.toggle("hidden");
};

AssessmentBuilder.toggleQuestion = function (id, event) {
    if (
        (event && event.target.closest("button")) ||
        event.target.closest(".drag-handle-q")
    )
        return;

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

AssessmentBuilder.updateCatDisplay = function (input) {
    const title = input
        .closest(".category-block")
        .querySelector(".category-display-title");
    title.innerText = input.value || "New Section";
};

AssessmentBuilder.getPayload = function (status) {
    const categories = [];
    document.querySelectorAll(".category-block").forEach((cat) => {
        const questions = [];
        cat.querySelectorAll(".question-block").forEach((q) => {
            const options = [];
            q.querySelectorAll(".option-row").forEach((opt) => {
                const isCorrectInput = opt.querySelector(".is-correct-input");
                let isCorrect =
                    isCorrectInput.type === "radio" ||
                    isCorrectInput.type === "checkbox"
                        ? isCorrectInput.checked
                        : true;
                options.push({
                    text: opt.querySelector(".option-input").value,
                    is_correct: isCorrect ? 1 : 0,
                });
            });
            questions.push({
                type: q.dataset.type,
                text: q.querySelector(".q-text").value,
                media_url: q.querySelector(".q-media-url")
                    ? q.querySelector(".q-media-url").value
                    : null,
                is_case_sensitive: q.querySelector(".case-sensitive-input")
                    ? q.querySelector(".case-sensitive-input").checked
                    : false,
                options: options,
            });
        });
        categories.push({
            title: cat.querySelector(".c-title").value,
            time_limit: parseInt(cat.querySelector(".c-time").value) || 0,
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

AssessmentBuilder.handleAutosaveTrigger = function () {
    if (AssessmentBuilder.isInitializing) return;

    AssessmentBuilder.hasChanged = true;
    AssessmentBuilder.updateAutosaveIndicator(
        '<i class="fas fa-circle-notch text-amber-500"></i> Unsaved changes...',
    );
    AssessmentBuilder.resetIdleTimer();
};

// New function that delays the save until the user is fully idle
AssessmentBuilder.resetIdleTimer = function () {
    clearTimeout(AssessmentBuilder.autosaveTimer);

    // Only start the countdown if there are pending changes
    if (AssessmentBuilder.hasChanged && !AssessmentBuilder.isInitializing) {
        AssessmentBuilder.autosaveTimer = setTimeout(() => {
            AssessmentBuilder.autosaveToServer();
        }, AssessmentBuilder.SYNC_DELAY);
    }
};

AssessmentBuilder.autosaveToServer = async function () {
    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return;

    const payload = AssessmentBuilder.getPayload("draft");
    const payloadString = JSON.stringify(payload);

    if (payloadString === AssessmentBuilder.lastPayload) {
        AssessmentBuilder.hasChanged = false; // Reset if nothing changed
        return;
    }
    AssessmentBuilder.lastPayload = payloadString;

    try {
        await fetch(wrapper.dataset.autosaveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
            body: payloadString,
        });
        AssessmentBuilder.updateAutosaveIndicator(
            '<i class="fas fa-check-circle text-green-500"></i> Synced',
        );
        AssessmentBuilder.hasChanged = false; // Reset flag after successful sync
    } catch (e) {
        AssessmentBuilder.updateAutosaveIndicator(
            '<i class="fas fa-wifi-slash text-amber-500"></i> Offline',
        );
    }
};

AssessmentBuilder.updateAutosaveIndicator = function (html) {
    const el = document.getElementById("autosave-indicator");
    if (el) el.innerHTML = html;
};

AssessmentBuilder.currentMediaUploadQId = null;
AssessmentBuilder.selectedMediaFile = null;

AssessmentBuilder.openMediaModal = function (qId) {
    AssessmentBuilder.currentMediaUploadQId = qId;
    AssessmentBuilder.clearSelectedMedia();
    document.getElementById("media-upload-modal").classList.remove("hidden");
};
AssessmentBuilder.handleMediaFileSelect = function (input) {
    if (!input.files || input.files.length === 0) return;

    AssessmentBuilder.selectedMediaFile = input.files[0];
    document.getElementById("selected-media-name").innerText =
        AssessmentBuilder.selectedMediaFile.name;

    document.getElementById("media-dropzone").classList.add("hidden");
    document
        .getElementById("selected-media-display")
        .classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("flex");

    document.getElementById("start-media-upload-btn").disabled = false;
};

AssessmentBuilder.clearSelectedMedia = function () {
    AssessmentBuilder.selectedMediaFile = null;
    document.getElementById("media-file-input").value = "";

    document.getElementById("media-dropzone").classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("hidden");
    document.getElementById("selected-media-display").classList.remove("flex");

    document.getElementById("start-media-upload-btn").disabled = true;
};

// Add this state variable at the top of your file
AssessmentBuilder.currentUploadXhr = null;

// Replaces the existing closeMediaModal function
AssessmentBuilder.closeMediaModal = function () {
    // 1. Abort any ongoing upload when modal closes
    if (AssessmentBuilder.currentUploadXhr) {
        AssessmentBuilder.currentUploadXhr.abort();
        AssessmentBuilder.currentUploadXhr = null;
    }

    document.getElementById("media-upload-modal").classList.add("hidden");
    AssessmentBuilder.currentMediaUploadQId = null;

    // 2. Reset UI states safely
    const progressContainer = document.getElementById("upload-progress-container");
    if (progressContainer) progressContainer.classList.add("hidden");
    
    const btn = document.getElementById("start-media-upload-btn");
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i><span>Upload Media</span>';
    }
};

// Replaces the existing executeMediaUpload function
AssessmentBuilder.executeMediaUpload = function () {
    const wrapper = document.getElementById("assessment-wrapper");
    const btn = document.getElementById("start-media-upload-btn");
    const progressContainer = document.getElementById("upload-progress-container");
    const progressBar = document.getElementById("upload-progress-bar");
    const progressText = document.getElementById("upload-progress-text");

    if (!AssessmentBuilder.selectedMediaFile || !AssessmentBuilder.currentMediaUploadQId) return;

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Uploading...</span>';

    // Show progress bar
    if (progressContainer) {
        progressContainer.classList.remove("hidden");
        progressBar.style.width = "0%";
        progressText.innerText = "0%";
    }

    const formData = new FormData();
    formData.append("media_file", AssessmentBuilder.selectedMediaFile);

    const xhr = new XMLHttpRequest();
    AssessmentBuilder.currentUploadXhr = xhr;

    // Track Progress
    xhr.upload.addEventListener("progress", function (e) {
        if (e.lengthComputable && progressContainer) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percentComplete + "%";
            progressText.innerText = percentComplete + "%";
        }
    });

    // Handle Response
    xhr.addEventListener("load", function () {
        AssessmentBuilder.currentUploadXhr = null;
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    AssessmentBuilder.setMediaPreview(
                        AssessmentBuilder.currentMediaUploadQId,
                        data.media_url,
                        data.media_type,
                        data.original_name || AssessmentBuilder.selectedMediaFile.name
                    );
                    AssessmentBuilder.closeMediaModal();
                    AssessmentBuilder.handleAutosaveTrigger();
                } else {
                    alert(data.message || "Failed to upload media.");
                    resetUI();
                }
            } catch (e) {
                alert("Invalid server response.");
                resetUI();
            }
        } else {
            alert("Upload failed with status: " + xhr.status);
            resetUI();
        }
    });

    xhr.addEventListener("error", function () {
        AssessmentBuilder.currentUploadXhr = null;
        alert("Network error occurred during upload.");
        resetUI();
    });

    xhr.addEventListener("abort", function () {
        AssessmentBuilder.currentUploadXhr = null;
        // Aborted silently, reset the UI
        resetUI();
    });

    xhr.open("POST", wrapper.dataset.uploadUrl, true);
    xhr.setRequestHeader("X-CSRF-TOKEN", wrapper.dataset.csrf);
    xhr.setRequestHeader("Accept", "application/json");
    xhr.send(formData);

    function resetUI() {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (progressContainer) progressContainer.classList.add("hidden");
    }
};

AssessmentBuilder.setMediaPreview = function (qId, url, explicitType = null, originalName = null) {
    const block = document.getElementById(qId);
    const mediaInput = block.querySelector(".q-media-url") || block.querySelector(".q-image-url");
    if (mediaInput) mediaInput.value = url;

    const previewDiv = document.getElementById(`preview-${qId}`);
    // Use originalName if provided, otherwise extract from URL
    const displayFileName = originalName || url.split('/').pop().split('?')[0]; 
    const lowerUrl = url.toLowerCase();

    // Determine type based on explicit parameter or file extension
    let type = explicitType;
    if (!type) {
        if (lowerUrl.endsWith('.mp3') || lowerUrl.endsWith('.wav')) type = "audio";
        else if (lowerUrl.endsWith('.mp4') || lowerUrl.endsWith('.webm')) type = "video";
        else if (lowerUrl.endsWith('.pdf')) type = "pdf";
        else if (lowerUrl.endsWith('.zip') || lowerUrl.endsWith('.rar')) type = "archive";
        else type = "image";
    }

    // Apply the centered, gray-background layout from material.js
    previewDiv.className = "relative mt-3 mb-4 rounded-lg overflow-hidden border border-gray-200 flex flex-col items-center justify-center w-full bg-gray-50 p-2";

    let mediaHtml = "";

    if (type === "audio") {
        mediaHtml = `<audio controls src="${url}" class="w-full mt-2 outline-none"></audio>`;
    } else if (type === "video") {
        mediaHtml = `<div class="w-full bg-black rounded-lg overflow-hidden">
                        <video controls src="${url}" class="max-h-64 w-full"></video>
                     </div>
                     <span class="text-xs font-medium text-gray-500 mt-2">${displayFileName}</span>`;
    } else if (type === "pdf") {
        mediaHtml = `<div class="flex flex-col items-center p-4">
                        <i class="fas fa-file-pdf text-4xl text-red-500 mb-2"></i>
                        <span class="text-sm font-bold text-gray-700 mt-2">${displayFileName}</span>
                        <a href="${url}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 font-medium">View PDF</a>
                     </div>`;
    } else if (type === "archive") {
        mediaHtml = `<div class="flex flex-col items-center p-4">
                        <i class="fas fa-file-archive text-4xl text-yellow-500 mb-2"></i>
                        <span class="text-sm font-bold text-gray-700 mt-2">${displayFileName}</span>
                        <a href="${url}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 font-medium">Download Archive</a>
                     </div>`;
    } else {
        // Standard Image Layout
        mediaHtml = `<img src="${url}" class="max-h-64 object-contain rounded-lg shadow-sm">
                     <span class="text-xs font-medium text-gray-500 mt-2">${displayFileName}</span>`;
    }

    // Add the top-right trash button
    previewDiv.innerHTML = `
        ${mediaHtml}
        <button type="button" onclick="AssessmentBuilder.removeQuestionMedia('${qId}')" class="absolute top-2 right-2 h-8 w-8 bg-red-500/80 hover:bg-red-600 transition text-white rounded shadow-sm flex items-center justify-center">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    previewDiv.classList.remove('hidden');
};

AssessmentBuilder.removeQuestionMedia = function (qId) {
    const block = document.getElementById(qId);
    block.querySelector(".q-media-url").value = "";

    const previewDiv = document.getElementById(`preview-${qId}`);
    if (previewDiv) {
        previewDiv.innerHTML = "";
        previewDiv.classList.add("hidden");
    }

    AssessmentBuilder.handleAutosaveTrigger();
};

AssessmentBuilder.collectCategoriesData = function () {
    return AssessmentBuilder.getPayload("draft").categories;
};

AssessmentBuilder.saveCompleteExam = async function (btn, status) {
    clearTimeout(AssessmentBuilder.autosaveTimer);
    AssessmentBuilder.lastPayload = "";

    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return; // Guard

    const payload = AssessmentBuilder.getPayload(status);

    if (!payload.title || !payload.year_level) {
        return AssessmentBuilder.showModal(
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

            AssessmentBuilder.showModal("success", title, msg, () => {
                window.goToUrl(wrapper.dataset.manageUrl, window.currentNavBtn);
            });
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (e) {
        AssessmentBuilder.showModal("error", "Save Failed", e.message);
        AssessmentBuilder.resetBtn(btn, originalText);
    }
};

AssessmentBuilder.resetBtn = (btn, txt) => {
    btn.disabled = false;
    btn.innerHTML = txt;
};

AssessmentBuilder.deleteAssessmentFromBuilder = async function () {
    AssessmentBuilder.showModal(
        "confirm",
        "Discard Assessment?",
        "Are you sure you want to discard this entire assessment? This cannot be undone.",
        async () => {
            const wrapper = document.getElementById("assessment-wrapper");
            if (!wrapper) return; // Guard

            try {
                const response = await fetch(wrapper.dataset.deleteUrl, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": wrapper.dataset.csrf,
                        Accept: "application/json",
                    },
                });
                if (response.ok) {
                    window.goToUrl(
                        wrapper.dataset.redirectUrl,
                        window.currentNavBtn,
                    );
                }
            } catch (e) {
                AssessmentBuilder.showModal(
                    "error",
                    "Error",
                    "Failed to discard assessment.",
                );
            }
        },
    );
};

window.addEventListener("beforeunload", (event) => {
    if (AssessmentBuilder.lastPayload !== "") {
        event.preventDefault();
        event.returnValue = "";
    }
});

AssessmentBuilder.discardChangesAndExit = function (btn) {
    AssessmentBuilder.showModal(
        "confirm",
        "Discard Unsaved Changes?",
        "Are you sure you want to discard your unsaved work and exit? This cannot be undone.",
        async () => {
            const wrapper = document.getElementById("assessment-wrapper");
            if (!wrapper) return; // Guard

            const backModal = document.getElementById("back-modal");
            if (backModal) backModal.classList.add("hidden");

            clearTimeout(AssessmentBuilder.autosaveTimer);
            AssessmentBuilder.lastPayload = "";

            const isNew = wrapper.dataset.isNew === "true";

            const originalText = btn.innerHTML;
            btn.innerHTML =
                '<i class="fas fa-spinner fa-spin mr-2"></i> Discarding...';
            btn.disabled = true;

            localStorage.removeItem(
                "assessment_draft_" + wrapper.dataset.assessmentId,
            );

            try {
                if (isNew) {
                    await AssessmentBuilder.silentlyDeleteAndExit();
                    AssessmentBuilder.goToUrl(wrapper.dataset.redirectUrl);
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
                    AssessmentBuilder.goToUrl(wrapper.dataset.manageUrl);
                }
            } catch (e) {
                console.warn("Failed to clear drafts or delete:", e);
                AssessmentBuilder.goToUrl(wrapper.dataset.redirectUrl);
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

AssessmentBuilder.showModal = function (type, title, message, callback = null) {
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
    if (!btn) return;
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
window.currentNavBtn = null; // Store it globally for the modals to use

window.goToUrl = function (url) {
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

AssessmentBuilder.silentlyDeleteAndExit = async function () {
    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return; // Guard
    try {
        await fetch(wrapper.dataset.deleteUrl, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
        });
    } catch (e) {
        console.warn("Failed to delete empty assessment");
    }
};

// Accept the nav element directly from the HTML click
AssessmentBuilder.handleAssessmentBackButton = async function (btn) {
    const wrapper = document.getElementById("assessment-wrapper");
    if (!wrapper) return;

    const isNew = wrapper.dataset.isNew === "true";
    const manageUrl = wrapper.dataset.manageUrl;
    const redirectUrl = wrapper.dataset.redirectUrl;

    if (AssessmentBuilder.hasChanged) {
        const backModal = document.getElementById("back-modal");
        if (backModal) backModal.classList.remove("hidden");
    } else {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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

window.openImportModal = function () {
    window.clearSelectedFile();
    document.getElementById("excel-import-modal").classList.remove("hidden");
};

window.closeImportModal = function () {
    document.getElementById("excel-import-modal").classList.add("hidden");
};

window.handleFileSelect = function (input) {
    if (!input.files || input.files.length === 0) return;

    selectedFile = input.files[0];
    document.getElementById("selected-file-name").innerText = selectedFile.name;
    document.getElementById("file-dropzone").classList.add("hidden");
    document.getElementById("selected-file-display").classList.remove("hidden");
    document.getElementById("selected-file-display").classList.add("flex");
    document.getElementById("start-upload-btn").disabled = false;
};

window.clearSelectedFile = function () {
    selectedFile = null;
    document.getElementById("excel-file-input").value = "";
    document.getElementById("file-dropzone").classList.remove("hidden");
    document.getElementById("selected-file-display").classList.add("hidden");
    document.getElementById("selected-file-display").classList.remove("flex");
    document.getElementById("start-upload-btn").disabled = true;
};


