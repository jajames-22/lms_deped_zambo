@extends('dashboard.layout')

@section('sidebar_nav')
    <button onclick="loadPartial('{{ url('/dashboard/home') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition-all group">
        <i class="fas fa-th-large w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Dashboard</span> 
    </button>

    <button id="nav-materials-btn" onclick="loadPartial('{{ url('/dashboard/materials') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition-all group">
        <i class="fas fa-clipboard w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Materials</span> 
    </button>

    <button id="nav-assessment-btn" onclick="loadPartial('{{ url('/dashboard/assessment') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition-all group">
        <i class="fas fa-clipboard-list w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Assessment</span> 
    </button>

    <button id="nav-criteria-btn" onclick="loadPartial('{{ url('/dashboard/criteria') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition-all group">
        <i class="fas fa-list-check w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Criteria Builder</span> 
    </button>

    <button id="nav-explore-layout-btn" onclick="loadPartial('{{ url('/dashboard/explore-layout') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition-all group">
        <i class="fas fa-layer-group w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Explore Layout</span> 
    </button>

    <button id="nav-feedback-btn" onclick="loadPartial('{{ url('/dashboard/feedback') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-comment w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Help & Feedback</span> 
    </button>
    
    <button id="nav-profile-btn" onclick="loadPartial('{{ url('/dashboard/profile') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-user w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Profile</span>
    </button>
@endsection