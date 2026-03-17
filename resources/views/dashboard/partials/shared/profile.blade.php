@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
    $roleColors = [
        'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
        'teacher' => 'bg-blue-100 text-blue-700 border-blue-200',
        'student' => 'bg-green-100 text-green-700 border-green-200',
    ];
    $roleColor = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <div class="lg:col-span-1 space-y-6 sticky top-6">
            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm text-center">
                <div class="relative inline-block mb-4 group">
                    <div class="w-32 h-32 rounded-full border-4 border-white shadow-xl overflow-hidden bg-gray-50 flex items-center justify-center text-4xl font-black text-[#a52a2a]">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile Photo" class="w-full h-full object-cover">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <label for="avatar-upload" class="absolute inset-0 bg-black/50 text-white rounded-full opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center cursor-pointer transition-all duration-300 backdrop-blur-sm">
                        <i class="fas fa-camera text-xl mb-1"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Change</span>
                    </label>
                    <input type="file" id="avatar-upload" class="hidden" accept="image/png, image/jpeg, image/webp" onchange="previewAndUploadAvatar(this)">
                </div>

                <h3 class="text-xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>

                <div class="mt-6 pt-6 border-t border-gray-100 space-y-4 text-left">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">
                            {{ $user->role === 'student' ? 'Learner Reference Number (LRN)' : 'Employee ID' }}
                        </p>
                        <p class="font-mono text-gray-800 font-semibold bg-gray-50 p-2 rounded-lg border border-gray-100">
                            {{ $user->user_id }}
                        </p>
                    </div>
                    
                    @if($user->school)
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Assigned Institution</p>
                            <p class="text-sm text-gray-800 font-semibold flex items-start gap-2">
                                <i class="fas fa-school text-[#a52a2a] mt-1"></i>
                                <span>
                                    {{ $user->school->name }}<br>
                                    <span class="text-xs text-gray-500 font-normal">{{ $user->school->district->name ?? '' }}</span>
                                </span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-red-50 text-[#a52a2a] flex items-center justify-center text-lg">
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

                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-1 space-y-1.5">
                                <label class="text-xs font-bold text-gray-600 uppercase">Suffix</label>
                                <input type="text" name="suffix" value="{{ old('suffix', $user->suffix) }}" placeholder="e.g. Jr."
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                            </div>
                            <div class="col-span-2 space-y-1.5">
                                <label class="text-xs font-bold text-gray-600 uppercase">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-gray-800 transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-lg">
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
                        <label class="text-xs font-bold text-gray-600 uppercase">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">New Password</label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-600 uppercase">Confirm Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a] outline-none transition-all text-sm">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-gray-50">
                        <button type="submit" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-md hover:bg-gray-800 transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

<script>
    // Frontend JS handles for the forms.
    // Adjust these if you are using your standard `fetch` or `axios` partial loader system.

    function previewAndUploadAvatar(input) {
        if (input.files && input.files[0]) {
            // Show a loading snackbar or alert here if you want
            
            let formData = new FormData();
            formData.append('avatar', input.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            // Replace with your actual avatar upload route
            fetch('{{ route('profile.avatar.update') }}', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Reload the profile partial to show the new image
                    loadPartial('{{ route('profile') }}', document.querySelector('.active-nav-btn'));
                } else {
                    alert('Error uploading image.');
                }
            });
        }
    }

    function submitProfileForm(e, form) {
        // If you want standard Laravel submission with page reload, remove this function entirely.
        // If you are using your SPA-style partial loading, keep this to prevent full page reloads:
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
        .then(response => {
            if(response.ok) {
                alert('Profile updated successfully!');
                // Optional: loadPartial(...) to refresh the view
            } else {
                alert('Validation error. Please check your inputs.');
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
                alert('Password updated successfully!');
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