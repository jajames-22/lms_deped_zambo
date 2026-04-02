@extends('dashboard.layout')

@section('sidebar_nav')
    {{-- ADDED ID: nav-home-btn --}}
    <button id="nav-home-btn" onclick="loadPartial('{{ url('/dashboard/home') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group transition-all">
        <i class="fas fa-th-large w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Dashboard</span> 
    </button>

    {{-- ADDED ID: nav-explore-btn --}}
    <button id="nav-explore-btn" onclick="loadPartial('{{ url('/dashboard/explore') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group transition-all">
        <i class="fas fa-compass w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Explore</span> 
    </button>

    <button id="nav-enrolled-btn" onclick="loadPartial('{{ url('/dashboard/enrolled') }}', this)" 
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group transition-all">
        <i class="fas fa-book-open w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Enrolled</span> 
    </button>

    {{-- ADDED ID: nav-certificates-btn --}}
    <button id="nav-certificates-btn" onclick="loadPartial('{{ url('/dashboard/certificates') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group transition-all">
        <i class="fas fa-tasks w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Certificates</span>
    </button>

    <button id="nav-analytics-btn" onclick="loadPartial('{{ url('/dashboard/analytics') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group transition-all">
        <i class="fas fa-chart-bar w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">My Progress</span> 
    </button>

    <button id="nav-profile-btn" onclick="loadPartial('{{ url('/dashboard/profile') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group transition-all">
        <i class="fas fa-user w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">My Profile</span>
    </button>
@endsection

<script>
    // Handle Auto-loading from Email Links
    @if(session('autoLoad'))
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                if (typeof loadPartial === 'function') {
                    loadPartial("{{ session('autoLoad') }}", document.getElementById('nav-enrolled-btn'));
                }
            }, 100);
        });
    @endif
</script>