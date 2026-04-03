window.MaterialBuilder = window.MaterialBuilder || {};

MaterialBuilder.state = {
    title: "",
    description: "",
    categories: [],
};

MaterialBuilder.hasChanged = false;
MaterialBuilder.sessionDirty = false;
MaterialBuilder.catCount = 0;
MaterialBuilder.isInitializing = false;
MaterialBuilder.autosaveTimer = null;
MaterialBuilder.SYNC_DELAY = 3000;
MaterialBuilder.lastPayload = "";
MaterialBuilder.currentMediaUploadQId = null;
MaterialBuilder.selectedMediaFile = null;
MaterialBuilder.activityListenersAdded = false;

MaterialBuilder.enforceExamPosition = function() {
    const container = document.getElementById("builder-container");
    if (!container) return;
    const exams = container.querySelectorAll('.category-block[data-section-type="exam"]');
    exams.forEach(exam => container.appendChild(exam));
};

MaterialBuilder.initSortable = function () {
    if (typeof Sortable === "undefined") return;

    const container = document.getElementById("builder-container");
    if (container && !container.sortableInstance) {
        container.sortableInstance = new Sortable(container, {
            animation: 150,
            handle: ".drag-handle-cat",
            ghostClass: "opacity-50",
            onEnd: function () {
                MaterialBuilder.enforceExamPosition();
                MaterialBuilder.handleAutosaveTrigger();
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
                    MaterialBuilder.handleAutosaveTrigger();
                },
            });
        }
    });
};

MaterialBuilder.calculateTotalTime = function () {
    let total = 0;
    document.querySelectorAll(".c-time").forEach((input) => {
        total += parseInt(input.value) || 0;
    });
    const display = document.getElementById("total-time-display");
    if (display) {
        display.innerHTML = `<i class="far fa-clock mr-1"></i> ${total} mins`;
    }
};

MaterialBuilder.initBuilder = function () {
    MaterialBuilder.isInitializing = true;
    MaterialBuilder.catCount = 0;
    MaterialBuilder.lastPayload = "";
    MaterialBuilder.hasChanged = false;
    MaterialBuilder.sessionDirty = false;
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
            if (
                serverDraft &&
                serverDraft.categories &&
                serverDraft.categories.length > 0
            ) {
                existingData = serverDraft.categories;
                if (serverDraft.title)
                    document.getElementById("setup-title").value =
                        serverDraft.title;
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
            MaterialBuilder.renderExistingCategory(cat);
        });
        MaterialBuilder.enforceExamPosition();
    }

    wrapper.addEventListener("input", (e) => {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) {
            MaterialBuilder.handleAutosaveTrigger();
        }
    });

    if (!MaterialBuilder.activityListenersAdded) {
        const resetTimer = () => MaterialBuilder.resetIdleTimer();
        window.addEventListener("mousemove", resetTimer);
        window.addEventListener("keydown", resetTimer);
        window.addEventListener("mousedown", resetTimer);
        window.addEventListener("touchstart", resetTimer);
        window.addEventListener("scroll", resetTimer, true);
        MaterialBuilder.activityListenersAdded = true;
    }

    MaterialBuilder.updateAutosaveIndicator("Ready");
    MaterialBuilder.initSortable();
    MaterialBuilder.calculateTotalTime();

    setTimeout(() => {
        MaterialBuilder.isInitializing = false;
        MaterialBuilder.hasChanged = false;
    }, 500);
};

MaterialBuilder.renderExistingCategory = function (catData) {
    const sectionType = catData.section_type || "lesson";
    MaterialBuilder.addSection(sectionType);

    const latestCat = document.querySelector(".category-block:last-child");
    latestCat.querySelector(".c-title").value = catData.title || "";
    latestCat.querySelector(".category-display-title").innerText =
        catData.title || (sectionType === "exam" ? "Final Exam" : "New Lesson");

    if (catData.time_limit) {
        const timeInput = latestCat.querySelector(".c-time");
        if (timeInput) timeInput.value = catData.time_limit;
    }

    const qContainer = latestCat.querySelector('[id^="q-container-"]');
    qContainer.innerHTML = "";

    if (catData.questions && catData.questions.length > 0) {
        catData.questions.forEach((q) => {
            const subType = q.type || "content";
            let mainType =
                sectionType === "exam"
                    ? "exam"
                    : subType === "content"
                      ? "content"
                      : "quiz";

            MaterialBuilder.addItem(
                qContainer.id.split("-").pop(),
                mainType,
                subType,
            );

            const latestQ = qContainer.querySelector(
                ".question-block:last-child",
            );
            latestQ.querySelector(".q-text").value =
                q.text || q.question_text || "";

            const mediaUrl = q.media_url || q.image_url;
            if (mediaUrl) MaterialBuilder.setMediaPreview(latestQ.id, mediaUrl);

            latestQ.querySelector(".options-list").innerHTML = "";

            if (q.options && q.options.length > 0 && mainType !== "content") {
                const isCaseSensitive =
                    q.is_case_sensitive == 1 || q.is_case_sensitive === true;
                q.options.forEach((opt) => {
                    MaterialBuilder.addOptionToQuestion(
                        latestQ.id,
                        subType,
                        opt.is_correct == 1 || opt.is_correct === true,
                        opt.text || opt.option_text || "",
                        isCaseSensitive,
                    );
                });
            } else if (subType === "true_false" && mainType !== "content") {
                MaterialBuilder.addOptionToQuestion(
                    latestQ.id,
                    "true_false",
                    false,
                    "True",
                );
                MaterialBuilder.addOptionToQuestion(
                    latestQ.id,
                    "true_false",
                    false,
                    "False",
                );
            }
        });
    }
};

MaterialBuilder.addSection = function (type = "lesson") {
    const container = document.getElementById("builder-container");
    if (!container) return;

    MaterialBuilder.catCount++;
    const catId = `cat-${MaterialBuilder.catCount}`;
    const badgeColor = type === "exam" ? "bg-red-100 text-red-600" : "bg-blue-100 text-blue-600";
    const borderColor = type === "exam" ? "border-red-200" : "border-blue-200";
    const titleDefault = type === "exam" ? "Final Exam" : "New Lesson";
    const icon = type === "exam" ? "fa-file-signature" : "fa-book-open";
    const defaultTime = type === "exam" ? "30" : "10";

    const dragHandleHtml = type === "exam" 
        ? "" 
        : `<div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-700 p-2 drag-handle-cat rounded hover:bg-gray-200 transition" title="Drag to reorder Section"><i class="fas fa-grip-vertical"></i></div>`;

    const controlsHtml = `
        <div class="flex flex-col md:flex-row items-center gap-3">
            ${type === "lesson" ? `
                <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, 'content')" class="flex-1 w-full py-3 text-blue-600 bg-blue-50 text-sm font-bold hover:bg-blue-100 transition rounded-xl border border-blue-100 flex items-center justify-center shadow-sm">
                    <i class="fas fa-align-left mr-2"></i> Add Lesson Content
                </button>` : ""
            }
            
            <div class="flex items-center flex-1 w-full shadow-sm rounded-xl border border-${type === "exam" ? "red-100" : "purple-100"}">
                <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, '${type === "exam" ? "exam" : "quiz"}', 'mcq')" class="flex-1 py-3 text-${type === "exam" ? "red-600 bg-red-50" : "purple-600 bg-purple-50"} text-sm font-bold hover:bg-${type === "exam" ? "red-100" : "purple-100"} transition flex items-center justify-center border-r border-white rounded-l-xl">
                    <i class="fas fa-plus-circle mr-2"></i> Add Multiple Choice
                </button>
                <div class="relative group/dropdown">
                    <button type="button" onclick="MaterialBuilder.toggleDropdown(this)" class="px-4 py-3 text-${type === "exam" ? "red-600 bg-red-50" : "purple-600 bg-purple-50"} hover:bg-${type === "exam" ? "red-100" : "purple-100"} transition rounded-r-xl">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg z-50 py-1 dropdown-menu">
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, '${type === "exam" ? "exam" : "quiz"}', 'checkbox'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-check-square mr-2"></i> Checkboxes</button>
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, '${type === "exam" ? "exam" : "quiz"}', 'text'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-pencil-alt mr-2"></i> Short Text</button>
                        <button type="button" onclick="MaterialBuilder.addItem(${MaterialBuilder.catCount}, '${type === "exam" ? "exam" : "quiz"}', 'true_false'); MaterialBuilder.toggleDropdown(this.closest('.relative').querySelector('button'))" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-adjust mr-2"></i> True or False</button>
                    </div>
                </div>
            </div>
        </div>`;

    const html = `
        <div class="bg-white rounded-2xl shadow-sm border ${borderColor} category-block transition-all mb-4" id="${catId}" data-section-type="${type}">
            <div class="p-4 bg-gray-50/50 flex items-center justify-between cursor-pointer" onclick="MaterialBuilder.toggleCategory('${catId}', event)">
                <div class="flex items-center gap-4 flex-1">
                    <div class="h-8 w-8 rounded-lg ${badgeColor} flex items-center justify-center font-bold text-sm cat-number-badge">
                        <i class="fas ${icon}"></i>
                    </div>
                    <span class="font-bold text-gray-700 category-display-title">${titleDefault}</span>
                </div>
                <div class="flex items-center gap-1">
                    ${dragHandleHtml}
                    <button type="button" onclick="MaterialBuilder.removeElement('${catId}')" class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition ml-2"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 category-body">
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">${type === "exam" ? "Exam Title" : "Lesson Title"}</label>
                        <input type="text" class="c-title w-full px-4 py-2 border border-gray-200 rounded-xl outline-none focus:border-[#a52a2a] transition font-medium" placeholder="${titleDefault}" onkeyup="MaterialBuilder.updateCatDisplay(this, '${titleDefault}')">
                    </div>
                    <div class="w-full md:w-32">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Est. Time (Mins)</label>
                        <input type="number" class="c-time w-full px-4 py-2 border border-gray-200 rounded-xl outline-none focus:border-[#a52a2a] transition font-medium text-center" value="${defaultTime}" onchange="MaterialBuilder.calculateTotalTime(); MaterialBuilder.handleAutosaveTrigger()">
                    </div>
                </div>
                
                <div id="q-container-${MaterialBuilder.catCount}" class="space-y-4 mb-4"></div>
                
                <div class="mt-6 relative z-30">${controlsHtml}</div>
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    MaterialBuilder.enforceExamPosition();
    MaterialBuilder.initSortable();
    MaterialBuilder.calculateTotalTime();
    MaterialBuilder.handleAutosaveTrigger();
};


MaterialBuilder.toggleDropdown = function (btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll(".dropdown-menu").forEach((el) => {
        if (el !== menu) el.classList.add("hidden");
    });
    menu.classList.toggle("hidden");
};

MaterialBuilder.addItem = function (
    cId,
    mainType,
    subType = "content",
    afterElement = null,
) {
    const container = document.getElementById(`q-container-${cId}`);
    if (!container) return;

    const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
    const icon =
        mainType === "content" ? "fa-align-left" : "fa-question-circle";
    const bgClass =
        mainType === "content"
            ? "bg-blue-50/30 border-blue-100"
            : mainType === "quiz"
              ? "bg-purple-50/30 border-purple-100"
              : "bg-red-50/30 border-red-100";
    const typeDisplay =
        mainType === "content"
            ? "Lesson Content Block"
            : `${mainType === "quiz" ? "Practice Quiz" : "Exam Question"}: ${subType.toUpperCase()}`;

    const html = `
        <div class="p-4 rounded-xl border border-gray-100 question-block relative group ${bgClass}" id="${qId}" data-main-type="${mainType}" data-sub-type="${subType}">
            <div class="flex justify-between items-start mb-2 cursor-pointer" onclick="MaterialBuilder.toggleQuestion('${qId}', event)">
                <div class="flex items-center gap-2 overflow-hidden pr-2">
                    <div class="h-6 w-6 flex items-center justify-center text-gray-400 group-hover:text-gray-600 transition q-chevron-icon shrink-0"><i class="fas fa-chevron-up text-xs"></i></div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1 shrink-0"><i class="fas ${icon}"></i> ${typeDisplay}</span>
                    <span class="text-xs text-gray-500 font-medium truncate ml-2 q-preview-text hidden"></span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-600 px-2 drag-handle-q rounded hover:bg-gray-200 transition"><i class="fas fa-grip-vertical"></i></div>
                    <button type="button" onclick="MaterialBuilder.removeElement('${qId}')" class="h-7 w-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition rounded-md hover:bg-red-50 ml-1"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="question-body">
                <div class="relative mb-3">
                    <textarea class="q-text w-full pl-3 pr-10 py-3 bg-white border border-gray-200 rounded-lg outline-none font-medium text-sm focus:border-[#a52a2a] min-h-[60px]" placeholder="Enter content..."></textarea>
                    <input type="hidden" class="q-media-url" value="">
                    <button type="button" onclick="MaterialBuilder.openMediaModal('${qId}')" class="absolute right-2 top-2 h-8 w-8 flex items-center justify-center text-gray-400 hover:text-[#a52a2a] hover:bg-gray-100 rounded transition"><i class="fas fa-photo-video"></i></button>
                </div>
                <div id="preview-${qId}" class="hidden"></div>
                <div class="options-list space-y-2 mb-3"></div>
                ${mainType !== "content" && (subType === "mcq" || subType === "checkbox") ? `<button type="button" onclick="MaterialBuilder.addOptionToQuestion('${qId}', '${subType}')" class="text-[10px] font-bold text-[#a52a2a] hover:underline uppercase flex items-center mt-2"><i class="fas fa-plus mr-1"></i> Add Choice</button>` : ""}
            </div>
            <div class="absolute -bottom-4 left-0 right-0 flex justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20 pointer-events-none">
                <div class="pointer-events-auto"><button type="button" onclick="MaterialBuilder.addItem('${cId}', '${mainType}', '${subType}', this.closest('.question-block'))" class="bg-[#a52a2a] text-white rounded-full h-7 w-7 flex items-center justify-center shadow-lg hover:scale-110 transition-transform"><i class="fas fa-plus text-[10px]"></i></button></div>
            </div>
        </div>`;

    if (afterElement) afterElement.insertAdjacentHTML("afterend", html);
    else container.insertAdjacentHTML("beforeend", html);

    if (mainType !== "content") {
        if (subType === "mcq" || subType === "checkbox") {
            MaterialBuilder.addOptionToQuestion(qId, subType, true, "");
            MaterialBuilder.addOptionToQuestion(qId, subType, false, "");
        } else if (subType === "text")
            MaterialBuilder.addOptionToQuestion(qId, "text", true, "");
        else if (subType === "true_false") {
            MaterialBuilder.addOptionToQuestion(
                qId,
                "true_false",
                false,
                "True",
            );
            MaterialBuilder.addOptionToQuestion(
                qId,
                "true_false",
                false,
                "False",
            );
        }
    }
    MaterialBuilder.initSortable();
};

MaterialBuilder.addOptionToQuestion = function (
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

    if (type === "mcq" || type === "true_false") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm" value="${text}" ${type === "true_false" ? "readonly" : ""}>
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition"><input type="radio" name="correct-${qId}" class="is-correct-input h-4 w-4" ${isCorrect ? "checked" : ""}><span class="text-[10px] font-bold uppercase">Correct</span></label>
                    ${type === "mcq" ? `<button type="button" onclick="MaterialBuilder.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition"><i class="fas fa-times-circle"></i></button>` : ""}
                </div>
            </div>`;
    } else if (type === "checkbox") {
        optHtml = `
            <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm" value="${text}">
                <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                    <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition"><input type="checkbox" class="is-correct-input h-4 w-4" ${isCorrect ? "checked" : ""}><span class="text-[10px] font-bold uppercase">Correct</span></label>
                    <button type="button" onclick="MaterialBuilder.removeOption(this, '${qId}')" class="text-gray-300 hover:text-red-500 transition"><i class="fas fa-times-circle"></i></button>
                </div>
            </div>`;
    } else if (type === "text") {
        optHtml = `
            <div class="flex items-center gap-3 bg-green-50/50 px-3 py-2 rounded-lg border border-green-200 option-row transition">
                <input type="text" class="option-input w-full bg-transparent outline-none text-sm font-medium" value="${text}">
                <input type="hidden" class="is-correct-input" value="true">
                <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 border-l border-green-200 pl-3 shrink-0"><input type="checkbox" class="case-sensitive-input h-4 w-4" ${isCaseSensitive ? "checked" : ""}><span class="text-[10px] font-bold uppercase">Case Sensitive</span></label>
            </div>`;
    }
    list.insertAdjacentHTML("beforeend", optHtml);
    MaterialBuilder.handleAutosaveTrigger();
};

MaterialBuilder.removeOption = function (btn, qId) {
    btn.closest(".option-row").remove();
    MaterialBuilder.handleAutosaveTrigger();
};
MaterialBuilder.removeElement = function (id) {
    document.getElementById(id).remove();
    MaterialBuilder.calculateTotalTime();
    MaterialBuilder.handleAutosaveTrigger();
};
MaterialBuilder.toggleCategory = function (id, event) {
    if (
        !["INPUT", "BUTTON", "I"].includes(event.target.tagName) &&
        !event.target.closest(".drag-handle-cat")
    )
        document
            .querySelector(`#${id} .category-body`)
            .classList.toggle("hidden");
};
MaterialBuilder.toggleQuestion = function (id, event) {
    if (
        event.target.closest("button") ||
        event.target.closest(".drag-handle-q")
    )
        return;
    const block = document.getElementById(id);
    const body = block.querySelector(".question-body");
    body.classList.toggle("hidden");
    const preview = block.querySelector(".q-preview-text");
    if (body.classList.contains("hidden")) {
        preview.innerText = "- " + block.querySelector(".q-text").value.trim();
        preview.classList.remove("hidden");
    } else preview.classList.add("hidden");
};

MaterialBuilder.updateCatDisplay = function (input, fallback) {
    input
        .closest(".category-block")
        .querySelector(".category-display-title").innerText =
        input.value || fallback;
};
MaterialBuilder.getPayload = function (status) {
    const categories = [];
    document.querySelectorAll(".category-block").forEach((cat) => {
        const questions = [];
        cat.querySelectorAll(".question-block").forEach((q) => {
            const options = [];
            if (q.dataset.mainType !== "content") {
                q.querySelectorAll(".option-row").forEach((opt) => {
                    const check = opt.querySelector(".is-correct-input");
                    options.push({
                        text: opt.querySelector(".option-input").value,
                        is_correct:
                            check.type === "radio" || check.type === "checkbox"
                                ? check.checked
                                    ? 1
                                    : 0
                                : 1,
                    });
                });
            }
            questions.push({
                type: q.dataset.subType,
                text: q.querySelector(".q-text").value,
                media_url: q.querySelector(".q-media-url").value,
                is_case_sensitive:
                    q.querySelector(".case-sensitive-input")?.checked || false,
                options: options,
            });
        });
        categories.push({
            section_type: cat.dataset.sectionType,
            title: cat.querySelector(".c-title").value,
            time_limit: parseInt(cat.querySelector(".c-time").value) || 0,
            questions: questions,
        });
    });
    return {
        status,
        title: document.getElementById("setup-title").value,
        description: document.getElementById("setup-desc").value,
        categories,
    };
};

MaterialBuilder.handleAutosaveTrigger = function () {
    if (!MaterialBuilder.isInitializing) {
        MaterialBuilder.hasChanged = true;
        MaterialBuilder.sessionDirty = true;
        MaterialBuilder.updateAutosaveIndicator(
            '<i class="fas fa-circle-notch text-amber-500"></i> Unsaved changes...',
        );
        MaterialBuilder.resetIdleTimer();
    }
};
MaterialBuilder.resetIdleTimer = function () {
    clearTimeout(MaterialBuilder.autosaveTimer);
    if (MaterialBuilder.hasChanged && !MaterialBuilder.isInitializing)
        MaterialBuilder.autosaveTimer = setTimeout(
            () => MaterialBuilder.autosaveToServer(),
            MaterialBuilder.SYNC_DELAY,
        );
};
MaterialBuilder.autosaveToServer = async function () {
    const wrapper = document.getElementById("material-wrapper");
    const payload = MaterialBuilder.getPayload("draft");
    const payloadString = JSON.stringify(payload);
    if (payloadString === MaterialBuilder.lastPayload) {
        MaterialBuilder.hasChanged = false;
        return;
    }
    MaterialBuilder.lastPayload = payloadString;
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
        MaterialBuilder.updateAutosaveIndicator(
            '<i class="fas fa-check-circle text-green-500"></i> Synced',
        );
        MaterialBuilder.hasChanged = false;
    } catch (e) {
        MaterialBuilder.updateAutosaveIndicator(
            '<i class="fas fa-wifi-slash text-amber-500"></i> Offline',
        );
    }
};

MaterialBuilder.updateAutosaveIndicator = function (html) {
    document.getElementById("autosave-indicator").innerHTML = html;
};
MaterialBuilder.openMediaModal = function (qId) {
    MaterialBuilder.currentMediaUploadQId = qId;
    MaterialBuilder.clearSelectedMedia();
    document.getElementById("media-upload-modal").classList.remove("hidden");
};

MaterialBuilder.handleMediaFileSelect = function (input) {
    MaterialBuilder.selectedMediaFile = input.files[0];
    document.getElementById("selected-media-name").innerText =
        input.files[0].name;
    document.getElementById("media-dropzone").classList.add("hidden");
    document
        .getElementById("selected-media-display")
        .classList.remove("hidden");
    document.getElementById("start-media-upload-btn").disabled = false;
};
MaterialBuilder.clearSelectedMedia = function () {
    MaterialBuilder.selectedMediaFile = null;
    document.getElementById("media-file-input").value = "";
    document.getElementById("media-dropzone").classList.remove("hidden");
    document.getElementById("selected-media-display").classList.add("hidden");
    document.getElementById("start-media-upload-btn").disabled = true;
};

// Add this state variable at the top of your file near the others
MaterialBuilder.currentUploadXhr = null;

// Replaces the existing closeMediaModal function
MaterialBuilder.closeMediaModal = function () {
    // 1. Abort any ongoing upload when modal closes
    if (MaterialBuilder.currentUploadXhr) {
        MaterialBuilder.currentUploadXhr.abort();
        MaterialBuilder.currentUploadXhr = null;
    }
    
    document.getElementById("media-upload-modal").classList.add("hidden");
    
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
MaterialBuilder.executeMediaUpload = function () {
    const wrapper = document.getElementById("material-wrapper");
    const btn = document.getElementById("start-media-upload-btn");
    const progressContainer = document.getElementById("upload-progress-container");
    const progressBar = document.getElementById("upload-progress-bar");
    const progressText = document.getElementById("upload-progress-text");

    if (!MaterialBuilder.selectedMediaFile || !MaterialBuilder.currentMediaUploadQId) return;

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
    formData.append("media_file", MaterialBuilder.selectedMediaFile);

    const xhr = new XMLHttpRequest();
    MaterialBuilder.currentUploadXhr = xhr;

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
        MaterialBuilder.currentUploadXhr = null;
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    MaterialBuilder.setMediaPreview(
                        MaterialBuilder.currentMediaUploadQId,
                        data.media_url,
                        data.media_type,
                        data.original_name || MaterialBuilder.selectedMediaFile.name
                    );
                    MaterialBuilder.closeMediaModal();
                    MaterialBuilder.handleAutosaveTrigger();
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
        MaterialBuilder.currentUploadXhr = null;
        alert("Network error occurred during upload.");
        resetUI();
    });

    xhr.addEventListener("abort", function () {
        MaterialBuilder.currentUploadXhr = null;
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
MaterialBuilder.setMediaPreview = function (qId, url, type = null, originalName = null) {
    const block = document.getElementById(qId);
    block.querySelector(".q-media-url").value = url;
    const previewDiv = document.getElementById(`preview-${qId}`);
    
    // Extract display name from originalName or fallback to URL
    const displayFileName = originalName || url.split('/').pop().split('?')[0]; 
    const lowerUrl = url.toLowerCase();

    previewDiv.className =
        "relative mt-3 mb-4 rounded-lg overflow-hidden border border-gray-200 flex flex-col items-center justify-center w-full bg-gray-50 p-2";

    let mediaHtml = "";

    // Comprehensive Type Detection
    if (type === "audio" || lowerUrl.endsWith('.mp3') || lowerUrl.endsWith('.wav')) {
        mediaHtml = `<audio controls src="${url}" class="w-full mt-2"></audio>`;
    } else if (type === "video" || lowerUrl.endsWith('.mp4') || lowerUrl.endsWith('.webm')) {
        mediaHtml = `<div class="w-full bg-black rounded-lg overflow-hidden">
                        <video controls src="${url}" class="max-h-64 w-full"></video>
                     </div>
                     <span class="text-xs font-medium text-gray-500 mt-2">${displayFileName}</span>`;
    } else if (type === "pdf" || lowerUrl.endsWith('.pdf')) {
        mediaHtml = `<div class="flex flex-col items-center p-4">
                        <i class="fas fa-file-pdf text-4xl text-red-500 mb-2"></i>
                        <span class="text-sm font-bold text-gray-700 mt-2">${displayFileName}</span>
                        <a href="${url}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 font-medium">View PDF</a>
                     </div>`;
    } else if (type === "zip" || type === "archive" || lowerUrl.endsWith('.zip') || lowerUrl.endsWith('.rar')) {
        mediaHtml = `<div class="flex flex-col items-center p-4">
                        <i class="fas fa-file-archive text-4xl text-yellow-500 mb-2"></i>
                        <span class="text-sm font-bold text-gray-700 mt-2">${displayFileName}</span>
                        <a href="${url}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 font-medium">Download Archive</a>
                     </div>`;
    } else {
        // Assume Image for everything else
        mediaHtml = `<img src="${url}" class="max-h-64 object-contain rounded-lg shadow-sm">
                     <span class="text-xs font-medium text-gray-500 mt-2">${displayFileName}</span>`;
    }

    previewDiv.innerHTML = `${mediaHtml}<button type="button" onclick="MaterialBuilder.removeQuestionMedia('${qId}')" class="absolute top-2 right-2 h-8 w-8 bg-red-500/80 hover:bg-red-600 transition text-white rounded shadow-sm flex items-center justify-center"><i class="fas fa-trash"></i></button>`;
    previewDiv.classList.remove('hidden');
};


MaterialBuilder.removeQuestionMedia = function (qId) {
    const block = document.getElementById(qId);
    block.querySelector(".q-media-url").value = "";
    document.getElementById(`preview-${qId}`).className = "hidden";
    MaterialBuilder.handleAutosaveTrigger();
};
MaterialBuilder.saveCompleteMaterial = async function (btn, status) {
    const wrapper = document.getElementById("material-wrapper");
    const payload = MaterialBuilder.getPayload(status);
    if (!payload.title)
        return MaterialBuilder.showModal(
            "warning",
            "Missing Information",
            "Enter a title.",
        );
    btn.disabled = true;
    const formData = new FormData();
    formData.append("title", payload.title);
    formData.append("description", payload.description);
    formData.append("status", status);
    formData.append("categories", JSON.stringify(payload.categories));
    const thumb = document.getElementById("thumbnail-upload");
    if (thumb.files.length > 0) formData.append("thumbnail", thumb.files[0]);
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
        if (result.success)
            MaterialBuilder.showModal(
                "success",
                status === "published" ? "Published!" : "Saved!",
                "Successfully Saved Changes.",
                () => MaterialBuilder.goToUrl(wrapper.dataset.manageUrl),
            );
    } catch (e) {
        MaterialBuilder.showModal("error", "Failed", e.message);
        btn.disabled = false;
    }
};

MaterialBuilder.discardChangesAndExit = function (btn) {
    MaterialBuilder.showModal(
        "confirm",
        "Discard Changes?",
        "Work will be lost.",
        async () => {
            const wrapper = document.getElementById("material-wrapper");
            const isNew = wrapper.dataset.isNew === "true";
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
        },
    );
};

MaterialBuilder.handleBackButton = async function (btn) {
    const wrapper = document.getElementById("material-wrapper");
    if (MaterialBuilder.sessionDirty)
        document.getElementById("back-modal").classList.remove("hidden");
    else {
        if (wrapper.dataset.isNew === "true") {
            await MaterialBuilder.silentlyDeleteAndExit();
            MaterialBuilder.goToUrl(wrapper.dataset.redirectUrl);
        } else MaterialBuilder.goToUrl(wrapper.dataset.manageUrl);
    }
};

MaterialBuilder.showModal = function (type, title, message, callback = null) {
    const modal = document.getElementById("status-modal");
    const icon = document.getElementById("status-modal-icon");
    const btn = document.getElementById("status-modal-btn");
    document.getElementById("status-modal-title").innerText = title;
    document.getElementById("status-modal-message").innerText = message;
    icon.className =
        "h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl " +
        (type === "success"
            ? "bg-green-50 text-green-500"
            : type === "error"
              ? "bg-red-50 text-red-500"
              : "bg-amber-50 text-amber-500");
    btn.className =
        "w-full py-3.5 text-white font-bold rounded-xl " +
        (type === "success"
            ? "bg-green-600"
            : type === "error"
              ? "bg-red-600"
              : "bg-amber-500");
    modal.classList.remove("hidden");
    btn.onclick = () => {
        modal.classList.add("hidden");
        if (callback) callback();
    };
};

MaterialBuilder.goToUrl = function (url) {
    // 1. Ensure modals are closed so they don't persist on the screen after loadPartial
    const backModal = document.getElementById("back-modal");
    const statusModal = document.getElementById("status-modal");
    if (backModal) backModal.classList.add("hidden");
    if (statusModal) statusModal.classList.add("hidden");

    // 2. Use loadPartial if it exists in the global scope, otherwise fallback to standard navigation
    if (typeof loadPartial === 'function') {
        loadPartial(url);
    } else if (typeof window.loadPartial === 'function') {
        window.loadPartial(url);
    } else {
        window.location.href = url;
    }
};

MaterialBuilder.silentlyDeleteAndExit = async function () {
    const wrapper = document.getElementById("material-wrapper");
    try {
        await fetch(wrapper.dataset.deleteUrl, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
        });
    } catch (e) {}
};