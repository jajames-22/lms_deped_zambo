@extends('dashboard.layout')

@section('sidebar_nav')
    <button onclick="loadPartial('{{ url('/dashboard/home') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 bg-[#a52a2a]/10 text-[#a52a2a] font-medium border-r-4 border-[#a52a2a] group transition-all">
        <i class="fas fa-th-large w-5 mr-3"></i>
        <span>Dashboard</span> 
    </button>

    <button onclick="loadPartial('{{ url('/dashboard/materials') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-book-open w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Materials</span> 
    </button>

    <button onclick="loadPartial('{{ url('/dashboard/assessment') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-clipboard w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Assessment</span> 
    </button>

    <button onclick="loadPartial('{{ url('/dashboard/schools') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-school w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Schools</span> 
    </button>
    
    <button onclick="loadPartial('{{ url('/dashboard/teachers') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-chalkboard-user w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Teachers</span>
    </button>
    
    <button onclick="loadPartial('{{ url('/dashboard/students') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-user-graduate w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Students</span>
    </button>
    
    <button onclick="loadPartial('{{ url('/dashboard/profile') }}', this)"
        class="nav-btn w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 transition group">
        <i class="fas fa-user w-5 mr-3 group-hover:text-[#a52a2a] transition-colors"></i>
        <span class="group-hover:text-[#a52a2a] transition-colors">Profile</span>
    </button>
@endsection