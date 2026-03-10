window.builderState = {
    title: "",
    year_level: "",
    description: "",
    categories: [],
};

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
    clearTimeout(autosaveTimer);

    const wrapper = document.getElementById("assessment-wrapper");
    const container = document.getElementById("builder-container");

    if (!wrapper || !container) return;

    // 2. FORCE CLEAR OLD DOM ELEMENTS
    container.innerHTML = "";
    const id = wrapper.dataset.assessmentId; // (Assuming you applied the fix from earlier!)

    const existingDataEl = document.getElementById("existing-data");
    let existingData = [];

    // 1. Load relational data (if they previously hit "Save" or "Publish")
    if (existingDataEl && existingDataEl.value) {
        existingData = JSON.parse(existingDataEl.value);
    }

    // 2. Load Server Autosave
    const serverDraftEl = document.getElementById("server-draft-data");
    if (serverDraftEl && serverDraftEl.value) {
        try {
            const serverDraft = JSON.parse(serverDraftEl.value);
            if (serverDraft) {
                // If it's our new format containing categories, title, etc.
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
                    existingData = serverDraft; // Fallback for your old array format
                }
            }
        } catch (e) {
            console.warn("Invalid server draft JSON");
        }
    }

    // 3. Load Local Storage
    const localDraft = localStorage.getItem("assessment_draft_" + id);
    if (localDraft) {
        try {
            const parsed = JSON.parse(localDraft);
            if (parsed) {
                if (parsed.categories && parsed.categories.length > 0) {
                    existingData = parsed.categories;
                }
                // Restore text fields from local storage too
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

    /* GLOBAL AUTOSAVE LISTENER */

    wrapper.addEventListener("input", (e) => {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) {
            window.handleAutosaveTrigger();
        }
    });
};

/* =========================================
   AUTOSAVE TRIGGER
========================================= */

window.handleAutosaveTrigger = function () {
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

    /* Prevent duplicate saves */
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

        // Parse the JSON response so we can read error messages
        const responseData = await response.json();

        if (response.ok && responseData.success) {
            window.updateAutosaveIndicator(
                '<i class="fas fa-check-circle text-green-500"></i> Synced',
            );
        } else {
            // Throw the exact error message provided by Laravel
            throw new Error(responseData.message || "Server rejected the save");
        }
    } catch (e) {
        window.updateAutosaveIndicator(
            '<i class="fas fa-wifi-slash text-amber-500"></i> Offline',
        );

        // This will print the exact SQL or PHP error to your browser's Developer Console (F12)
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
                <button type="button" onclick="window.addQuestion(${window.catCount})" class="w-full py-3 rounded-xl border border-dashed border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50 transition">
                    <i class="fas fa-plus-circle mr-2"></i> Add Question
                </button>
            </div>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);
    window.addQuestion(window.catCount);
};

window.addQuestion = function (cId) {
    const container = document.getElementById(`q-container-${cId}`);
    if (!container) return;

    const qId = `q-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
    const html = `
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 question-block relative group" id="${qId}">
            <button type="button" onclick="window.removeElement('${qId}')" class="absolute top-2 right-2 h-7 w-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition">
                <i class="fas fa-times"></i>
            </button>
            <input type="text" class="q-text w-full px-3 py-2 bg-white border border-gray-200 rounded-lg outline-none mb-3 font-medium text-sm focus:border-[#a52a2a]" placeholder="Enter Question...">
            
            <div class="options-list space-y-2 mb-3"></div>
            
            <button type="button" onclick="window.addOptionToQuestion('${qId}')" class="text-[10px] font-bold text-[#a52a2a] hover:underline uppercase flex items-center">
                <i class="fas fa-plus mr-1"></i> Add Choice
            </button>
        </div>`;

    container.insertAdjacentHTML("beforeend", html);

    // GUARANTEE TWO DEFAULT OPTIONS ARE ADDED IMMEDIATELY
    window.addOptionToQuestion(qId, true, ""); // First option (marked as correct by default)
    window.addOptionToQuestion(qId, false, ""); // Second option
};

window.addOptionToQuestion = function (qId, isCorrect = false, text = "") {
    const list = document.querySelector(`#${qId} .options-list`);

    // Generate a placeholder based on current option count (e.g., "Choice 1", "Choice 2")
    const optCount = list.querySelectorAll(".option-row").length + 1;

    // FIX: Moved the radio button and delete button completely to the right side
    const optHtml = `
        <div class="flex items-center justify-between gap-3 bg-white px-3 py-2 rounded-lg border border-gray-200 focus-within:border-[#a52a2a] option-row transition">
            <input type="text" class="option-input w-full bg-transparent outline-none text-sm" placeholder="Choice ${optCount}..." value="${text}">
            
            <div class="flex items-center gap-3 border-l border-gray-100 pl-3 shrink-0">
                <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-green-600 transition">
                    <input type="radio" name="correct-${qId}" title="Mark as Correct Answer" class="is-correct-radio cursor-pointer text-green-600 focus:ring-green-600" ${isCorrect ? "checked" : ""}>
                    <span class="text-[10px] font-bold uppercase">Correct</span>
                </label>
                <button type="button" onclick="window.removeOption(this, '${qId}')" title="Remove Choice" class="text-gray-300 hover:text-red-500 transition h-6 w-6 flex items-center justify-center">
                    <i class="fas fa-times-circle text-base"></i>
                </button>
            </div>
        </div>`;

    list.insertAdjacentHTML("beforeend", optHtml);
    window.handleAutosaveTrigger();
};

window.removeOption = function (btnElement, qId) {
    const list = document.querySelector(`#${qId} .options-list`);

    if (list.querySelectorAll(".option-row").length <= 2) {
        // REPLACED ALERT WITH WARNING MODAL
        window.showModal('warning', 'Action Prevented', 'A question must have at least two choices.');
        return;
    }

    btnElement.closest(".option-row").remove();
    window.handleAutosaveTrigger();
};

// --- DATA COLLECTION & SYNC ---

window.getPayload = function (status) {
    const categories = [];
    document.querySelectorAll(".category-block").forEach((cat) => {
        const questions = [];
        cat.querySelectorAll(".question-block").forEach((q) => {
            const options = [];
            q.querySelectorAll(".option-row").forEach((opt) => {
                options.push({
                    text: opt.querySelector(".option-input").value,
                    is_correct: opt.querySelector(".is-correct-radio").checked
                        ? 1
                        : 0,
                });
            });
            questions.push({
                text: q.querySelector(".q-text").value,
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

// FIX: Also expose collectCategoriesData so autosave works properly
window.collectCategoriesData = function () {
    return window.getPayload("draft").categories;
};

window.saveCompleteExam = async function (btn, status) {
    clearTimeout(autosaveTimer);
    lastPayload = ""; // Bypass beforeunload warning

    const wrapper = document.getElementById("assessment-wrapper");
    const payload = window.getPayload(status);

    if (!payload.title || !payload.year_level) {
        // REPLACED ALERT WITH MODAL
        return window.showModal('warning', 'Missing Information', 'Please fill out the Assessment Title and Year / Grade Level before saving.');
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
            
            // REPLACED ALERT WITH SUCCESS MODAL + CALLBACK
            const title = status === "published" ? "Test Published!" : "Draft Saved!";
            const msg = status === "published" ? "Your test is live and ready for students." : "Your progress has been safely stored.";
            
            window.showModal('success', title, msg, () => {
                // This redirect logic runs ONLY AFTER they click "OK" on the modal
                if (typeof loadPartial === 'function') {
                    loadPartial(wrapper.dataset.redirectUrl);
                } else {
                    window.location.href = wrapper.dataset.redirectUrl;
                }
            });
            
        } else {
            throw new Error(result.message || "Failed to save");
        }
    } catch (e) {
        // REPLACED ALERT WITH ERROR MODAL
        window.showModal('error', 'Save Failed', e.message);
        window.resetBtn(btn, originalText);
    }
};

window.renderExistingCategory = function (catData) {
    window.addCategory();
    const latestCat = document.querySelector(".category-block:last-child");
    latestCat.querySelector(".c-title").value = catData.title || "";
    latestCat.querySelector(".c-time").value = catData.time_limit || "";
    latestCat.querySelector(".category-display-title").innerText =
        catData.title || "New Section";

    const qContainer = latestCat.querySelector('[id^="q-container-"]');
    qContainer.innerHTML = ""; // Clear the default blank question

    // FIX: Using defensive checks for both keys just in case Laravel transforms 'text' back to 'question_text'
    if (catData.questions && catData.questions.length > 0) {
        catData.questions.forEach((q) => {
            window.addQuestion(qContainer.id.split("-").pop());
            const latestQ = qContainer.querySelector(
                ".question-block:last-child",
            );

            // Fixed key binding here
            latestQ.querySelector(".q-text").value =
                q.text || q.question_text || "";
            latestQ.querySelector(".options-list").innerHTML = ""; // Clear default options

            if (q.options && q.options.length > 0) {
                // Render unlimited saved options
                q.options.forEach((opt) => {
                    // Fixed key binding here
                    window.addOptionToQuestion(
                        latestQ.id,
                        opt.is_correct == 1 || opt.is_correct === true,
                        opt.text || opt.option_text || "",
                    );
                });
            }
        });
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
    if (!confirm("Are you sure you want to discard this entire assessment?"))
        return;
    const wrapper = document.getElementById("assessment-wrapper");
    try {
        const response = await fetch(wrapper.dataset.deleteUrl, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": wrapper.dataset.csrf,
                Accept: "application/json",
            },
        });
        if (response.ok) loadPartial(wrapper.dataset.redirectUrl);
    } catch (e) {
        alert("Delete failed");
    }
};

// for back button
window.addEventListener("beforeunload", (event) => {
    // Only trigger if there is unsaved work (lastPayload isn't empty)
    if (lastPayload !== "") {
        event.preventDefault();
        event.returnValue = ""; // Modern browsers require this to show a generic alert
    }
});

window.discardChangesAndExit = function(btn) {
    // 1. Trigger the confirmation modal FIRST
    window.showModal('confirm', 'Discard Unsaved Changes?', 'Are you sure you want to discard your unsaved work and exit? This cannot be undone.', async () => {
        
        // --- EVERYTHING BELOW THIS LINE ONLY HAPPENS IF THEY CLICK "YES" ---
        
        const wrapper = document.getElementById("assessment-wrapper");
        if (!wrapper) return;

        // Hide the initial back-button modal now that they've confirmed
        document.getElementById('back-modal').classList.add('hidden');

        // Stop autosave and BYPASS the "Leave site?" warning
        clearTimeout(autosaveTimer);
        lastPayload = ""; 

        // Show loading state on the button just in case the redirect takes a second
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Discarding...';
        btn.disabled = true;

        // Wipe the browser's local memory
        localStorage.removeItem("assessment_draft_" + wrapper.dataset.assessmentId);

        try {
            // Tell the server to cleanly wipe the draft_json column
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

        // Safely redirect back to the index dashboard
        if (typeof loadPartial === 'function') {
            loadPartial(wrapper.dataset.redirectUrl);
        } else {
            window.location.href = wrapper.dataset.redirectUrl;
        }
        
    }); // <-- End of the confirmation callback
};

// Add this anywhere inside window.initBuilder:
const backModal = document.getElementById("back-modal");
if (backModal) {
    backModal.addEventListener("click", function (e) {
        // If they clicked the dark backdrop directly (and not the white modal card)
        if (e.target.classList.contains("backdrop-blur-sm")) {
            this.classList.add("hidden");
        }
    });
}


// UTILITIES

window.showModal = function(type, title, message, callback = null) {
    const modal = document.getElementById('status-modal');
    if (!modal) {
        alert(`${title}\n${message}`);
        if (callback && type !== 'confirm') callback();
        return;
    }

    const iconContainer = document.getElementById('status-modal-icon');
    const titleEl = document.getElementById('status-modal-title');
    const msgEl = document.getElementById('status-modal-message');
    const btn = document.getElementById('status-modal-btn');
    const cancelBtn = document.getElementById('status-modal-cancel-btn');

    // Set Text
    titleEl.innerText = title;
    msgEl.innerText = message;

    // Reset styles
    iconContainer.className = 'h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
    btn.className = 'w-full py-3.5 text-white font-bold rounded-xl transition active:scale-95 shadow-md';
    cancelBtn.classList.add('hidden'); // Hidden by default
    btn.innerText = 'OK';

    // Remove old listeners
    cancelBtn.onclick = null;
    btn.onclick = null;

    if (type === 'success') {
        iconContainer.classList.add('bg-green-50', 'text-green-500');
        iconContainer.innerHTML = '<i class="fas fa-check-circle"></i>';
        btn.classList.add('bg-green-600', 'hover:bg-green-700', 'shadow-green-600/20');
    } else if (type === 'error') {
        iconContainer.classList.add('bg-red-50', 'text-red-500');
        iconContainer.innerHTML = '<i class="fas fa-times-circle"></i>';
        btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-600/20');
    } else if (type === 'warning') {
        iconContainer.classList.add('bg-amber-50', 'text-amber-500');
        iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        btn.classList.add('bg-amber-500', 'hover:bg-amber-600', 'shadow-amber-500/20');
    } else if (type === 'confirm') {
        // NEW CONFIRMATION STYLE
        iconContainer.classList.add('bg-red-50', 'text-red-500');
        iconContainer.innerHTML = '<i class="fas fa-trash-alt"></i>';
        btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-600/20');
        btn.innerText = 'Yes, Discard';
        
        cancelBtn.classList.remove('hidden'); // Show cancel button
        cancelBtn.onclick = function() {
            modal.classList.add('hidden'); // Just close if they cancel
        };
    }

    // Show modal
    modal.classList.remove('hidden');

    // Handle primary button click
    btn.onclick = function() {
        modal.classList.add('hidden');
        if (callback && typeof callback === 'function') {
            callback(); // Run the action if they click "OK" or "Yes"
        }
    };
};