<div class="space-y-6 w-full max-w-6xl mx-auto pb-12 animate-float-in">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Help & Support Desk</h1>
            <p class="text-gray-500 text-sm mt-1">Review user reports, feature requests, and system bugs.</p>
        </div>
        
        {{-- NEW: Broadcast Button --}}
        <button onclick="openBroadcastModal()" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-2 text-sm w-fit">
            <i class="fas fa-bullhorn"></i> System Broadcast
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
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
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Resolved</p>
                <h4 class="text-2xl font-black text-gray-900 leading-none mt-1">{{ $resolvedCount }}</h4>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[600px] relative">

        <div id="admin-feedback-list" class="flex-1 flex flex-col">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50 shrink-0">
                <div class="flex items-center space-x-1 bg-gray-200/50 p-1 rounded-xl w-full md:w-fit overflow-x-auto no-scrollbar shrink-0">
                    @php 
                        $actionNeededCount = collect($feedbacks)->filter(function($fb) {
                            return in_array($fb->status, ['open', 'waiting_on_support']);
                        })->count();

                        $pendingCount = collect($feedbacks)->filter(function($fb) {
                            return in_array($fb->status, ['in_progress', 'waiting_on_user']);
                        })->count();
                    @endphp

                    <button onclick="FeedbackManager.filter('all', this)" 
                        class="feedback-tab px-6 py-2 text-sm font-bold rounded-lg transition-all bg-white text-[#a52a2a] shadow-sm whitespace-nowrap">All</button>
                    
                    <button onclick="FeedbackManager.filter('action_needed', this)" 
                        class="feedback-tab flex items-center gap-2 px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">
                        <span>Action Needed</span>
                        @if($actionNeededCount > 0)
                            <span class="flex items-center justify-center min-w-[20px] h-[20px] px-1.5 bg-red-500 text-white text-[11px] font-bold rounded-full shadow-sm animate-pulse">
                                {{ $actionNeededCount }}
                            </span>
                        @endif
                    </button>
                    
                    <button onclick="FeedbackManager.filter('pending', this)" 
                        class="feedback-tab flex items-center gap-2 px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">
                        <span>Pending Users</span>
                        @if($pendingCount > 0)
                            <span class="flex items-center justify-center min-w-[20px] h-[20px] px-1.5 bg-amber-500 text-white text-[11px] font-bold rounded-full shadow-sm">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </button>
                    
                    <button onclick="FeedbackManager.filter('resolved', this)" 
                        class="feedback-tab px-6 py-2 text-sm font-bold rounded-lg transition-all text-gray-500 hover:text-gray-700 whitespace-nowrap">Resolved & Closed</button>
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
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none" title="Sort by User">
                                User <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none" title="Sort by Subject">
                                Subject <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none" title="Sort by Category">
                                Category <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none" title="Sort by Date">
                                Date <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black cursor-pointer hover:bg-gray-50 transition sortable-col select-none" title="Sort by Status">
                                Status <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-6 py-4 font-black text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($feedbacks as $fb)
                            @php
                                $filterGroup = 'completed';
                                if (in_array($fb->status, ['open', 'waiting_on_support'])) $filterGroup = 'action_needed';
                                if (in_array($fb->status, ['in_progress', 'waiting_on_user'])) $filterGroup = 'in_progress';
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

{{-- NEW: Broadcast Notification Modal --}}
<div id="broadcastModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeBroadcastModal()"></div>
    <div id="broadcastBox" class="bg-white rounded-3xl w-full max-w-lg shadow-2xl relative z-10 transform scale-95 opacity-0 transition-all duration-300 overflow-hidden flex flex-col">
        
        <div class="px-8 pt-8 pb-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
            <div>
                <h2 class="text-xl font-black text-gray-900">System Broadcast</h2>
                <p class="text-gray-500 text-sm mt-1">Send a notification to all registered users.</p>
            </div>
            <button onclick="closeBroadcastModal()" class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:bg-red-50 transition flex items-center justify-center shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="broadcastForm" onsubmit="submitBroadcast(event)" class="p-8 space-y-5">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Notification Type</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="broadcastType" value="info" class="peer sr-only" checked>
                        <div class="p-3 text-center rounded-xl border border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                            <i class="fas fa-info-circle text-blue-500 mb-1"></i>
                            <p class="text-xs font-bold text-gray-700">Info</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="broadcastType" value="warning" class="peer sr-only">
                        <div class="p-3 text-center rounded-xl border border-gray-200 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                            <i class="fas fa-exclamation-triangle text-amber-500 mb-1"></i>
                            <p class="text-xs font-bold text-gray-700">Warning</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="broadcastType" value="success" class="peer sr-only">
                        <div class="p-3 text-center rounded-xl border border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                            <i class="fas fa-check-circle text-green-500 mb-1"></i>
                            <p class="text-xs font-bold text-gray-700">Success</p>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Subject / Title</label>
                <input type="text" id="broadcastSubject" required placeholder="e.g., Scheduled System Maintenance"
                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm text-gray-900 shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Message</label>
                <textarea id="broadcastMessage" required rows="4" placeholder="Type the announcement here..."
                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm resize-none shadow-sm"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <button type="button" onclick="closeBroadcastModal()" class="px-6 py-3 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition">Cancel</button>
                <button type="submit" id="broadcastSubmitBtn" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Send to All
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.allFeedbacksData = @json($feedbacks);

    var FeedbackManager = {
        currentStatus: 'all',
        currentPage: 1,
        pageSize: 10,

        getActiveEl: function(selector) {
            const els = document.querySelectorAll(selector);
            return els.length ? els[els.length - 1] : null;
        },

        init: function() {
            const activeTable = this.getActiveEl('#feedbacksTable');
            if (!activeTable) return;
            
            const searchInput = this.getActiveEl('#feedbackSearchInput');
            if (searchInput && !searchInput.dataset.initialized) {
                searchInput.dataset.initialized = 'true';
                searchInput.addEventListener('input', () => this.applyFilters());
            }

            this.setupSorting(activeTable);
            this.applyFilters();

            const savedUrl = sessionStorage.getItem('lastActiveTab') || '';
            if (savedUrl.includes('?ticket=')) {
                const ticketId = parseInt(new URLSearchParams(savedUrl.split('?')[1]).get('ticket'));
                if (ticketId) openAdminFeedbackDetail(ticketId);
            }
        },

        filter: function(status, btnElement) {
            this.currentStatus = status;

            const activeContainer = this.getActiveEl('#admin-feedback-list');
            if(activeContainer) {
                activeContainer.querySelectorAll('.feedback-tab').forEach(tab => {
                    tab.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm');
                    tab.classList.add('text-gray-500', 'hover:text-gray-700');
                });
            }

            if(btnElement) {
                btnElement.classList.remove('text-gray-500', 'hover:text-gray-700');
                btnElement.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm');
            }

            this.currentPage = 1; 
            this.applyFilters();
        },

        applyFilters: function() {
            const activeTable = this.getActiveEl('#feedbacksTable');
            if (!activeTable) return;

            const searchInput = this.getActiveEl('#feedbackSearchInput');
            const query = (searchInput?.value || '').toLowerCase();

            const allRows = Array.from(activeTable.querySelectorAll('.feedback-row'));
            
            const filteredRows = allRows.filter(row => {
                const rowText = row.textContent.toLowerCase();
                const matchesSearch = rowText.includes(query);
                const rowStatus = row.dataset.status; 
                
                let matchesStatus = false;
                if (this.currentStatus === 'all') matchesStatus = true;
                else if (this.currentStatus === 'action_needed') matchesStatus = (rowStatus === 'action_needed');
                else if (this.currentStatus === 'pending') matchesStatus = (rowStatus === 'in_progress'); 
                else if (this.currentStatus === 'resolved') matchesStatus = (rowStatus === 'completed'); 

                return matchesSearch && matchesStatus;
            });

            this.applyPagination(activeTable, allRows, filteredRows);
        },

        applyPagination: function(activeTable, allRows, filteredRows) {
            const tbody = activeTable.querySelector('tbody');
            const emptyState = this.getActiveEl('#emptyStateRow');
            const paginationWrapper = this.getActiveEl('#pagination-wrapper');

            allRows.forEach(row => row.style.display = 'none');

            if (filteredRows.length === 0) {
                if (emptyState) emptyState.style.display = '';
                if (paginationWrapper) {
                    paginationWrapper.classList.add('hidden');
                    paginationWrapper.classList.remove('flex');
                }
                return;
            }

            if (emptyState) emptyState.style.display = 'none';
            if (paginationWrapper) {
                paginationWrapper.classList.remove('hidden');
                paginationWrapper.classList.add('flex');
            }

            let totalPages = Math.ceil(filteredRows.length / this.pageSize);
            if (this.currentPage > totalPages) this.currentPage = totalPages;
            if (this.currentPage < 1) this.currentPage = 1;

            const startIdx = (this.currentPage - 1) * this.pageSize;
            const endIdx = Math.min(startIdx + this.pageSize, filteredRows.length);

            for (let i = startIdx; i < endIdx; i++) {
                filteredRows[i].style.display = '';
                tbody.appendChild(filteredRows[i]);
            }

            const startInfo = this.getActiveEl('#page-start-info');
            const endInfo = this.getActiveEl('#page-end-info');
            const totalInfo = this.getActiveEl('#page-total-info');
            
            if (startInfo) startInfo.innerText = startIdx + 1;
            if (endInfo) endInfo.innerText = endIdx;
            if (totalInfo) totalInfo.innerText = filteredRows.length;

            this.renderPaginationControls(totalPages);
        },

        renderPaginationControls: function(totalPages) {
            const controls = this.getActiveEl('#pagination-controls');
            if (!controls) return;
            controls.innerHTML = '';

            const createBtn = (text, page, disabled, active) => {
                const btn = document.createElement('button');
                btn.innerHTML = text;
                btn.disabled = disabled;
                btn.className = `px-3 py-1 min-w-[32px] rounded-lg text-sm font-bold transition-all border ${active
                    ? 'bg-[#a52a2a] text-white border-[#a52a2a] shadow-sm'
                    : disabled
                        ? 'bg-transparent text-gray-300 border-transparent cursor-not-allowed'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] hover:border-[#a52a2a]/30 shadow-sm'
                    }`;

                if (!disabled && !active) {
                    btn.onclick = () => {
                        this.currentPage = page;
                        this.applyFilters();
                    };
                }
                return btn;
            };

            controls.appendChild(createBtn('<i class="fas fa-chevron-left text-xs"></i>', this.currentPage - 1, this.currentPage === 1, false));

            let startP = Math.max(1, this.currentPage - 1);
            let endP = Math.min(totalPages, this.currentPage + 1);

            if (this.currentPage === 1) endP = Math.min(3, totalPages);
            if (this.currentPage === totalPages) startP = Math.max(1, totalPages - 2);

            if (startP > 1) {
                controls.appendChild(createBtn(1, 1, false, this.currentPage === 1));
                if (startP > 2) controls.appendChild(createBtn('...', null, true, false));
            }

            for (let i = startP; i <= endP; i++) {
                controls.appendChild(createBtn(i, i, false, i === this.currentPage));
            }

            if (endP < totalPages) {
                if (endP < totalPages - 1) controls.appendChild(createBtn('...', null, true, false));
                controls.appendChild(createBtn(totalPages, totalPages, false, this.currentPage === totalPages));
            }

            controls.appendChild(createBtn('<i class="fas fa-chevron-right text-xs"></i>', this.currentPage + 1, this.currentPage === totalPages, false));
        },

        setupSorting: function(activeTable) {
            const sortableHeaders = activeTable.querySelectorAll('.sortable-col');
            sortableHeaders.forEach(header => {
                const newHeader = header.cloneNode(true);
                header.parentNode.replaceChild(newHeader, header);

                newHeader.addEventListener('click', () => {
                    const colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
                    const isAsc = newHeader.classList.contains('asc');

                    activeTable.querySelectorAll('.sortable-col i').forEach(icon => {
                        icon.className = 'fas fa-sort ml-1 text-gray-300';
                    });
                    activeTable.querySelectorAll('.sortable-col').forEach(h => {
                        h.classList.remove('asc', 'desc');
                    });

                    let multiplier = 1;
                    if (isAsc) {
                        newHeader.classList.add('desc');
                        newHeader.querySelector('i').className = 'fas fa-sort-down ml-1 text-[#a52a2a]';
                        multiplier = -1;
                    } else {
                        newHeader.classList.add('asc');
                        newHeader.querySelector('i').className = 'fas fa-sort-up ml-1 text-[#a52a2a]';
                        multiplier = 1;
                    }

                    const tbody = activeTable.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('.feedback-row'));
                    
                    rows.sort((a, b) => {
                        const aVal = a.children[colIndex].dataset.sort ? parseInt(a.children[colIndex].dataset.sort) : a.children[colIndex].textContent.trim().toLowerCase();
                        const bVal = b.children[colIndex].dataset.sort ? parseInt(b.children[colIndex].dataset.sort) : b.children[colIndex].textContent.trim().toLowerCase();

                        if (aVal < bVal) return -1 * multiplier;
                        if (aVal > bVal) return 1 * multiplier;
                        return 0;
                    });
                    
                    rows.forEach(row => tbody.appendChild(row));
                    this.applyFilters();
                });
            });
        }
    };

    setTimeout(() => FeedbackManager.init(), 50);

    function getActiveEl(selector) {
        const els = document.querySelectorAll(selector);
        return els.length ? els[els.length - 1] : null;
    }

    function openAdminFeedbackDetail(id) {
        const feedback = window.allFeedbacksData.find(fb => fb.id === id);
        if (!feedback) return;

        getActiveEl('#admin-feedback-list').classList.add('hidden');
        getActiveEl('#admin-feedback-detail').classList.remove('hidden');

        getActiveEl('#ad-detail-subject').innerText = feedback.subject;
        getActiveEl('#ad-detail-meta').innerText = `Ticket #${feedback.id.toString().padStart(4, '0')} • ${feedback.sender ? feedback.sender.first_name : 'Unknown'}`;

        if (feedback.sender) {
            getActiveEl('#ad-user-initial').innerText = feedback.sender.first_name ? feedback.sender.first_name.charAt(0).toUpperCase() : 'U';
            getActiveEl('#ad-user-name').innerText = `${feedback.sender.first_name} ${feedback.sender.last_name}`;
            getActiveEl('#ad-user-role').innerText = feedback.sender.role;
            getActiveEl('#ad-user-email').innerText = feedback.sender.email;
        }
        getActiveEl('#ad-category').innerText = feedback.category.replace(/_/g, ' ');
        getActiveEl('#ad-date').innerText = new Date(feedback.created_at).toLocaleString();

        const mediaBox = getActiveEl('#ad-detail-media');
        if (feedback.media_url) {
            mediaBox.classList.remove('hidden');
            getActiveEl('#ad-detail-img').src = '/storage/' + feedback.media_url;
            getActiveEl('#ad-detail-media-link').href = '/storage/' + feedback.media_url;
        } else {
            mediaBox.classList.add('hidden');
        }

        const badgeContainer = getActiveEl('#ad-detail-statusBadge');
        let statusHtml = '';
        if (feedback.status === 'open') statusHtml = '<span class="px-3 py-1.5 bg-purple-50 text-purple-600 border border-purple-200 text-xs font-black uppercase rounded-lg">New Ticket</span>';
        else if (feedback.status === 'waiting_on_support') statusHtml = '<span class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 text-xs font-black uppercase rounded-lg">User Replied</span>';
        else if (feedback.status === 'in_progress') statusHtml = '<span class="px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 text-xs font-black uppercase rounded-lg"><i class="fas fa-spinner fa-spin mr-1"></i> In Progress</span>';
        else if (feedback.status === 'waiting_on_user') statusHtml = '<span class="px-3 py-1.5 bg-amber-50 text-amber-600 border border-amber-200 text-xs font-black uppercase rounded-lg">Waiting on User</span>';
        else if (feedback.status === 'resolved') statusHtml = '<span class="px-3 py-1.5 bg-green-50 text-green-600 border border-green-200 text-xs font-black uppercase rounded-lg">Resolved</span>';
        else statusHtml = '<span class="px-3 py-1.5 bg-gray-100 text-gray-600 border border-gray-300 text-xs font-black uppercase rounded-lg"><i class="fas fa-lock"></i> Closed</span>';
        badgeContainer.innerHTML = statusHtml;

        getActiveEl('#ad-detail-message').innerText = feedback.message;

        const resolvedBlock = getActiveEl('#ad-resolved-block');
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

        const replyForm = getActiveEl('#admin-reply-form');
        if (feedback.status === 'closed') {
            replyForm.classList.add('hidden');
        } else {
            replyForm.classList.remove('hidden');
            getActiveEl('#reply-feedback-id').value = feedback.id;
            getActiveEl('#reply-message').value = '';
            getActiveEl('#reply-status').value = 'in_progress'; 
        }
    }

    function closeAdminFeedbackDetail() {
        getActiveEl('#admin-feedback-detail').classList.add('hidden');
        getActiveEl('#admin-feedback-list').classList.remove('hidden');
    }

    function submitAdminReply(e) {
        e.preventDefault();

        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        
        const feedbackId = getActiveEl('#reply-feedback-id').value;
        const message = getActiveEl('#reply-message').value;
        const statusValue = getActiveEl('#reply-status').value; 

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
                status: statusValue 
            })
        })
            .then(async response => {
                if (response.ok) {
                    if(typeof showSnackbar === 'function') showSnackbar('Update sent successfully!', 'success');
                    else alert(`Update sent successfully!`);
                    
                    window.location.reload(); 
                } else {
                    const data = await response.json();
                    if(typeof showSnackbar === 'function') showSnackbar(data.message || 'Error updating ticket.', 'error');
                    else alert(data.message || 'Error updating ticket.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                if(typeof showSnackbar === 'function') showSnackbar('A network error occurred.', 'error');
                else alert('A network error occurred.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
    }

    // --- NEW: Broadcast Modal Logic ---
    function openBroadcastModal() {
        const modal = document.getElementById('broadcastModal');
        const box = document.getElementById('broadcastBox');
        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeBroadcastModal() {
        const modal = document.getElementById('broadcastModal');
        const box = document.getElementById('broadcastBox');
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('broadcastForm').reset();
        }, 300);
    }

    function submitBroadcast(e) {
        e.preventDefault();
        const btn = document.getElementById('broadcastSubmitBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        btn.disabled = true;

        const subject = document.getElementById('broadcastSubject').value;
        const message = document.getElementById('broadcastMessage').value;
        const type = document.querySelector('input[name="broadcastType"]:checked').value;

        fetch(`{{ route('admin.broadcast') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                subject: subject,
                message: message,
                type: type
            })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok) {
                closeBroadcastModal();
                if(typeof showSnackbar === 'function') showSnackbar('Broadcast sent to all users!', 'success');
                else alert('Broadcast sent to all users!');
            } else {
                if(typeof showSnackbar === 'function') showSnackbar(data.message || 'Error sending broadcast.', 'error');
                else alert(data.message || 'Error sending broadcast.');
            }
        })
        .catch(err => {
            console.error(err);
            if(typeof showSnackbar === 'function') showSnackbar('A network error occurred.', 'error');
            else alert('A network error occurred.');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
</script>