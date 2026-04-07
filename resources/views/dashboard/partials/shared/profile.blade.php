@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
    $roleColors = [
        'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
        'teacher' => 'bg-blue-100 text-blue-700 border-blue-200',
        'student' => 'bg-green-100 text-green-700 border-green-200',
    ];
    $roleColor = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    
    // Fetch the user's feedbacks directly to power the modal
    $userFeedbacks = \App\Models\Feedback::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
@endphp

<div class="space-y-6 w-full max-w-6xl mx-auto pb-12">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-500 text-sm mt-1">Manage your personal information and account security.</p>
        </div>
        <div>
            <span class="px-4 py-2 {{ $roleColor }} text-xs font-bold rounded-lg border uppercase tracking-widest flex items-center gap-2">
                @if($user->role === 'admin') <i class="fas fa-shield-alt"></i>
                @elseif($user->role === 'teacher') <i class="fas fa-chalkboard-teacher"></i>
                @else <i class="fas fa-user-graduate"></i>
                @endif
                {{ ucfirst($user->role) }} Account
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
                
                <div class="relative inline-block mb-4 group mx-auto">
                    <div class="w-32 h-32 rounded-full border-4 border-gray-50 shadow-md overflow-hidden bg-gray-100 flex items-center justify-center text-4xl font-black text-[#a52a2a]">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile Photo" class="w-full h-full object-cover">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <label for="avatar-upload" class="absolute inset-0 bg-black/60 text-white rounded-full opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center cursor-pointer transition-all duration-300">
                        <i class="fas fa-camera text-xl mb-1"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Change</span>
                    </label>
                    <input type="file" id="avatar-upload" class="hidden" accept="image/png, image/jpeg, image/webp" onchange="previewAndUploadAvatar(this)">
                </div>

                <h3 class="text-xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h3>
                <p class="text-sm text-gray-500 mb-6">{{ $user->email }}</p>

                <div class="pt-6 border-t border-gray-100 space-y-5 text-left">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1.5">
                            {{ $user->role === 'student' ? 'Learner Reference Number (LRN)' : 'Employee ID' }}
                        </p>
                        <p class="font-mono text-gray-800 font-semibold bg-gray-50 p-2.5 rounded-lg border border-gray-200">
                            {{ $user->role === 'student' ? ($user->lrn ?? 'Not provided') : ($user->employee_id ?? 'Not provided') }}
                        </p>
                    </div>
                    
                    @if($user->school)
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1.5">Assigned Institution</p>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 flex items-start gap-3">
                                <div class="bg-white p-2 rounded shadow-sm shrink-0">
                                    <i class="fas fa-school text-[#a52a2a]"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-800 font-bold leading-tight">{{ $user->school->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $user->school->district->name ?? 'No District' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- NEW SUPPORT BUTTON --}}
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100 text-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg mx-auto mb-3">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-1">Need Help?</h3>
                        <p class="text-xs text-gray-500 mb-4 px-2">Report issues, bugs, or ask questions directly to the admin.</p>
                        <button type="button" onclick="openFeedbackModal()" class="w-full py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-blue-700 transition">
                            Contact Support
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="lg:col-span-8 space-y-6">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-red-50 text-[#a52a2a] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Personal Details</h2>
                        <p class="text-xs text-gray-500">Update your name and contact information.</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-5" onsubmit="submitProfileForm(event, this)">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $user->suffix) }}" placeholder="e.g. Jr."
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-bold text-gray-600 uppercase">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-gray-50 mt-6">
                        <button type="submit" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-gray-800 transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Security Settings</h2>
                        <p class="text-xs text-gray-500">Ensure your account is using a long, random password.</p>
                    </div>
                </div>

                <form action="{{ route('password.update') }}" method="POST" class="space-y-5" onsubmit="submitPasswordForm(event, this)">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-1.5 max-w-md">
                        <label class="text-xs font-bold text-gray-600 uppercase">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">New Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-gray-50 mt-6">
                        <button type="submit" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-gray-800 transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- HELP & FEEDBACK MODAL --}}
<div id="feedbackModal" class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeFeedbackModal()"></div>
    <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[90vh] min-h-[550px] relative z-10" id="feedbackModalBox">
        
        <div class="bg-gray-50 border-b border-gray-200 px-6 pt-6 shrink-0 relative">
            <button type="button" onclick="closeFeedbackModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-600 transition"><i class="fas fa-times"></i></button>
            <h2 class="text-xl font-black text-gray-900 mb-4">Help & Support</h2>
            <div class="flex gap-6 border-b border-gray-200">
                <button type="button" onclick="switchFeedbackTab('form')" id="tab-btn-form" class="pb-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 transition-colors">Send Report</button>
                <button type="button" onclick="switchFeedbackTab('list')" id="tab-btn-list" class="pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">My Reports</button>
            </div>
        </div>

        <div id="feedback-tab-form" class="p-6 overflow-y-auto sidebar-scroll">
            <form action="{{ route('feedback.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="submitFeedbackForm(event, this)">
                @csrf
                <div>
                    <label class="text-xs font-bold text-gray-600 uppercase mb-1 block">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:border-blue-600 outline-none transition-all text-sm">
                        <option value="" disabled selected>Select an issue type...</option>
                        <option value="account">Account or Login Issue</option>
                        <option value="material">Module / Course Content Error</option>
                        <option value="assessment">Assessment / Quiz Problem</option>
                        <option value="bug_report">System Bug / Glitch</option>
                        <option value="other">Other Inquiry</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600 uppercase mb-1 block">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" required placeholder="Brief title of the issue" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:border-blue-600 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600 uppercase mb-1 block">Message <span class="text-red-500">*</span></label>
                    <textarea name="message" required rows="4" placeholder="Describe the issue in detail. If this is about an assessment, please include the module title..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:border-blue-600 outline-none transition-all text-sm resize-none"></textarea>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600 uppercase mb-1 block">Attach Screenshot (Optional)</label>
                    <input type="file" name="media" accept="image/*" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition-colors">
                </div>
                <div class="pt-4 flex justify-end border-t border-gray-100">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-2 text-sm">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>
                </div>
            </form>
        </div>

        <div id="feedback-tab-list" class="hidden p-6 overflow-y-auto sidebar-scroll bg-gray-50/50 flex-1">
            
            <div id="feedback-list-view" class="space-y-3">
                @forelse($userFeedbacks as $fb)
                    <div onclick="viewFeedbackDetails({{ $fb->id }})" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm cursor-pointer hover:border-blue-300 transition group relative overflow-hidden">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-gray-900 group-hover:text-blue-600 transition truncate pr-4">{{ $fb->subject }}</h4>
                            @if($fb->status === 'resolved' || $fb->status === 'closed')
                                <span class="shrink-0 px-2 py-1 bg-green-100 text-green-700 text-[10px] font-black uppercase rounded-md">Resolved</span>
                            @else
                                <span class="shrink-0 px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-md">Pending</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mb-2">{{ ucfirst(str_replace('_', ' ', $fb->category)) }} • {{ $fb->created_at->format('M d, Y') }}</p>
                        <p class="text-sm text-gray-600 line-clamp-1">{{ $fb->message }}</p>
                    </div>
                @empty
                    <div class="flex-1 w-full flex flex-col items-center justify-center py-16 text-center m-auto h-full">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-sm">
                            <i class="fas fa-inbox text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No Reports Found</h3>
                        <p class="text-gray-500 font-medium text-sm">You haven't submitted any reports yet.</p>
                    </div>
                @endforelse
            </div>

            <div id="feedback-detail-view" class="hidden h-full flex-col">
                <button type="button" onclick="backToFeedbackList()" class="mb-4 text-sm font-bold text-gray-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to List
                </button>
                
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex-1 flex flex-col">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex justify-between items-start mb-2">
                            <h3 id="fd-subject" class="font-black text-lg text-gray-900 leading-tight">Loading...</h3>
                            <span id="fd-status" class="shrink-0 px-2 py-1 ml-4 bg-gray-100 text-gray-700 text-[10px] font-black uppercase rounded-md">Status</span>
                        </div>
                        <p id="fd-meta" class="text-xs text-gray-500 font-medium">Category • Date</p>
                    </div>
                    <div class="p-5 overflow-y-auto sidebar-scroll">
                        <div class="mb-6">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Your Original Message</h4>
                            <p id="fd-message" class="text-sm text-gray-800 whitespace-pre-wrap bg-gray-50 p-4 rounded-xl border border-gray-100 leading-relaxed"></p>
                            
                            <div id="fd-media-container" class="mt-3 hidden">
                                <a id="fd-media-link" href="#" target="_blank" class="inline-flex px-4 py-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-xs font-bold hover:bg-blue-100 transition items-center gap-2">
                                    <i class="fas fa-image text-lg"></i> View Attached Screenshot
                                </a>
                            </div>
                        </div>
                        
                        <div id="fd-reply-container" class="hidden border-t border-gray-100 pt-6">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="fas fa-user-shield"></i></div>
                                <div>
                                    <h4 class="text-[11px] font-black text-gray-900 uppercase tracking-widest leading-none">Admin Response</h4>
                                    <p class="text-[10px] text-gray-400">System Administrator</p>
                                </div>
                            </div>
                            <p id="fd-reply" class="text-sm text-gray-800 whitespace-pre-wrap bg-green-50 p-4 rounded-xl border border-green-200 leading-relaxed"></p>
                        </div>
                        
                        <div id="fd-no-reply" class="border-t border-gray-100 pt-6 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-2 text-xl"><i class="fas fa-clock"></i></div>
                            <p class="text-sm text-gray-500 font-medium">An administrator has not replied to this ticket yet.</p>
                            <p class="text-xs text-gray-400 mt-1">We usually respond within 24-48 hours.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GENERIC SUCCESS MODAL --}}
<div id="profileSuccessModal" class="fixed inset-0 z-[99999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeProfileSuccessModal()"></div>
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 text-center p-6 relative z-10" id="profileSuccessBox">
        <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl bg-green-50 text-green-500">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 id="profileSuccessTitle" class="text-xl font-black text-gray-900 mb-2">Success!</h3>
        <p id="profileSuccessMessage" class="text-sm text-gray-500 mb-6">Action completed successfully.</p>
        <button type="button" onclick="closeProfileSuccessModal()" class="w-full px-4 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-md">
            Okay
        </button>
    </div>
</div>

<script>
    setTimeout(() => {
        const savedUrl = sessionStorage.getItem('lastActiveTab') || '';
        if(savedUrl.includes('?ticket=')) {
            const ticketId = parseInt(new URLSearchParams(savedUrl.split('?')[1]).get('ticket'));
            if(ticketId) {
                openFeedbackModal();
                switchFeedbackTab('list');
                setTimeout(() => viewFeedbackDetails(ticketId), 150);
            }
        }
    }, 100);

    // --- SUCCESS MODAL LOGIC ---
    function showProfileSuccess(title, message, callback = null) {
        window.profileSuccessCallback = callback;
        document.getElementById('profileSuccessTitle').innerText = title;
        document.getElementById('profileSuccessMessage').innerText = message;
        
        const modal = document.getElementById('profileSuccessModal');
        const box = document.getElementById('profileSuccessBox');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    }

    function closeProfileSuccessModal() {
        const modal = document.getElementById('profileSuccessModal');
        const box = document.getElementById('profileSuccessBox');
        
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        
        setTimeout(() => { 
            modal.classList.add('hidden'); 
            if (window.profileSuccessCallback) {
                window.profileSuccessCallback();
                window.profileSuccessCallback = null;
            }
        }, 300);
    }

    // --- FEEDBACK MODAL LOGIC ---
    window.userFeedbacksData = @json($userFeedbacks);

    function openFeedbackModal() {
        const modal = document.getElementById('feedbackModal');
        const box = document.getElementById('feedbackModalBox');
        switchFeedbackTab('form'); 
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    }

    function closeFeedbackModal() {
        const modal = document.getElementById('feedbackModal');
        const box = document.getElementById('feedbackModalBox');
        
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        
        setTimeout(() => { 
            modal.classList.add('hidden'); 
            backToFeedbackList(); 
        }, 300);
    }

    function switchFeedbackTab(tab) {
        const tabForm = document.getElementById('feedback-tab-form');
        const tabList = document.getElementById('feedback-tab-list');
        const btnForm = document.getElementById('tab-btn-form');
        const btnList = document.getElementById('tab-btn-list');

        if (tab === 'form') {
            tabForm.classList.remove('hidden');
            tabList.classList.add('hidden');
            tabList.classList.remove('flex', 'flex-col', 'w-full');
            
            btnForm.className = 'pb-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 transition-colors';
            btnList.className = 'pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors';
        } else {
            tabForm.classList.add('hidden');
            tabList.classList.remove('hidden');
            tabList.classList.add('flex', 'flex-col', 'w-full');
            
            btnList.className = 'pb-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 transition-colors';
            btnForm.className = 'pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors';
        }
    }

    function submitFeedbackForm(e, form) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' }
        })
        .then(async response => {
            if(response.ok) {
                // Trigger the beautiful new success modal!
                showProfileSuccess('Report Sent!', 'Your feedback has been submitted successfully. The admin will review it shortly.', () => {
                    closeFeedbackModal();
                    // Fixes the active button state explicitly!
                    loadPartial('{{ route('profile') }}', document.getElementById('nav-profile-btn'));
                });
            } else {
                const data = await response.json();
                alert(data.message || 'Validation error. Please check your inputs.');
            }
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

    function viewFeedbackDetails(id) {
        // FIX: Use the window variable and == to find the correct report
        const feedback = window.userFeedbacksData.find(fb => fb.id == id);
        
        if (!feedback) return;

        document.getElementById('feedback-list-view').classList.add('hidden');
        document.getElementById('feedback-detail-view').classList.remove('hidden');
        document.getElementById('feedback-detail-view').classList.add('flex');

        document.getElementById('fd-subject').innerText = feedback.subject;
        document.getElementById('fd-message').innerText = feedback.message;
        
        const dateObj = new Date(feedback.created_at);
        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const catName = feedback.category.charAt(0).toUpperCase() + feedback.category.slice(1).replace('_', ' ');
        document.getElementById('fd-meta').innerText = `${catName} • ${formattedDate}`;

        const statusBadge = document.getElementById('fd-status');
        if (feedback.status === 'resolved' || feedback.status === 'closed') {
            statusBadge.className = 'shrink-0 px-2 py-1 ml-4 bg-green-100 text-green-700 text-[10px] font-black uppercase rounded-md';
            statusBadge.innerText = 'Resolved';
        } else {
            statusBadge.className = 'shrink-0 px-2 py-1 ml-4 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-md';
            statusBadge.innerText = 'Pending';
        }

        const mediaContainer = document.getElementById('fd-media-container');
        const mediaLink = document.getElementById('fd-media-link');
        if (feedback.media_url) {
            mediaContainer.classList.remove('hidden');
            mediaLink.href = feedback.media_url;
        } else {
            mediaContainer.classList.add('hidden');
        }

        const replyContainer = document.getElementById('fd-reply-container');
        const noReplyContainer = document.getElementById('fd-no-reply');
        
        if (feedback.admin_reply) {
            replyContainer.classList.remove('hidden');
            noReplyContainer.classList.add('hidden');
            document.getElementById('fd-reply').innerText = feedback.admin_reply;
        } else {
            replyContainer.classList.add('hidden');
            noReplyContainer.classList.remove('hidden');
        }
    }

    function backToFeedbackList() {
        document.getElementById('feedback-detail-view').classList.add('hidden');
        document.getElementById('feedback-detail-view').classList.remove('flex');
        document.getElementById('feedback-list-view').classList.remove('hidden');
    }

    // --- EXISTING AVATAR/PROFILE LOGIC ---
    function previewAndUploadAvatar(input) {
        if (input.files && input.files[0]) {
            let formData = new FormData();
            formData.append('avatar', input.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('{{ route('profile.avatar.update') }}', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showProfileSuccess('Avatar Updated!', 'Your profile picture has been changed.', () => {
                        loadPartial('{{ route('profile') }}', document.getElementById('nav-profile-btn'));
                    });
                } else {
                    alert('Error uploading image. Make sure it is under 2MB.');
                }
            })
            .catch(() => alert('Network error occurred during upload.'));
        }
    }

    function submitProfileForm(e, form) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' }
        })
        .then(async response => {
            if(response.ok) {
                showProfileSuccess('Profile Updated!', 'Your personal details have been saved.');
            } else {
                const data = await response.json();
                alert(data.message || 'Validation error. Please check your inputs.');
            }
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

    function submitPasswordForm(e, form) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' }
        })
        .then(async response => {
            if(response.ok) {
                showProfileSuccess('Password Updated!', 'Your security settings have been saved.');
                form.reset();
            } else {
                const data = await response.json();
                alert(data.message || 'Error updating password. Check your current password.');
            }
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
</script>