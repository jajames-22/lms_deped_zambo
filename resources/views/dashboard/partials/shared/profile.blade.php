@php
    // Fetch fresh user data directly from DB
    $user = \App\Models\User::find(\Illuminate\Support\Facades\Auth::id()); 
    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
    $roleColors = [
        'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
        'teacher' => 'bg-blue-100 text-blue-700 border-blue-200',
        'student' => 'bg-green-100 text-green-700 border-green-200',
    ];
    $roleColor = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    
    // Fetch user feedbacks WITH threaded messages
    $userFeedbacks = \App\Models\Feedback::with('messages.sender')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')->get();
    
    $ticketsJson = $userFeedbacks->map(function($f) {
        return [
            'id' => $f->id,
            'subject' => $f->subject,
            'category' => ucwords(str_replace('_', ' ', $f->category)),
            'message' => $f->message,
            'status' => $f->status,
            'messages' => $f->messages, // Pass the array of replies!
            'media_url' => $f->media_url ? asset('storage/' . $f->media_url) : null,
            'date' => $f->created_at->format('M d, Y h:i A')
        ];
    })->values(); // Forces a clean Javascript array
    
    $schools = \App\Models\School::orderBy('name', 'asc')->get();

    // --- 30-DAY RESTRICTION LOGIC ---
    $hoursSinceUpdate = $user->updated_at->diffInHours(\Carbon\Carbon::now());
    $requiredHours = 30 * 24; // 720 hours
    $daysLeftToUpdate = 0;
    
    if ($hoursSinceUpdate < $requiredHours) {
        $daysLeftToUpdate = ceil(($requiredHours - $hoursSinceUpdate) / 24);
    }
@endphp

<div class="space-y-6 w-full max-w-6xl mx-auto pb-12 animate-float-in">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-500 text-sm mt-1">Manage your personal information and account security.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($user->role !== 'admin')
            <button onclick="openSupportModal()" class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl shadow-md hover:bg-black transition-all flex items-center gap-2">
                <i class="fas fa-headset"></i> Contact Support
            </button>
            @endif
            
            <span class="px-4 py-2 {{ $roleColor }} text-xs font-black rounded-xl border uppercase tracking-widest inline-flex items-center gap-2">
                <i class="fas fa-circle text-[8px]"></i>
                {{ $user->role }}
            </span>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-8 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-[#a52a2a]/10 to-transparent"></div>
        
        <div class="relative group">
            <div class="w-32 h-32 rounded-full border-4 border-white shadow-xl overflow-hidden bg-gray-50 flex items-center justify-center text-4xl font-black text-gray-300 relative z-10">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile Photo" class="w-full h-full object-cover">
                @else
                    {{ $initials }}
                @endif
            </div>
            
            <button onclick="document.getElementById('avatarUpload').click()" 
                class="absolute bottom-2 right-2 w-10 h-10 bg-gray-900 text-white rounded-full border-2 border-white shadow-lg flex items-center justify-center hover:bg-[#a52a2a] transition-all z-20 group-hover:scale-110">
                <i class="fas fa-camera text-sm"></i>
            </button>
            
            <form id="avatarForm" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf @method('PATCH')
                <input type="file" id="avatarUpload" name="avatar" accept="image/*" onchange="submitAvatarForm()">
            </form>
        </div>

        <div class="flex-1 text-center md:text-left relative z-10">
            <h2 class="text-3xl font-black text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2 justify-center md:justify-start">
                <i class="fas fa-envelope text-gray-400"></i> {{ $user->email ?? 'No email provided' }}
            </p>
            <div class="mt-4 flex flex-wrap items-center justify-center md:justify-start gap-3">
                <div class="px-4 py-2 bg-gray-50 rounded-xl border border-gray-100 text-xs font-bold text-gray-600 flex items-center gap-2">
                    <i class="fas fa-id-badge text-gray-400"></i>
                    {{ $user->email ?? 'Not provided' }}
                </div>
                <div class="px-4 py-2 bg-gray-50 rounded-xl border border-gray-100 text-xs font-bold text-gray-600 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-gray-400"></i>
                    Joined {{ $user->created_at->format('M Y') }}
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 flex-shrink-0 space-y-2">
            <button onclick="switchProfileTab('profile')" id="btn-tab-profile" 
                class="profile-tab-btn bg-[#a52a2a] text-white shadow-md w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-left text-sm">
                <i class="fas fa-user w-5 text-center"></i>
                Personal Info
            </button>
            
            <button onclick="switchProfileTab('security')" id="btn-tab-security" 
                class="profile-tab-btn text-gray-600 hover:bg-gray-100 hover:text-gray-900 w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-left text-sm">
                <i class="fas fa-shield-alt w-5 text-center"></i>
                Security & Password
            </button>
        </div>

        <div class="flex-1">
            
            <div id="panel-profile" class="profile-tab-panel transition-opacity duration-300">
                <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-6">Personal Details</h3>
                    
                    <form action="{{ route('profile.update') }}" method="POST" onsubmit="submitProfileForm(event, this)">
                        @csrf
                        @method('PATCH')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                                <input type="text" name="username" value="{{ $user->username }}" required 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                                <p class="text-[10px] text-gray-500 mt-1 leading-tight">
                                    Max 30 chars. Must contain at least 3 letters. Only letters, numbers, periods (.), or underscores (_) allowed.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                                <input type="text" name="first_name" value="{{ $user->first_name }}" required 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ $user->middle_name }}" 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" name="last_name" value="{{ $user->last_name }}" required 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Suffix</label>
                                <input type="text" name="suffix" value="{{ $user->suffix }}" placeholder="e.g. Jr., Sr." 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>

                            @if($user->role === 'student')
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Grade Level <span class="text-red-500">*</span></label>
                                    <select name="grade_level" required class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none cursor-pointer">
                                        <option value="" disabled {{ empty($user->grade_level) ? 'selected' : '' }}>Select grade level...</option>
                                        <optgroup label="Primary">
                                            <option value="Kindergarten" {{ $user->grade_level == 'Kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                                            <option value="Grade 1" {{ $user->grade_level == 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                                            <option value="Grade 2" {{ $user->grade_level == 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                                            <option value="Grade 3" {{ $user->grade_level == 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                                            <option value="Grade 4" {{ $user->grade_level == 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                                            <option value="Grade 5" {{ $user->grade_level == 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                                            <option value="Grade 6" {{ $user->grade_level == 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                                        </optgroup>
                                        <optgroup label="Junior High">
                                            <option value="Grade 7" {{ $user->grade_level == 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                            <option value="Grade 8" {{ $user->grade_level == 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                            <option value="Grade 9" {{ $user->grade_level == 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                            <option value="Grade 10" {{ $user->grade_level == 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                        </optgroup>
                                        <optgroup label="Senior High">
                                            <option value="Grade 11" {{ $user->grade_level == 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                                            <option value="Grade 12" {{ $user->grade_level == 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Institution / School <span class="text-red-500">*</span></label>
                                    <select name="school_id" id="schoolSelect" required class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none cursor-pointer">
                                        <option value="" disabled {{ empty($user->school_id) ? 'selected' : '' }}>Select your school...</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" {{ $user->school_id == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }} (ID: {{ $school->school_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition-all flex items-center gap-2">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="panel-security" class="profile-tab-panel hidden transition-opacity duration-300">
                <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-6 flex items-center gap-2">
                        <i class="fas fa-lock text-[#a52a2a]"></i> Change Password
                    </h3>
                    
                    <form action="{{ route('password.update') }}" method="POST" onsubmit="submitPasswordForm(event, this)">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-5 max-w-lg">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Current Password</label>
                                <input type="password" name="current_password" required 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">New Password</label>
                                <input type="password" name="password" required 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Confirm New Password</label>
                                <input type="password" name="password_confirmation" required 
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-start">
                            <button type="submit" class="px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition-all flex items-center gap-2">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if($user->role !== 'admin')
<div id="supportModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6">
    <div class="absolute inset-0 bg-gray-900/70 transition-opacity" onclick="closeSupportModal()"></div>
    <div id="supportModalBox" class="relative bg-gray-50 rounded-3xl shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col transform scale-95 opacity-0 transition-all duration-300">
        
        <div class="bg-white px-6 py-5 border-b border-gray-100 flex justify-between items-center shrink-0 z-10 rounded-t-3xl shadow-sm">
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-50 text-[#a52a2a] flex items-center justify-center shadow-inner">
                    <i class="fas fa-headset"></i> 
                </div>
                Support Center
            </h3>
            <button onclick="closeSupportModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex border-b border-gray-200 px-6 pt-2 bg-white shrink-0">
            <button id="btn-support-send" onclick="switchSupportTab('send')" class="px-5 py-3 font-bold text-[#a52a2a] border-b-2 border-[#a52a2a] transition-all focus:outline-none">Send Report</button>
            <button id="btn-support-history" onclick="switchSupportTab('history')" class="px-5 py-3 font-bold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-all focus:outline-none">My Reports</button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 relative rounded-b-3xl sidebar-scroll">
            
            <div id="support-panel-send" class="support-panel block">
                <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-6 flex items-center gap-2">
                        <i class="fas fa-paper-plane text-[#a52a2a]"></i> Submit a New Ticket
                    </h3>
                    <form action="{{ route('feedback.store') }}" method="POST" enctype="multipart/form-data" onsubmit="submitFeedbackForm(event, this)">
                        @csrf
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                                    <select name="category" required class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none cursor-pointer">
                                        <option value="" disabled selected>Select an issue category...</option>
                                        <option value="bug_report">Bug Report / Technical Issue</option>
                                        <option value="feature_request">Feature Request</option>
                                        <option value="account_issue">Account & Login Issue</option>
                                        <option value="content_issue">Course Material Issue</option>
                                        <option value="general_inquiry">General Inquiry</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
                                    <input type="text" name="subject" required placeholder="Briefly describe the issue..."
                                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Message <span class="text-red-500">*</span></label>
                                <textarea name="message" required rows="4" placeholder="Provide more details here..."
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">Attachment / Screenshot <span class="text-gray-400 font-normal">(Optional, max 2MB)</span></label>
                                <input type="file" name="media" accept="image/*"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#a52a2a]/10 file:text-[#a52a2a] hover:file:bg-[#a52a2a]/20 cursor-pointer">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition-all flex items-center gap-2">
                                <i class="fas fa-paper-plane"></i> Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="support-panel-history" class="support-panel hidden">
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    @if($userFeedbacks->count() > 0)
                        <div class="space-y-3">
                            @foreach($userFeedbacks as $feedback)
                                <div onclick="openTicketView({{ $feedback->id }})" class="cursor-pointer p-4 rounded-xl border border-gray-100 hover:border-[#a52a2a]/40 hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between bg-gray-50 hover:bg-white group gap-4">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-900 text-base truncate group-hover:text-[#a52a2a] transition-colors">{{ $feedback->subject }}</h4>
                                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                            <span><i class="fas fa-clock text-gray-400"></i> {{ $feedback->created_at->format('M d, Y') }}</span>
                                            <span class="text-gray-300">•</span>
                                            <span class="truncate">{{ ucwords(str_replace('_', ' ', $feedback->category)) }}</span>
                                        </p>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-4">
                                        <span class="text-[10px] uppercase tracking-widest font-black px-3 py-1.5 rounded-lg border 
                                            {{ in_array($feedback->status, ['open', 'waiting_on_support']) ? 'bg-amber-100 text-amber-700 border-amber-200' : '' }}
                                            {{ in_array($feedback->status, ['in_progress', 'waiting_on_user']) ? 'bg-blue-100 text-blue-700 border-blue-200' : '' }}
                                            {{ $feedback->status === 'resolved' ? 'bg-green-100 text-green-700 border-green-200' : '' }}
                                            {{ $feedback->status === 'closed' ? 'bg-gray-100 text-gray-600 border-gray-200' : '' }}">
                                            {{ str_replace('_', ' ', $feedback->status) }}
                                        </span>
                                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-[#a52a2a] transition-colors"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-inbox text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">No reports found.</p>
                            <p class="text-gray-400 text-xs mt-1">When you submit an issue, it will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div id="support-panel-view" class="support-panel hidden">
                <button onclick="switchSupportTab('history')" class="mb-4 text-sm font-bold text-gray-500 hover:text-[#a52a2a] flex items-center gap-2 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    Back to List
                </button>
                <div id="ticket-view-content" class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                </div>
            </div>

        </div>
    </div>
</div>
@endif

<div id="profileGlobalModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity" onclick="closeProfileModal()"></div>
    <div id="profileGlobalModalBox" class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
        
        <div id="profileModalIconBox" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i id="profileModalIcon" class="text-4xl"></i>
        </div>
        
        <h3 id="profileModalTitle" class="text-2xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="profileModalMessage" class="text-gray-500 mb-8 text-sm">Message content here.</p>
        
        <button type="button" id="profileModalBtn" onclick="closeProfileModal()" class="w-full px-4 py-3 font-bold rounded-xl shadow-lg transition">
            Okay
        </button>
    </div>
</div>

<script>
    // --- TOM SELECT INITIALIZATION ---
    (function initTomSelect() {
        const schoolSelect = document.getElementById('schoolSelect');
        if (schoolSelect) {
            if (typeof TomSelect !== 'undefined') {
                new TomSelect("#schoolSelect", { create: false, sortField: { field: "text", direction: "asc" } });
            } else {
                const script = document.createElement('script');
                script.src = "https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js";
                script.onload = () => {
                    new TomSelect("#schoolSelect", { create: false, sortField: { field: "text", direction: "asc" } });
                };
                document.head.appendChild(script);
                
                if(!document.getElementById('tomSelectCss')) {
                    const link = document.createElement('link');
                    link.id = 'tomSelectCss';
                    link.rel = 'stylesheet';
                    link.href = 'https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css';
                    document.head.appendChild(link);
                }
            }
        }
    })();

    window.daysLeftToUpdate = {{ max(0, $daysLeftToUpdate) }};

    const userTicketsData = @json($ticketsJson);

    function openSupportModal() {
        const modal = document.getElementById('supportModal');
        const box = document.getElementById('supportModalBox');
        if(!modal) return;
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeSupportModal() {
        const modal = document.getElementById('supportModal');
        const box = document.getElementById('supportModalBox');
        if(!modal) return;
        
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function switchSupportTab(tab) {
        document.querySelectorAll('.support-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('support-panel-' + tab).classList.remove('hidden');
        
        const btnSend = document.getElementById('btn-support-send');
        const btnHistory = document.getElementById('btn-support-history');
        
        if (tab === 'send') {
            btnSend.className = 'px-5 py-3 font-bold text-[#a52a2a] border-b-2 border-[#a52a2a] transition-all focus:outline-none';
            btnHistory.className = 'px-5 py-3 font-bold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-all focus:outline-none';
        } else if (tab === 'history') {
            btnHistory.className = 'px-5 py-3 font-bold text-[#a52a2a] border-b-2 border-[#a52a2a] transition-all focus:outline-none';
            btnSend.className = 'px-5 py-3 font-bold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-all focus:outline-none';
        } else if (tab === 'view') {
            btnSend.className = 'px-5 py-3 font-bold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-all focus:outline-none';
            btnHistory.className = 'px-5 py-3 font-bold text-[#a52a2a] border-b-2 border-[#a52a2a] transition-all focus:outline-none';
        }
    }

    function openTicketView(id) {
        // Safe lookup: Find the specific ticket matching the ID
        const t = userTicketsData.find(ticket => ticket.id == id);
        if(!t) return;
        
        let statusStyle = 'bg-gray-100 text-gray-600';
        if(['open', 'waiting_on_support'].includes(t.status)) statusStyle = 'bg-amber-100 text-amber-700 border-amber-200';
        else if(t.status === 'in_progress' || t.status === 'waiting_on_user') statusStyle = 'bg-blue-100 text-blue-700 border-blue-200';
        else if(t.status === 'resolved') statusStyle = 'bg-green-100 text-green-700 border-green-200';

        let html = `
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-4">
                <h4 class="font-black text-gray-900 text-2xl leading-tight">${t.subject}</h4>
                <span class="shrink-0 text-[10px] uppercase tracking-widest font-black px-3 py-1.5 rounded-lg border ${statusStyle}">${t.status.replace(/_/g, ' ')}</span>
            </div>
            <div class="text-sm text-gray-700 mb-8 whitespace-pre-wrap leading-relaxed">${t.message}</div>
        `;
        
        // Render Thread
        if (t.messages && t.messages.length > 0) {
            html += `<h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4 border-b border-gray-100 pb-2">Ticket History</h4>`;
            t.messages.forEach(msg => {
                const isAdmin = msg.sender && msg.sender.role === 'admin';
                if (isAdmin) {
                    html += `<div class="mt-3 p-5 bg-[#a52a2a]/5 rounded-2xl border border-[#a52a2a]/20 relative overflow-hidden"><div class="absolute top-0 left-0 w-1 h-full bg-[#a52a2a]"></div><div class="flex items-center gap-2 mb-2 text-[#a52a2a]"><i class="fas fa-headset"></i><span class="text-xs font-black uppercase tracking-widest">Support Response</span></div><p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">${msg.message}</p></div>`;
                } else {
                    html += `<div class="mt-3 p-5 bg-gray-50 rounded-2xl border border-gray-200"><div class="flex items-center gap-2 mb-2 text-gray-600"><i class="fas fa-user"></i><span class="text-xs font-black uppercase tracking-widest">You</span></div><p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">${msg.message}</p></div>`;
                }
            });
        }
        
        // Show 3-Day Warning if Resolved
        if (t.status === 'resolved') {
            html += `
            <div class="mt-6 mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div>
                    <p class="text-sm text-blue-800 font-bold">Ticket Resolved</p>
                    <p class="text-xs text-blue-600 mt-1">If there are no further problems, this ticket will be permanently closed in 3 days. If you still need help, reply below to reopen it.</p>
                </div>
            </div>`;
        }

        // Append Reply Form if NOT Hard Closed
        if (t.status !== 'closed') {
            html += `
            <form action="/dashboard/feedback/${t.id}/user-reply" method="POST" onsubmit="submitFeedbackReplyForm(event, this, ${t.id})" class="mt-4 pt-6 border-t border-gray-100">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label class="block text-sm font-bold text-gray-700 mb-2">Send a Reply</label>
                <textarea name="message" required rows="3" placeholder="Add more details or answer support's question..." class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] transition-all text-sm outline-none resize-none"></textarea>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition flex items-center gap-2 text-sm"><i class="fas fa-reply"></i> Send Reply</button>
                </div>
            </form>`;
        }

        document.getElementById('ticket-view-content').innerHTML = html;
        switchSupportTab('view');
    }

    (function() {
        const activeUrl = sessionStorage.getItem('lastActiveTab') || window.location.href;
        
        if(activeUrl.includes('tab=history')) {
            openSupportModal();
            setTimeout(() => {
                switchSupportTab('history');
            }, 350); 
            sessionStorage.removeItem('lastActiveTab');
        } else {
            try {
                const urlObj = new URL(activeUrl, window.location.origin);
                const ticketId = urlObj.searchParams.get('ticket');
        
                if (ticketId) {
                    openSupportModal();
                    setTimeout(() => {
                        openTicketView(ticketId);
                    }, 350); 
                }
            } catch(e) {}
        }
    })();

    function switchProfileTab(tabId) {
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            btn.className = 'profile-tab-btn text-gray-600 hover:bg-gray-100 hover:text-gray-900 w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-left text-sm';
        });

        const activeBtn = document.getElementById('btn-tab-' + tabId);
        if (activeBtn) {
            activeBtn.className = 'profile-tab-btn bg-[#a52a2a] text-white shadow-md w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all text-left text-sm';
        }

        document.querySelectorAll('.profile-tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        const activePanel = document.getElementById('panel-' + tabId);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }
    }

    let reloadPageOnModalClose = false;

    function showProfileModal(title, message, type = 'success') {
        document.getElementById('profileModalTitle').innerText = title;
        document.getElementById('profileModalMessage').innerText = message;
        
        const iconBox = document.getElementById('profileModalIconBox');
        const icon = document.getElementById('profileModalIcon');
        const btn = document.getElementById('profileModalBtn');

        if (type === 'success') {
            reloadPageOnModalClose = true; 
            iconBox.className = 'w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner';
            icon.className = 'fas fa-check text-4xl';
            btn.className = 'w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition';
        } else {
            reloadPageOnModalClose = false; 
            iconBox.className = 'w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner';
            icon.className = 'fas fa-exclamation-triangle text-4xl';
            btn.className = 'w-full px-4 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-black transition';
        }

        var modal = document.getElementById('profileGlobalModal');
        var box = document.getElementById('profileGlobalModalBox');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeProfileModal() {
        var modal = document.getElementById('profileGlobalModal');
        var box = document.getElementById('profileGlobalModalBox');
        
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            if (reloadPageOnModalClose) {
                loadPartial('{{ route('dashboard.profile') }}', document.getElementById('nav-profile-btn') || document.body);
            }
        }, 300);
    }

    function submitAvatarForm() {
        const form = document.getElementById('avatarForm');
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(async response => {
            if(response.ok) {
                showProfileModal('Avatar Updated!', 'Your profile picture has been successfully updated.', 'success');
            } else {
                showProfileModal('Upload Failed', 'There was an error uploading your image.', 'error');
            }
        });
    }

    function submitProfileForm(e, form) {
        e.preventDefault();

        if (window.daysLeftToUpdate > 0) {
            showProfileModal(
                'Update Restricted', 
                `You recently updated your profile. You cannot change your personal details for another ${window.daysLeftToUpdate} day(s).`, 
                'error'
            );
            return;
        }

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
                showProfileModal('Profile Updated!', 'Your personal details have been saved.', 'success');
            } else {
                const data = await response.json();
                showProfileModal('Validation Error', data.message || 'Please check your inputs and try again.', 'error');
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
                showProfileModal('Password Updated!', 'Your security settings have been saved.', 'success');
                form.reset();
            } else {
                const data = await response.json();
                showProfileModal('Error Updating Password', data.message || 'Check your current password and try again.', 'error');
            }
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

    function submitFeedbackReplyForm(e, form, ticketId) {
        e.preventDefault();
        
        const btn = form.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        btn.disabled = true;

        closeSupportModal(); 

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' }
        })
        .then(async response => {
            if(response.ok) {
                sessionStorage.setItem('lastActiveTab', '/dashboard/profile?ticket=' + ticketId);
                showProfileModal('Reply Sent!', 'Your message has been added to the ticket.', 'success');
                form.reset();
            } else {
                const data = await response.json();
                showProfileModal('Submission Failed', data.message || 'There was an error sending your reply.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showProfileModal('Network Error', 'A network error occurred while sending your reply.', 'error');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

    function submitFeedbackForm(e, form) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        btn.disabled = true;

        closeSupportModal(); 

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' }
        })
        .then(async response => {
            if(response.ok) {
                sessionStorage.setItem('lastActiveTab', '/dashboard/profile?tab=history'); 
                showProfileModal('Ticket Submitted!', 'Your feedback has been sent to the admins.', 'success');
                form.reset();
            } else {
                const data = await response.json();
                showProfileModal('Submission Failed', data.message || 'There was an error submitting your ticket.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showProfileModal('Network Error', 'A network error occurred.', 'error');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
</script>