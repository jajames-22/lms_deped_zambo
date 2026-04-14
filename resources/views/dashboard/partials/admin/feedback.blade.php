<div class="space-y-6 w-full max-w-6xl mx-auto pb-12 animate-float-in">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Help & Support Desk</h1>
            <p class="text-gray-500 text-sm mt-1">Review user reports, feature requests, and system bugs.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div
                class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-inbox"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pending Tickets</p>
                <h4 class="text-2xl font-black text-gray-900 leading-none mt-1">{{ $pendingCount }}</h4>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-bug"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bug Reports</p>
                <h4 class="text-2xl font-black text-gray-900 leading-none mt-1">{{ $bugCount }}</h4>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div
                class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Resolved</p>
                <h4 class="text-2xl font-black text-gray-900 leading-none mt-1">{{ $resolvedCount }}</h4>
            </div>
        </div>
    </div>

    <div
        class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[600px] relative">

        <div id="admin-feedback-list" class="flex-1 flex flex-col">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50 shrink-0">
                
                <div class="flex bg-white rounded-lg p-1 border border-gray-200 shadow-sm w-full sm:w-auto overflow-x-auto" id="feedback-filters">
                    <button class="filter-btn active whitespace-nowrap px-4 py-1.5 rounded-md text-xs font-bold bg-blue-50 text-blue-600 transition" data-filter="all">All</button>
                    <button class="filter-btn whitespace-nowrap px-4 py-1.5 rounded-md text-xs font-bold text-gray-500 hover:bg-gray-50 transition" data-filter="action_needed"><i class="fas fa-exclamation-circle text-red-500 mr-1"></i> Action Needed</button>
                    <button class="filter-btn whitespace-nowrap px-4 py-1.5 rounded-md text-xs font-bold text-gray-500 hover:bg-gray-50 transition" data-filter="in_progress"><i class="fas fa-spinner fa-spin text-amber-500 mr-1"></i> In Progress</button>
                    <button class="filter-btn whitespace-nowrap px-4 py-1.5 rounded-md text-xs font-bold text-gray-500 hover:bg-gray-50 transition" data-filter="completed"><i class="fas fa-check text-green-500 mr-1"></i> Completed</button>
                </div>

                <div class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="feedbackSearchInput" placeholder="Search tickets..."
                        class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 focus:border-[#a52a2a] focus:ring-1 focus:ring-[#a52a2a] rounded-xl outline-none transition-all text-sm text-gray-700 shadow-sm">
                </div>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse" id="feedbacksTable">
                    <thead class="bg-white text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none"
                                title="Sort by User">
                                User <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none"
                                title="Sort by Subject">
                                Subject <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none"
                                title="Sort by Category">
                                Category <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none"
                                title="Sort by Date">
                                Date <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none"
                                title="Sort by Status">
                                Status <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($feedbacks as $fb)
                            @php
                                // Map the database status to the UI filter groups
                                $filterGroup = 'completed';
                                if (in_array($fb->status, ['open', 'waiting_on_support']))
                                    $filterGroup = 'action_needed';
                                if (in_array($fb->status, ['in_progress', 'waiting_on_user']))
                                    $filterGroup = 'in_progress';
                            @endphp

                            <tr class="hover:bg-gray-50/50 transition group cursor-pointer feedback-row"
                                data-status="{{ $filterGroup }}" onclick="openAdminFeedbackDetail({{ $fb->id }})">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-600 text-xs shrink-0">
                                            {{ $fb->sender ? substr($fb->sender->first_name, 0, 1) : 'U' }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ $fb->sender ? $fb->sender->first_name . ' ' . $fb->sender->last_name : 'Unknown' }}</p>
                                            <p class="text-[10px] text-gray-500 uppercase font-black">{{ $fb->sender ? $fb->sender->role : '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-gray-800 line-clamp-1 max-w-[200px]">{{ $fb->subject }}</p>
                                    @if($fb->media_url)
                                        <span class="text-[10px] text-blue-500 font-bold mt-1 inline-flex items-center gap-1"><i class="fas fa-image"></i> Attachment</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase rounded-md border border-gray-200">
                                        {{ str_replace('_', ' ', $fb->category) }}
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 text-xs font-medium text-gray-500" data-sort="{{ $fb->created_at->timestamp }}">
                                    {{ $fb->created_at->format('M d, Y') }}<br>
                                    <span class="text-[10px] text-gray-400">{{ $fb->created_at->format('h:i A') }}</span>
                                </td>
                                
                                <td class="px-6 py-4">
                                    @if($fb->status === 'open')
                                        <span class="px-2.5 py-1 bg-purple-50 text-purple-600 border border-purple-200 text-[10px] font-black uppercase rounded-md flex items-center w-fit gap-1.5"><i class="fas fa-asterisk text-[8px]"></i> New</span>
                                    @elseif($fb->status === 'waiting_on_support')
                                        <span class="px-2.5 py-1 bg-red-50 text-red-600 border border-red-200 text-[10px] font-black uppercase rounded-md flex items-center w-fit gap-1.5"><i class="fas fa-exclamation-circle text-[10px]"></i> User Replied</span>
                                    @elseif($fb->status === 'in_progress')
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-black uppercase rounded-md flex items-center w-fit gap-1.5"><i class="fas fa-spinner fa-spin text-[10px]"></i> In Progress</span>
                                    @elseif($fb->status === 'waiting_on_user')
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 text-[10px] font-black uppercase rounded-md flex items-center w-fit gap-1.5"><i class="fas fa-hourglass-half text-[10px]"></i> Awaiting User</span>
                                    @elseif($fb->status === 'resolved')
                                        <span class="px-2.5 py-1 bg-green-50 text-green-600 border border-green-200 text-[10px] font-black uppercase rounded-md flex items-center w-fit gap-1.5"><i class="fas fa-check text-[10px]"></i> Resolved</span>
                                    @elseif($fb->status === 'closed')
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-300 text-[10px] font-black uppercase rounded-md flex items-center w-fit gap-1.5"><i class="fas fa-lock text-[10px]"></i> Closed</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 group-hover:text-blue-600 group-hover:border-blue-200 group-hover:bg-blue-50 transition flex items-center justify-center ml-auto shadow-sm">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        <tr id="emptyStateRow" style="display: none;">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-sm">
                                    <i class="fas fa-check-double text-2xl text-gray-300"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Inbox Zero!</h3>
                                <p class="text-gray-500 text-sm max-w-sm mx-auto">There are no tickets matching your current search or filter.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="pagination-wrapper" class="hidden flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50 shrink-0">
                <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                    Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info" class="font-bold text-gray-900">0</span> results
                </div>
                <div class="flex items-center gap-1" id="pagination-controls"></div>
            </div>
        </div>

        <div id="admin-feedback-detail" class="hidden flex-1 flex flex-col h-full absolute inset-0 bg-white z-10">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4">
                    <button onclick="closeAdminFeedbackDetail()" class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-[#a52a2a] hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center shadow-sm">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div>
                        <h2 id="ad-detail-subject" class="text-xl font-black text-gray-900 leading-tight">Loading...</h2>
                        <p id="ad-detail-meta" class="text-xs font-medium text-gray-500 mt-1">Ticket #000 • User Name</p>
                    </div>
                </div>
                <div id="ad-detail-statusBadge"></div>
            </div>

            <div class="flex-1 overflow-y-auto sidebar-scroll p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">

                    <div class="lg:col-span-2 space-y-6">
                        <div>
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Original Message</h4>
                            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
                                <p id="ad-detail-message" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed"></p>

                                <div id="ad-detail-media" class="mt-4 hidden pt-4 border-t border-gray-200">
                                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Attached Screenshot</h4>
                                    <a id="ad-detail-media-link" href="#" target="_blank" class="block overflow-hidden rounded-xl border border-gray-200 hover:border-blue-400 transition relative group">
                                        <img id="ad-detail-img" src="" class="w-full max-h-64 object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-sm">
                                            <i class="fas fa-external-link-alt mr-2"></i> View Full Image
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div id="ad-resolved-block" class="hidden"></div>

                        <form id="admin-reply-form" onsubmit="submitAdminReply(event)" class="hidden space-y-3 lg:pb-5">
                            @csrf
                            <input type="hidden" id="reply-feedback-id">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-reply text-blue-500"></i> Write a Response
                            </h4>
                            <textarea id="reply-message" required rows="4" placeholder="Type your response here..."
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm resize-none shadow-sm"></textarea>

                            <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-2">
                                <select id="reply-status" required class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 outline-none focus:border-blue-500 transition-all">
                                    <option value="in_progress">Mark as In Progress</option>
                                    <option value="waiting_on_user">Ask Question / Wait for User</option>
                                    <option value="resolved">Mark as Resolved</option>
                                </select>
                                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-2 text-sm w-full sm:w-auto">
                                    <i class="fas fa-paper-plane"></i> Send Update
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-5 sticky top-0">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Reporter Details</h4>

                            <div class="flex items-center gap-3 mb-5 pb-5 border-b border-gray-100">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-lg font-black text-gray-600 shrink-0" id="ad-user-initial">U</div>
                                <div>
                                    <p id="ad-user-name" class="font-bold text-gray-900">User Name</p>
                                    <p id="ad-user-role" class="text-xs text-[#a52a2a] uppercase font-black tracking-wider">Role</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Email Address</p>
                                    <p id="ad-user-email" class="text-sm font-medium text-gray-800 break-all"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Category</p>
                                    <p id="ad-category" class="text-sm font-medium text-gray-800 capitalize"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Date Submitted</p>
                                    <p id="ad-date" class="text-sm font-medium text-gray-800"></p>
                                </div>
                            </div>
                        </div>
                        <div class="h-5"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.allFeedbacksData = @json($feedbacks);

    var currentPage = 1;
    var pageSize = 10;
    var allFeedbackRows = [];
    var currentFilteredRows = [];
    var currentStatusFilter = 'all';

    setTimeout(function () {
        allFeedbackRows = Array.from(document.querySelectorAll('.feedback-row'));
        currentFilteredRows = [...allFeedbackRows];
        applyPagination();
        setupFiltersAndSearch();
        setupSorting();

        const savedUrl = sessionStorage.getItem('lastActiveTab') || '';
        if (savedUrl.includes('?ticket=')) {
            const ticketId = parseInt(new URLSearchParams(savedUrl.split('?')[1]).get('ticket'));
            if (ticketId) openAdminFeedbackDetail(ticketId);
        }
    }, 50);

    function applyPagination() {
        var tbody = document.querySelector('#feedbacksTable tbody');
        var emptyState = document.getElementById('emptyStateRow');
        var paginationWrapper = document.getElementById('pagination-wrapper');

        allFeedbackRows.forEach(row => row.style.display = 'none');

        if (currentFilteredRows.length === 0) {
            if (emptyState) emptyState.style.display = '';
            paginationWrapper.classList.add('hidden');
            paginationWrapper.classList.remove('flex');
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        paginationWrapper.classList.remove('hidden');
        paginationWrapper.classList.add('flex');

        var totalPages = Math.ceil(currentFilteredRows.length / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        var startIdx = (currentPage - 1) * pageSize;
        var endIdx = Math.min(startIdx + pageSize, currentFilteredRows.length);

        for (var i = startIdx; i < endIdx; i++) {
            currentFilteredRows[i].style.display = '';
            tbody.appendChild(currentFilteredRows[i]);
        }

        document.getElementById('page-start-info').innerText = startIdx + 1;
        document.getElementById('page-end-info').innerText = endIdx;
        document.getElementById('page-total-info').innerText = currentFilteredRows.length;

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        var controls = document.getElementById('pagination-controls');
        controls.innerHTML = '';

        var createBtn = function (text, page, disabled, active) {
            var btn = document.createElement('button');
            btn.innerHTML = text;
            btn.disabled = disabled;
            btn.className = `px-3 py-1 min-w-[32px] rounded-lg text-sm font-bold transition-all border ${active
                ? 'bg-[#a52a2a] text-white border-[#a52a2a] shadow-sm'
                : disabled
                    ? 'bg-transparent text-gray-300 border-transparent cursor-not-allowed'
                    : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] hover:border-[#a52a2a]/30 shadow-sm'
                }`;

            if (!disabled && !active) {
                btn.onclick = function () {
                    currentPage = page;
                    applyPagination();
                };
            }
            return btn;
        };

        controls.appendChild(createBtn('<i class="fas fa-chevron-left text-xs"></i>', currentPage - 1, currentPage === 1, false));

        var startP = Math.max(1, currentPage - 1);
        var endP = Math.min(totalPages, currentPage + 1);

        if (currentPage === 1) endP = Math.min(3, totalPages);
        if (currentPage === totalPages) startP = Math.max(1, totalPages - 2);

        if (startP > 1) {
            controls.appendChild(createBtn(1, 1, false, currentPage === 1));
            if (startP > 2) controls.appendChild(createBtn('...', null, true, false));
        }

        for (var i = startP; i <= endP; i++) {
            controls.appendChild(createBtn(i, i, false, i === currentPage));
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) controls.appendChild(createBtn('...', null, true, false));
            controls.appendChild(createBtn(totalPages, totalPages, false, currentPage === totalPages));
        }

        controls.appendChild(createBtn('<i class="fas fa-chevron-right text-xs"></i>', currentPage + 1, currentPage === totalPages, false));
    }

    function setupFiltersAndSearch() {
        var searchInput = document.getElementById('feedbackSearchInput');
        var filterBtns = document.querySelectorAll('.filter-btn');

        function executeFilter() {
            var currentSearchBox = document.getElementById('feedbackSearchInput');
            var filterText = (currentSearchBox ? currentSearchBox.value : '').toLowerCase();

            currentFilteredRows = allFeedbackRows.filter(function (row) {
                var rowText = row.textContent.toLowerCase();
                var matchesSearch = rowText.includes(filterText);
                var matchesStatus = currentStatusFilter === 'all' || row.dataset.status === currentStatusFilter;
                return matchesSearch && matchesStatus;
            });

            currentPage = 1;
            applyPagination();
        }

        if (searchInput) {
            var newSearchInput = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearchInput, searchInput);
            newSearchInput.addEventListener('input', executeFilter);
        }

        filterBtns.forEach(btn => {
            var newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener('click', function () {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-blue-50', 'text-blue-600', 'active');
                    b.classList.add('text-gray-500', 'hover:bg-gray-50');
                });
                this.classList.remove('text-gray-500', 'hover:bg-gray-50');
                this.classList.add('bg-blue-50', 'text-blue-600', 'active');

                currentStatusFilter = this.dataset.filter;
                executeFilter();
            });
        });
    }

    function setupSorting() {
        var sortableHeaders = document.querySelectorAll('.sortable-col');
        sortableHeaders.forEach(function (header) {
            var newHeader = header.cloneNode(true);
            header.parentNode.replaceChild(newHeader, header);

            newHeader.addEventListener('click', function () {
                var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
                var isAsc = newHeader.classList.contains('asc');

                document.querySelectorAll('.sortable-col i').forEach(function (icon) {
                    icon.className = 'fas fa-sort ml-1 text-gray-300';
                });
                document.querySelectorAll('.sortable-col').forEach(function (h) {
                    h.classList.remove('asc', 'desc');
                });

                var multiplier = 1;
                if (isAsc) {
                    newHeader.classList.add('desc');
                    newHeader.querySelector('i').className = 'fas fa-sort-down ml-1 text-[#a52a2a]';
                    multiplier = -1;
                } else {
                    newHeader.classList.add('asc');
                    newHeader.querySelector('i').className = 'fas fa-sort-up ml-1 text-[#a52a2a]';
                    multiplier = 1;
                }

                currentFilteredRows.sort(function (a, b) {
                    var aVal = a.children[colIndex].dataset.sort ? parseInt(a.children[colIndex].dataset.sort) : a.children[colIndex].textContent.trim().toLowerCase();
                    var bVal = b.children[colIndex].dataset.sort ? parseInt(b.children[colIndex].dataset.sort) : b.children[colIndex].textContent.trim().toLowerCase();

                    if (aVal < bVal) return -1 * multiplier;
                    if (aVal > bVal) return 1 * multiplier;
                    return 0;
                });

                currentPage = 1;
                applyPagination();
            });
        });
    }

    function openAdminFeedbackDetail(id) {
        const feedback = window.allFeedbacksData.find(fb => fb.id === id);
        if (!feedback) return;

        document.getElementById('admin-feedback-list').classList.add('hidden');
        document.getElementById('admin-feedback-detail').classList.remove('hidden');

        document.getElementById('ad-detail-subject').innerText = feedback.subject;
        document.getElementById('ad-detail-meta').innerText = `Ticket #${feedback.id.toString().padStart(4, '0')} • ${feedback.sender ? feedback.sender.first_name : 'Unknown'}`;

        // Populate Right-Side User Detail Panel
        if (feedback.sender) {
            document.getElementById('ad-user-initial').innerText = feedback.sender.first_name ? feedback.sender.first_name.charAt(0).toUpperCase() : 'U';
            document.getElementById('ad-user-name').innerText = `${feedback.sender.first_name} ${feedback.sender.last_name}`;
            document.getElementById('ad-user-role').innerText = feedback.sender.role;
            document.getElementById('ad-user-email').innerText = feedback.sender.email;
        }
        document.getElementById('ad-category').innerText = feedback.category.replace(/_/g, ' ');
        document.getElementById('ad-date').innerText = new Date(feedback.created_at).toLocaleString();

        // Check Media URL
        const mediaBox = document.getElementById('ad-detail-media');
        if (feedback.media_url) {
            mediaBox.classList.remove('hidden');
            document.getElementById('ad-detail-img').src = '/storage/' + feedback.media_url;
            document.getElementById('ad-detail-media-link').href = '/storage/' + feedback.media_url;
        } else {
            mediaBox.classList.add('hidden');
        }

        // Status Colors
        const badgeContainer = document.getElementById('ad-detail-statusBadge');
        let statusHtml = '';
        if (feedback.status === 'open') statusHtml = '<span class="px-3 py-1.5 bg-purple-50 text-purple-600 border border-purple-200 text-xs font-black uppercase rounded-lg">New Ticket</span>';
        else if (feedback.status === 'waiting_on_support') statusHtml = '<span class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 text-xs font-black uppercase rounded-lg">User Replied</span>';
        else if (feedback.status === 'in_progress') statusHtml = '<span class="px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 text-xs font-black uppercase rounded-lg"><i class="fas fa-spinner fa-spin mr-1"></i> In Progress</span>';
        else if (feedback.status === 'waiting_on_user') statusHtml = '<span class="px-3 py-1.5 bg-amber-50 text-amber-600 border border-amber-200 text-xs font-black uppercase rounded-lg">Waiting on User</span>';
        else if (feedback.status === 'resolved') statusHtml = '<span class="px-3 py-1.5 bg-green-50 text-green-600 border border-green-200 text-xs font-black uppercase rounded-lg">Resolved</span>';
        else statusHtml = '<span class="px-3 py-1.5 bg-gray-100 text-gray-600 border border-gray-300 text-xs font-black uppercase rounded-lg"><i class="fas fa-lock"></i> Closed</span>';
        badgeContainer.innerHTML = statusHtml;

        document.getElementById('ad-detail-message').innerText = feedback.message;

        // Render Conversation Thread
        const resolvedBlock = document.getElementById('ad-resolved-block');
        if (feedback.messages && feedback.messages.length > 0) {
            resolvedBlock.classList.remove('hidden');
            let threadHtml = '<h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4"><i class="fas fa-comments text-blue-500"></i> Conversation History</h4>';

            feedback.messages.forEach(msg => {
                const isAdmin = msg.sender && msg.sender.role === 'admin';
                const name = msg.sender ? `${msg.sender.first_name} ${msg.sender.last_name}` : 'Unknown';
                const date = new Date(msg.created_at).toLocaleString();

                if (isAdmin) {
                    threadHtml += `<div class="mb-4 bg-blue-50/50 border border-blue-100 rounded-2xl p-4 ml-8 relative">
                        <div class="flex justify-between items-center mb-2"><span class="text-xs font-black text-blue-700 uppercase"><i class="fas fa-headset"></i> Support (${name})</span><span class="text-[10px] text-gray-400">${date}</span></div>
                        <p class="text-sm text-gray-800 whitespace-pre-wrap">${msg.message}</p>
                    </div>`;
                } else {
                    threadHtml += `<div class="mb-4 bg-gray-50 border border-gray-200 rounded-2xl p-4 mr-8 relative">
                        <div class="flex justify-between items-center mb-2"><span class="text-xs font-black text-gray-600 uppercase"><i class="fas fa-user"></i> ${name}</span><span class="text-[10px] text-gray-400">${date}</span></div>
                        <p class="text-sm text-gray-800 whitespace-pre-wrap">${msg.message}</p>
                    </div>`;
                }
            });
            resolvedBlock.innerHTML = threadHtml;
        } else {
            resolvedBlock.classList.add('hidden');
            resolvedBlock.innerHTML = '';
        }

        // Show/Hide Reply Form based on Hard Close
        const replyForm = document.getElementById('admin-reply-form');
        if (feedback.status === 'closed') {
            replyForm.classList.add('hidden');
        } else {
            replyForm.classList.remove('hidden');
            document.getElementById('reply-feedback-id').value = feedback.id;
            document.getElementById('reply-message').value = '';
            document.getElementById('reply-status').value = 'in_progress'; // Set a default
        }
    }

    function closeAdminFeedbackDetail() {
        document.getElementById('admin-feedback-detail').classList.add('hidden');
        document.getElementById('admin-feedback-list').classList.remove('hidden');
    }

    function submitAdminReply(e) {
        e.preventDefault();

        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const feedbackId = document.getElementById('reply-feedback-id').value;
        const message = document.getElementById('reply-message').value;
        const statusValue = document.getElementById('reply-status').value; // GRAB THE SELECTED STATUS

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        btn.disabled = true;

        fetch(`{{ url('/dashboard/feedback') }}/${feedbackId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                admin_reply: message,
                status: statusValue // PASS IT TO THE CONTROLLER
            })
        })
            .then(async response => {
                if (response.ok) {
                    alert(`Update sent successfully!`);
                    loadPartial('{{ route('dashboard.feedback') }}', document.getElementById('nav-feedback-btn'));
                } else {
                    const data = await response.json();
                    alert(data.message || 'Error updating ticket.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('A network error occurred.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
    }
</script>