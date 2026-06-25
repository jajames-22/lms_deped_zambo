<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">School Directory</h1>
            <p class="text-gray-500 text-sm">Manage registered institutions within the Zamboanga Division.</p>
        </div>

        <div class="flex-shrink-0 flex flex-wrap items-center gap-2 relative">

            <a id="btnDownloadSchoolTemplate" href="{{ route('schools.import.template', ['type' => 'schools']) }}"
                download
                class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 border border-gray-200 text-gray-600 font-bold rounded-xl shadow-sm hover:bg-gray-200 transition-all text-sm"
                title="Download Template">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Template</span>
            </a>

            <button id="importSchoolBtn" onclick="openSchoolImportModal()"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl shadow-sm hover:bg-gray-50 transition-all text-sm">
                <i class="fas fa-file-import"></i>
                <span>Import</span>
            </button>

            <button onclick="loadPartial('{{ route('schools.create') }}', document.getElementById('nav-schools-btn'))"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all">
                <i class="fas fa-plus-circle"></i>
                <span class="hidden sm:inline">Add New School</span>
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center bg-gray-200/50 p-1 rounded-xl">
            <button
                class="nav-tab px-4 py-2 text-sm font-bold rounded-lg bg-white text-[#a52a2a] shadow-sm pointer-events-none"
                data-target="tab-schools" onclick="switchSchoolTab(this)">
                Schools
            </button>
            <button
                class="nav-tab px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700 transition"
                data-target="tab-quadrants" onclick="switchSchoolTab(this)">
                Quadrants
            </button>
            <button
                class="nav-tab px-4 py-2 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700 transition"
                data-target="tab-districts" onclick="switchSchoolTab(this)">
                Districts
            </button>
        </div>

        <button onclick="toggleSchoolExportModal()"
            class="flex items-center justify-center gap-2 px-6 py-3 bg-gray-800 text-white font-bold rounded-xl shadow-sm hover:bg-gray-900 transition-all text-sm">
            <i class="fas fa-file-export"></i>
            <span class="hidden sm:inline">Generate Report</span>
        </button>
    </div>

    <div id="tab-schools" class="tab-pane block space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
                class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Schools</p>
                    <h3 class="text-2xl font-black text-gray-900" id="total-schools-count">{{ $schools->count() }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-school text-lg"></i>
                </div>
            </div>

            <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
                <div class="relative w-full">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="schoolSearchInput" placeholder="Search by name, ID, address, or district..."
                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="schoolsTable">
                    <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-center w-16">Logo</th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                                title="Sort by Name">
                                School Details <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                                title="Sort by Level">
                                Level <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 transition sortable-col select-none"
                                title="Sort by District">
                                District <i class="fas fa-sort ml-1 text-gray-300"></i>
                            </th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($schools as $school)
                            <tr class="hover:bg-gray-50/50 transition school-row">
                                <td class="px-4 py-2.5">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shadow-sm mx-auto">
                                        @if ($school->logo)
                                            <img src="{{ asset('storage/' . $school->logo) }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-image text-gray-300 text-xs"></i>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-gray-900 leading-tight school-name">
                                                {{ $school->name }}
                                            </p>
                                            <span
                                                class="bg-gray-100 text-gray-600 text-[10px] px-1.5 py-0.5 rounded font-mono border border-gray-200 school-id">
                                                {{ $school->school_id }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 max-w-[250px] truncate"
                                            title="{{ $school->address }}">
                                            <i class="fas fa-map-marker-alt text-[10px] mr-1"></i>
                                            {{ $school->address ?? 'No address provided' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-4 py-2.5">
                                    @php
                                        $badgeStyles = [
                                            'elementary' => 'bg-green-50 text-green-700 border-green-200',
                                            'highschool' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'seniorhighschool' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'integrated' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        ];
                                        $displayNames = [
                                            'elementary' => 'Elementary',
                                            'highschool' => 'High School',
                                            'seniorhighschool' => 'Senior High School',
                                            'integrated' => 'Integrated',
                                        ];
                                        $style = $badgeStyles[$school->level] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                        $name = $displayNames[$school->level] ?? ucfirst($school->level);
                                    @endphp
                                    <span
                                        class="px-2 py-1 {{ $style }} text-[10px] font-bold rounded-md border uppercase tracking-tighter school-level">
                                        {{ $name }}
                                    </span>
                                </td>

                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-semibold text-gray-700 school-district">{{ $school->district->name ?? 'N/A' }}</span>
                                        <span
                                            class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $school->district->quadrant->name ?? '' }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            onclick="loadPartial('{{ route('schools.edit', $school->id) }}', document.getElementById('nav-schools-btn'))"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                            title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button onclick="confirmDelete({{ $school->id }}, {{ $school->users_count ?? 0 }})"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none"
                                            title="Delete">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyStateRow">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-folder-open text-gray-200 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">No schools found.</p>
                                        <p class="text-gray-400 text-xs">Start by adding a new institution to the division.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="pagination-wrapper"
                class="hidden flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                    Showing <span id="page-start-info" class="font-bold text-gray-900">0</span> to <span
                        id="page-end-info" class="font-bold text-gray-900">0</span> of <span id="page-total-info"
                        class="font-bold text-gray-900">0</span> results
                </div>
                <div class="flex items-center gap-1" id="pagination-controls">
                </div>
            </div>

        </div>
    </div> <!-- Close tab-schools -->

    <!-- Quadrants Tab -->
    <div id="tab-quadrants" class="tab-pane hidden space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
                class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Quadrants</p>
                    <h3 class="text-2xl font-black text-gray-900">{{ $quadrants->count() }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-layer-group text-lg"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
                <div class="relative w-full">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="quadrantSearchInput" placeholder="Search quadrants..."
                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700"
                        oninput="filterQuadrants()">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <form id="addQuadrantForm" onsubmit="event.preventDefault(); storeQuadrant();"
                class="flex flex-col sm:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Quadrant
                        Name</label>
                    <input type="text" id="quadrantNameInput"
                        class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all text-sm outline-none"
                        required placeholder="e.g. Quadrant 1.1"
                        oninput="toggleSubmitBtn('quadrantNameInput', 'addQuadrantBtn')">
                </div>
                <button type="submit" id="addQuadrantBtn"
                    class="px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto flex items-center justify-center"
                    disabled>
                    <i class="fas fa-plus mr-2"></i> Add Quadrant
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="quadrantsTable">
                    <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Quadrant Details</th>
                            <th class="px-6 py-4 text-center">Districts Count</th>
                            <th class="px-6 py-4 text-center w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($quadrants as $quadrant)
                            <tr class="hover:bg-gray-50/50 transition quadrant-row" data-id="{{ $quadrant->id }}">
                                <td class="px-6 py-4 font-bold text-gray-900 text-sm">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-inner shrink-0">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <span class="quadrant-name-text">{{ $quadrant->name }}</span>
                                        <input type="text"
                                            class="quadrant-name-input hidden w-full max-w-xs px-3 py-1.5 bg-white border border-gray-300 focus:border-[#a52a2a] outline-none rounded-lg text-sm"
                                            value="{{ $quadrant->name }}">
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">{{ $quadrant->districts_count ?? $quadrant->districts->count() }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 quadrant-actions-default">
                                        <button onclick="editQuadrant(this)"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            onclick="confirmDeleteQuadrant({{ $quadrant->id }}, {{ $quadrant->districts_count ?? $quadrant->districts->count() }})"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="Delete">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 quadrant-actions-edit hidden">
                                        <button onclick="saveQuadrant({{ $quadrant->id }}, this)"
                                            class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded hover:bg-green-200 transition shadow-sm">Save</button>
                                        <button onclick="cancelEditQuadrant(this)"
                                            class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition shadow-sm">Cancel</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyQuadrantState">
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-layer-group text-gray-200 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">No quadrants found.</p>
                                        <p class="text-gray-400 text-xs mt-1">Add a quadrant using the form above.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Districts Tab -->
    <div id="tab-districts" class="tab-pane hidden space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
                class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between md:col-span-1">
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Districts</p>
                    <h3 class="text-2xl font-black text-gray-900">{{ $districts->count() }}</h3>
                </div>
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-lg"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center md:col-span-2">
                <div class="relative w-full">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="districtSearchInput" placeholder="Search districts..."
                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl outline-none transition-all text-sm text-gray-700"
                        oninput="filterDistricts()">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <form id="addDistrictForm" onsubmit="event.preventDefault(); storeDistrict();"
                class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div class="md:col-span-2 w-full">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Quadrant</label>
                    <select id="districtQuadrantInput"
                        class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all text-sm outline-none"
                        required onchange="checkDistrictForm()">
                        <option value="">Select Quadrant...</option>
                        @foreach($quadrants as $q)
                            <option value="{{ $q->id }}">{{ $q->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 w-full">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">District
                        Name</label>
                    <input type="text" id="districtNameInput"
                        class="w-full px-4 py-3 bg-gray-50 border border-transparent focus:border-[#a52a2a] focus:bg-white rounded-xl transition-all text-sm outline-none"
                        required placeholder="e.g. Ayala District" oninput="checkDistrictForm()">
                </div>
                <button type="submit" id="addDistrictBtn"
                    class="px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg hover:bg-red-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed w-full flex items-center justify-center h-[46px]"
                    disabled>
                    <i class="fas fa-plus mr-2"></i> Add
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="districtsTable">
                    <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">District Details</th>
                            <th class="px-6 py-4 text-center w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($quadrants as $quadrant)
                            <tr class="bg-gray-50/80 border-b border-gray-100 district-group-header">
                                <td class="px-6 py-3" colspan="2">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-layer-group text-gray-400 text-xs"></i>
                                        <span
                                            class="font-bold text-gray-700 text-xs uppercase tracking-wider quadrant-label">{{ $quadrant->name }}</span>
                                    </div>
                                </td>
                            </tr>
                            @forelse($quadrant->districts as $district)
                                <tr class="hover:bg-gray-50/50 transition district-row" data-id="{{ $district->id }}">
                                    <td class="px-6 py-3.5 pl-12 text-sm text-gray-800 font-medium w-full">
                                        <span class="district-name-text">{{ $district->name }}</span>
                                        <input type="text"
                                            class="district-name-input hidden w-full max-w-xs px-3 py-1.5 bg-white border border-gray-300 focus:border-[#a52a2a] outline-none rounded-lg text-sm"
                                            value="{{ $district->name }}">
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <div class="flex items-center justify-center gap-2 district-actions-default">
                                            <button onclick="editDistrict(this)"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button
                                                onclick="confirmDeleteDistrict({{ $district->id }}, {{ $district->schools_count ?? $district->schools->count() }})"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Delete">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                        <div class="flex items-center justify-center gap-2 district-actions-edit hidden">
                                            <button onclick="saveDistrict({{ $district->id }}, this)"
                                                class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded hover:bg-green-200 transition shadow-sm">Save</button>
                                            <button onclick="cancelEditDistrict(this)"
                                                class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition shadow-sm">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="district-empty-row">
                                    <td class="px-6 py-6 text-center bg-gray-50/30" colspan="2">
                                        <div class="flex flex-col items-center">
                                            <p class="text-gray-400 text-xs italic">No districts assigned to this quadrant yet.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @empty
                            <tr id="emptyDistrictState">
                                <td class="px-6 py-12 text-center" colspan="2">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-map-marker-alt text-gray-200 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">No districts found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="deleteQuadrantModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeDeleteQuadrantModal()"></div>
    <div
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
        <div
            class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-exclamation-triangle text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Delete Quadrant?</h3>
        <p id="deleteQuadrantWarningText" class="text-gray-500 mb-8 text-sm">This action cannot be undone.</p>

        <div id="quadrantTransferSection" class="hidden mb-6 text-left">
            <label class="block text-xs font-bold text-gray-700 mb-2">Transfer Districts To <span
                    class="text-red-500">*</span></label>
            <select id="transferQuadrantSelect"
                class="w-full border border-gray-200 bg-gray-50 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a]">
                <option value="">-- Select Destination Quadrant --</option>
                @foreach($quadrants as $q)
                    <option value="{{ $q->id }}">{{ $q->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteQuadrantModal()"
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" id="confirmDeleteQuadrantBtn" onclick="executeDeleteQuadrant()"
                class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-700 transition flex items-center justify-center">
                <span>Delete</span>
            </button>
        </div>
    </div>
</div>

<div id="deleteDistrictModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeDeleteDistrictModal()"></div>
    <div
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
        <div
            class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-exclamation-triangle text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Delete District?</h3>
        <p id="deleteDistrictWarningText" class="text-gray-500 mb-8 text-sm">This action cannot be undone. Are you sure
            you want to permanently remove this district?</p>

        <div id="districtTransferSection" class="hidden mb-6 text-left">
            <label class="block text-xs font-bold text-gray-700 mb-2">Transfer Schools To <span
                    class="text-red-500">*</span></label>
            <select id="transferDistrictSelect"
                class="w-full border border-gray-200 bg-gray-50 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a]">
                <option value="">-- Select Destination District --</option>
                @foreach($districts as $d)
                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->quadrant->name ?? 'N/A' }})</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteDistrictModal()"
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
            <button type="button" id="confirmDeleteDistrictBtn" onclick="executeDeleteDistrict()"
                class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-700 transition flex items-center justify-center">
                <span>Delete</span>
            </button>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeDeleteModal()"></div>
    <div
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all border border-gray-100 z-10 animate-fade-in-up">
        <div
            class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-exclamation-triangle text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Delete School?</h3>
        <p id="deleteSchoolWarningText" class="text-gray-500 mb-6 text-sm">This action cannot be undone. Are you sure
            you want to permanently remove
            this institution?</p>

        <div id="schoolTransferSection" class="hidden mb-6 text-left">
            <label class="block text-xs font-bold text-gray-700 mb-2">Transfer Users To <span
                    class="text-red-500">*</span></label>
            <select id="transferSchoolSelect"
                class="w-full border border-gray-200 bg-gray-50 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-[#a52a2a]/20 focus:border-[#a52a2a]">
                <option value="">-- Select Destination School --</option>
                @foreach($schools as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn"
                class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-900/20 hover:bg-red-700 transition flex items-center justify-center">
                <span>Delete</span>
            </button>
        </div>
    </div>
</div>

{{-- EXPORT MODAL --}}
<div id="schoolExportModal"
    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[110] hidden flex items-center justify-center opacity-0 transition-opacity duration-300 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 md:p-8 transform scale-95 transition-transform duration-300 border border-gray-100"
        id="schoolExportModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Export School Report</h3>
            <button type="button" onclick="toggleSchoolExportModal()"
                class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"><i
                    class="fas fa-times text-lg"></i></button>
        </div>

        <form action="{{ route('schools.report') }}" method="GET" target="_blank">
            <div class="mb-8">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Select Lists to Include</p>
                <div class="space-y-2">
                    <label
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" id="export_list_all_schools" name="list_type" value="all" checked
                            class="w-5 h-5 text-[#a52a2a] border-gray-300 focus:ring-[#a52a2a]">
                        <span class="text-gray-700 font-bold">All Lists</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-orange-50 transition-colors">
                        <input type="checkbox" name="lists[]" value="schools"
                            class="export-list-cb-school w-5 h-5 text-orange-600 rounded border-gray-300 focus:ring-orange-600">
                        <span class="text-gray-700 font-bold">List of Schools</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 transition-colors">
                        <input type="checkbox" name="lists[]" value="quadrants"
                            class="export-list-cb-school w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-600">
                        <span class="text-gray-700 font-bold">List of Quadrants</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 transition-colors">
                        <input type="checkbox" name="lists[]" value="districts"
                            class="export-list-cb-school w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-600">
                        <span class="text-gray-700 font-bold">List of Districts</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="toggleSchoolExportModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 rounded-xl font-bold border-0 transition-colors">Cancel</button>
                <button type="submit" name="action" value="print" onclick="setTimeout(toggleSchoolExportModal, 500)"
                    class="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-xl font-bold border-0 transition-colors flex items-center justify-center gap-2"><i
                        class="fas fa-print"></i> Print</button>
                <button type="submit" name="action" value="download" onclick="setTimeout(toggleSchoolExportModal, 500)"
                    class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-xl font-bold border-0 transition-colors flex items-center justify-center gap-2"><i
                        class="fas fa-file-pdf"></i> PDF</button>
            </div>
        </form>
    </div>
</div>

{{-- IMPORT SCHOOL MODAL --}}
<div id="importSchoolModal"
    class="fixed inset-0 z-[9999] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeSchoolImportModal()"></div>
    <div id="importSchoolBox"
        class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl relative z-10 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-900">Import Data</h3>
            <button onclick="closeSchoolImportModal()" class="text-gray-400 hover:text-gray-600 transition"><i
                    class="fas fa-times"></i></button>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Import Type</label>
            <div class="grid grid-cols-3 gap-2">
                <label
                    class="flex items-center justify-center gap-2 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-[#a52a2a] has-[:checked]:bg-red-50/50 has-[:checked]:text-[#a52a2a]">
                    <input type="radio" name="import_type" value="schools" checked class="hidden"
                        onchange="updateImportModalUI()">
                    <span class="text-sm font-bold">Schools</span>
                </label>
                <label
                    class="flex items-center justify-center gap-2 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-[#a52a2a] has-[:checked]:bg-red-50/50 has-[:checked]:text-[#a52a2a]">
                    <input type="radio" name="import_type" value="quadrants" class="hidden"
                        onchange="updateImportModalUI()">
                    <span class="text-sm font-bold">Quadrants</span>
                </label>
                <label
                    class="flex items-center justify-center gap-2 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-[#a52a2a] has-[:checked]:bg-red-50/50 has-[:checked]:text-[#a52a2a]">
                    <input type="radio" name="import_type" value="districts" class="hidden"
                        onchange="updateImportModalUI()">
                    <span class="text-sm font-bold">Districts</span>
                </label>
            </div>
        </div>

        <div id="importSchoolInstructions"
            class="mb-5 p-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-gray-600 leading-relaxed space-y-2">
            <p class="font-bold text-gray-800 flex items-center justify-between">
                <span><i class="fas fa-info-circle text-[#a52a2a] mr-1"></i> File Requirements</span>
                <a id="modalDownloadTemplateLink" href="{{ route('schools.import.template', ['type' => 'schools']) }}"
                    class="text-xs text-blue-600 hover:underline">Download Template</a>
            </p>
            <ul class="list-disc list-inside space-y-1 text-xs text-gray-500" id="importRequirementsList">
                <li>Columns: school_id, official_name, level, address, quadrant, district</li>
                <li>Duplicates by School ID or Name will be skipped</li>
                <li>Quadrants and Districts will be auto-created if missing</li>
            </ul>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select File</label>
            <input type="file" id="school-file-input" accept=".csv, .xlsx, .xls" class="block w-full text-sm text-gray-500
                       file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                       file:text-sm file:font-bold file:bg-[#a52a2a]/10 file:text-[#a52a2a]
                       hover:file:bg-[#a52a2a]/20 transition cursor-pointer">
        </div>

        <button id="submitSchoolImportBtn" onclick="submitSchoolImport()"
            class="w-full py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black transition flex justify-center items-center gap-2 shadow-lg shadow-gray-900/20">
            <i class="fas fa-upload"></i> Upload & Import
        </button>
    </div>
</div>

<div id="importMessageModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 transition-opacity duration-300" onclick="closeImportMessageModal()">
    </div>
    <div id="importMessageModalBox"
        class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 z-10">
        <div id="importModalIconBox"
            class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner bg-gray-50 text-gray-500">
            <i id="importModalIcon" class="fas fa-info-circle text-4xl"></i>
        </div>
        <h3 id="importModalTitle" class="text-2xl font-black text-gray-900 mb-2">Notice</h3>
        <p id="importModalMessage" class="text-gray-500 mb-8 text-sm whitespace-pre-line text-left">Message content goes
            here.</p>
        <button type="button" onclick="closeImportMessageModal()" id="importModalBtn"
            class="w-full px-4 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition">
            Okay
        </button>
    </div>
</div>

<script>
    // --- EXPORT MODAL LOGIC ---
    (function () {
        var listAllRadio = document.getElementById('export_list_all_schools');
        var listCheckboxes = document.querySelectorAll('.export-list-cb-school');

        if (listAllRadio && listCheckboxes.length > 0) {
            listAllRadio.addEventListener('change', function () {
                if (this.checked) {
                    listCheckboxes.forEach(cb => cb.checked = false);
                }
            });

            listCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    var checkedCount = document.querySelectorAll('.export-list-cb-school:checked').length;
                    if (checkedCount === 3) {
                        listAllRadio.checked = true;
                        listCheckboxes.forEach(c => c.checked = false);
                    } else if (checkedCount > 0) {
                        listAllRadio.checked = false;
                    } else {
                        listAllRadio.checked = true;
                    }
                });
            });
        }
    })();

    function toggleSchoolExportModal() {
        var modal = document.getElementById('schoolExportModal');
        var content = document.getElementById('schoolExportModalContent');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        } else {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    }

    function switchSchoolTab(btn) {
        document.querySelectorAll('.nav-tab').forEach(t => {
            t.classList.remove('bg-white', 'text-[#a52a2a]', 'shadow-sm', 'pointer-events-none');
            t.classList.add('text-gray-500');
        });
        btn.classList.add('bg-white', 'text-[#a52a2a]', 'shadow-sm', 'pointer-events-none');
        btn.classList.remove('text-gray-500');

        document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
        document.getElementById(btn.dataset.target).classList.remove('hidden');

        // Update external download template button based on active tab
        var activeTabStr = btn.dataset.target.replace('tab-', '');
        var btnDownload = document.getElementById('btnDownloadSchoolTemplate');
        if (btnDownload) {
            btnDownload.href = `{{ route('schools.import.template') }}?type=${activeTabStr}`;
        }
    }

    function toggleSubmitBtn(inputId, btnId) {
        const input = document.getElementById(inputId);
        const btn = document.getElementById(btnId);
        btn.disabled = input.value.trim() === '';
    }

    function checkDistrictForm() {
        const quadrant = document.getElementById('districtQuadrantInput').value;
        const name = document.getElementById('districtNameInput').value.trim();
        document.getElementById('addDistrictBtn').disabled = (quadrant === '' || name === '');
    }

    // --- IMPORT MODAL LOGIC ---
    function openSchoolImportModal() {
        var modal = document.getElementById('importSchoolModal');
        var box = document.getElementById('importSchoolBox');

        // Reset file input
        document.getElementById('school-file-input').value = '';

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            box.classList.remove('scale-95');
        }, 10);
    }

    function closeSchoolImportModal() {
        var modal = document.getElementById('importSchoolModal');
        var box = document.getElementById('importSchoolBox');

        modal.classList.add('opacity-0');
        box.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function updateImportModalUI() {
        var type = document.querySelector('input[name="import_type"]:checked').value;
        var list = document.getElementById('importRequirementsList');
        var link = document.getElementById('modalDownloadTemplateLink');

        link.href = `{{ route('schools.import.template') }}?type=${type}`;

        if (type === 'schools') {
            list.innerHTML = `
                <li>Columns: school_id, official_name, level, address, quadrant, district</li>
                <li>Duplicates by School ID or Name will be skipped</li>
                <li>Quadrants and Districts will be auto-created if missing</li>
            `;
        } else if (type === 'quadrants') {
            list.innerHTML = `
                <li>Columns: quadrant_name</li>
                <li>Duplicates by Quadrant Name will be skipped</li>
            `;
        } else if (type === 'districts') {
            list.innerHTML = `
                <li>Columns: district_name, quadrant_name</li>
                <li>Quadrants will be auto-created if missing</li>
                <li>Duplicates by District Name within the same Quadrant will be skipped</li>
            `;
        }
    }

    function submitSchoolImport() {
        var fileInput = document.getElementById('school-file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            showSnackbar("Please select a file to import.", "error");
            return;
        }

        var type = document.querySelector('input[name="import_type"]:checked').value;
        var formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('type', type);

        var btn = document.getElementById('submitSchoolImportBtn');
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch("{{ route('schools.import') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(async response => {
                var data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Import failed.');
                return data;
            })
            .then(data => {
                closeSchoolImportModal();
                showImportMessageModal('Import Summary', data.message, 'success');
            })
            .catch(error => {
                closeSchoolImportModal();
                showImportMessageModal('Import Error', error.message, 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

    function showImportMessageModal(title, message, type) {
        var modal = document.getElementById('importMessageModal');
        var box = document.getElementById('importMessageModalBox');
        var icon = document.getElementById('importModalIcon');
        var iconBox = document.getElementById('importModalIconBox');

        document.getElementById('importModalTitle').innerText = title;
        document.getElementById('importModalMessage').innerHTML = message;

        if (type === 'success') {
            iconBox.className = "w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner bg-green-50 text-green-500";
            icon.className = "fas fa-check-circle text-4xl";
            document.getElementById('importModalBtn').className = "w-full px-4 py-3 bg-green-600 text-white font-bold rounded-xl shadow-md hover:bg-green-700 transition";
        } else {
            iconBox.className = "w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner bg-red-50 text-red-500";
            icon.className = "fas fa-exclamation-triangle text-4xl";
            document.getElementById('importModalBtn').className = "w-full px-4 py-3 bg-red-600 text-white font-bold rounded-xl shadow-md hover:bg-red-700 transition";
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeImportMessageModal() {
        var modal = document.getElementById('importMessageModal');
        var box = document.getElementById('importMessageModalBox');

        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
        }, 300);
    }

    function storeQuadrant() {
        const name = document.getElementById('quadrantNameInput').value.trim();
        const btn = document.getElementById('addQuadrantBtn');
        const originalContent = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

        fetch('{{ route('quadrants.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name })
        })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Error saving quadrant.');
                }
                return response.json();
            })
            .then(data => {
                loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
            })
            .catch(error => {
                alert(error.message);
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
    }

    function storeDistrict() {
        const quadrant_id = document.getElementById('districtQuadrantInput').value;
        const name = document.getElementById('districtNameInput').value.trim();
        const btn = document.getElementById('addDistrictBtn');
        const originalContent = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

        fetch('{{ route('districts.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, quadrant_id: quadrant_id })
        })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Error saving district.');
                }
                return response.json();
            })
            .then(data => {
                loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
            })
            .catch(error => {
                alert(error.message);
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
    }

    // --- DELETE LOGIC ---
    var deleteSchoolId = null;

    function confirmDelete(id, userCount = 0) {
        deleteSchoolId = id;
        const warningText = document.getElementById('deleteSchoolWarningText');
        const transferSection = document.getElementById('schoolTransferSection');

        if (warningText) {
            if (userCount > 0) {
                warningText.innerHTML = `This school currently has <b class="text-red-600">${userCount} user(s)</b> assigned to it.<br><br>Before deleting the school, transfer all users to another school.`;
                if (transferSection) transferSection.classList.remove('hidden');

                // Hide current school from dropdown
                const select = document.getElementById('transferSchoolSelect');
                if (select) {
                    select.value = '';
                    Array.from(select.options).forEach(opt => {
                        if (opt.value == id) opt.style.display = 'none';
                        else opt.style.display = '';
                    });
                }
            } else {
                warningText.innerHTML = `This action cannot be undone. Are you sure you want to permanently remove this institution?`;
                if (transferSection) transferSection.classList.add('hidden');
            }
        }
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteSchoolId = null;
        document.getElementById('deleteModal').classList.add('hidden');
    }

    var confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        var newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', function () {
            if (!deleteSchoolId) return;

            const transferSelect = document.getElementById('transferSchoolSelect');
            const transferSection = document.getElementById('schoolTransferSection');
            let transferToId = null;

            if (transferSection && !transferSection.classList.contains('hidden')) {
                transferToId = transferSelect.value;
                if (!transferToId) {
                    showSnackbar("Please select a destination school for the users.", 'error');
                    return;
                }
            }

            var btnText = this.querySelector('span');
            var originalText = btnText.textContent;

            this.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            let requestBody = {};
            if (transferToId) requestBody.transfer_to_school_id = transferToId;

            fetch(`/dashboard/schools/${deleteSchoolId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestBody)
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || data.success === false) {
                        throw new Error(data.message || 'Network response was not ok');
                    }
                    return data;
                })
                .then(data => {
                    closeDeleteModal();
                    loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
                })
                .catch(error => {
                    console.error("Deletion error:", error);
                    showSnackbar(error.message || "An error occurred while trying to delete the school.", 'error');
                })
                .finally(() => {
                    this.disabled = false;
                    btnText.textContent = originalText;
                });
        });
    }

    // --- PAGINATION, SEARCH & SORT LOGIC ---
    var currentPage = 1;
    var pageSize = 20; // Set to 20 maximum items per page
    var allSchoolRows = [];
    var currentFilteredRows = [];

    // Initialize table data on load
    setTimeout(function () {
        allSchoolRows = Array.from(document.querySelectorAll('.school-row'));
        currentFilteredRows = [...allSchoolRows];
        applyPagination();
    }, 50);

    function applyPagination() {
        var tbody = document.querySelector('#schoolsTable tbody');
        var emptyState = document.getElementById('emptyStateRow');
        var paginationWrapper = document.getElementById('pagination-wrapper');

        // Hide all rows globally first
        allSchoolRows.forEach(row => row.style.display = 'none');

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

        // Show and re-append current rows (to enforce sorted order automatically)
        for (var i = startIdx; i < endIdx; i++) {
            currentFilteredRows[i].style.display = '';
            tbody.appendChild(currentFilteredRows[i]);
        }

        // Update Text
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

    // --- SEARCH LOGIC OVERRIDE ---
    var searchInput = document.getElementById('schoolSearchInput');
    if (searchInput) {
        var newSearchInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newSearchInput, searchInput);

        newSearchInput.addEventListener('input', function () {
            var filter = this.value.toLowerCase();

            currentFilteredRows = allSchoolRows.filter(function (row) {
                return row.textContent.toLowerCase().includes(filter);
            });

            var counterElement = document.getElementById('total-schools-count');
            if (counterElement) {
                counterElement.textContent = currentFilteredRows.length;
            }

            currentPage = 1; // Reset to page 1 on search
            applyPagination();
        });
    }

    // --- SORTING LOGIC OVERRIDE ---
    var sortableHeaders = document.querySelectorAll('.sortable-col');
    sortableHeaders.forEach(function (header) {
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', function () {
            var colIndex = Array.from(newHeader.parentNode.children).indexOf(newHeader);
            var isAsc = newHeader.classList.contains('asc');

            // Reset UI
            document.querySelectorAll('.sortable-col i').forEach(function (icon) {
                icon.className = 'fas fa-sort ml-1 text-gray-300';
            });
            document.querySelectorAll('.sortable-col').forEach(function (h) {
                h.classList.remove('asc', 'desc');
            });

            // Toggle Sort
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

            // Sort the filtered array
            currentFilteredRows.sort(function (a, b) {
                var aText = a.children[colIndex].textContent.trim().toLowerCase();
                var bText = b.children[colIndex].textContent.trim().toLowerCase();

                if (aText < bText) return -1 * multiplier;
                if (aText > bText) return 1 * multiplier;
                return 0;
            });

            currentPage = 1; // Reset to page 1 on sort
            applyPagination();
        });
    });
    // --- QUADRANT ACTIONS ---
    function filterQuadrants() {
        const filter = document.getElementById('quadrantSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.quadrant-row');
        let count = 0;
        rows.forEach(row => {
            const text = row.querySelector('.quadrant-name-text').textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }
        });
        const emptyState = document.getElementById('emptyQuadrantState');
        if (emptyState) {
            emptyState.style.display = count === 0 ? '' : 'none';
        }
    }

    function editQuadrant(btn) {
        const row = btn.closest('.quadrant-row');
        row.querySelector('.quadrant-name-text').classList.add('hidden');
        row.querySelector('.quadrant-name-input').classList.remove('hidden');
        row.querySelector('.quadrant-actions-default').classList.add('hidden');
        row.querySelector('.quadrant-actions-edit').classList.remove('hidden');
    }

    function cancelEditQuadrant(btn) {
        const row = btn.closest('.quadrant-row');
        row.querySelector('.quadrant-name-text').classList.remove('hidden');
        row.querySelector('.quadrant-name-input').classList.add('hidden');
        row.querySelector('.quadrant-actions-default').classList.remove('hidden');
        row.querySelector('.quadrant-actions-edit').classList.add('hidden');
        // Reset input value
        row.querySelector('.quadrant-name-input').value = row.querySelector('.quadrant-name-text').textContent;
    }

    function saveQuadrant(id, btn) {
        const row = btn.closest('.quadrant-row');
        const name = row.querySelector('.quadrant-name-input').value.trim();
        const originalContent = btn.innerHTML;

        if (!name) return;

        btn.disabled = true;
        btn.innerHTML = '...';

        fetch(`/dashboard/quadrants/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name })
        })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Error updating quadrant.');
                }
                return response.json();
            })
            .then(data => {
                loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
            })
            .catch(error => {
                alert(error.message);
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
    }

    let deleteQuadrantId = null;
    function confirmDeleteQuadrant(id, districtsCount = 0) {
        deleteQuadrantId = id;
        const warningText = document.getElementById('deleteQuadrantWarningText');
        const transferSection = document.getElementById('quadrantTransferSection');

        if (warningText) {
            if (districtsCount > 0) {
                warningText.innerHTML = `This quadrant currently has <b class="text-red-600">${districtsCount} district(s)</b>.<br><br>Before deleting, transfer all districts to another quadrant.`;
                if (transferSection) transferSection.classList.remove('hidden');

                const select = document.getElementById('transferQuadrantSelect');
                if (select) {
                    select.value = '';
                    Array.from(select.options).forEach(opt => {
                        if (opt.value == id) opt.style.display = 'none';
                        else opt.style.display = '';
                    });
                }
            } else {
                warningText.innerHTML = `This action cannot be undone. Are you sure you want to permanently remove this quadrant?`;
                if (transferSection) transferSection.classList.add('hidden');
            }
        }
        document.getElementById('deleteQuadrantModal').classList.remove('hidden');
    }
    function closeDeleteQuadrantModal() {
        deleteQuadrantId = null;
        document.getElementById('deleteQuadrantModal').classList.add('hidden');
    }

    function executeDeleteQuadrant() {
        if (!deleteQuadrantId) return;

        const transferSelect = document.getElementById('transferQuadrantSelect');
        const transferSection = document.getElementById('quadrantTransferSection');
        let transferToId = null;

        if (transferSection && !transferSection.classList.contains('hidden')) {
            transferToId = transferSelect.value;
            if (!transferToId) {
                showSnackbar("Please select a destination quadrant.", 'error');
                return;
            }
        }

        const btn = document.getElementById('confirmDeleteQuadrantBtn');
        const btnText = btn.querySelector('span');
        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        let requestBody = {};
        if (transferToId) requestBody.transfer_to_quadrant_id = transferToId;

        fetch(`/dashboard/quadrants/${deleteQuadrantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
            .then(async response => {
                if (!response.ok) throw new Error('Error deleting quadrant.');
                return response.json();
            })
            .then(data => {
                closeDeleteQuadrantModal();
                loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
            })
            .catch(error => {
                alert(error.message);
                btn.disabled = false;
                btnText.textContent = originalText;
            });
    }

    // --- DISTRICT ACTIONS ---
    function filterDistricts() {
        const filter = document.getElementById('districtSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.district-row');
        let count = 0;
        rows.forEach(row => {
            const text = row.querySelector('.district-name-text').textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }
        });
        const emptyState = document.getElementById('emptyDistrictState');
        if (emptyState) {
            emptyState.style.display = count === 0 ? '' : 'none';
        }
    }

    function editDistrict(btn) {
        const row = btn.closest('.district-row');
        row.querySelector('.district-name-text').classList.add('hidden');
        row.querySelector('.district-name-input').classList.remove('hidden');
        row.querySelector('.district-actions-default').classList.add('hidden');
        row.querySelector('.district-actions-edit').classList.remove('hidden');
    }

    function cancelEditDistrict(btn) {
        const row = btn.closest('.district-row');
        row.querySelector('.district-name-text').classList.remove('hidden');
        row.querySelector('.district-name-input').classList.add('hidden');
        row.querySelector('.district-actions-default').classList.remove('hidden');
        row.querySelector('.district-actions-edit').classList.add('hidden');
        row.querySelector('.district-name-input').value = row.querySelector('.district-name-text').textContent;
    }

    function saveDistrict(id, btn) {
        const row = btn.closest('.district-row');
        const name = row.querySelector('.district-name-input').value.trim();
        const originalContent = btn.innerHTML;

        if (!name) return;

        btn.disabled = true;
        btn.innerHTML = '...';

        fetch(`/dashboard/districts/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name })
        })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Error updating district.');
                }
                return response.json();
            })
            .then(data => {
                loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
            })
            .catch(error => {
                alert(error.message);
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
    }

    let deleteDistrictId = null;
    function confirmDeleteDistrict(id, schoolsCount = 0) {
        deleteDistrictId = id;
        const warningText = document.getElementById('deleteDistrictWarningText');
        const transferSection = document.getElementById('districtTransferSection');

        if (warningText) {
            if (schoolsCount > 0) {
                warningText.innerHTML = `This district currently has <b class="text-red-600">${schoolsCount} school(s)</b>.<br><br>Before deleting, transfer all schools to another district.`;
                if (transferSection) transferSection.classList.remove('hidden');

                const select = document.getElementById('transferDistrictSelect');
                if (select) {
                    select.value = '';
                    Array.from(select.options).forEach(opt => {
                        if (opt.value == id) opt.style.display = 'none';
                        else opt.style.display = '';
                    });
                }
            } else {
                warningText.innerHTML = `This action cannot be undone. Are you sure you want to permanently remove this district?`;
                if (transferSection) transferSection.classList.add('hidden');
            }
        }
        document.getElementById('deleteDistrictModal').classList.remove('hidden');
    }
    function closeDeleteDistrictModal() {
        deleteDistrictId = null;
        document.getElementById('deleteDistrictModal').classList.add('hidden');
    }

    function executeDeleteDistrict() {
        if (!deleteDistrictId) return;

        const transferSelect = document.getElementById('transferDistrictSelect');
        const transferSection = document.getElementById('districtTransferSection');
        let transferToId = null;

        if (transferSection && !transferSection.classList.contains('hidden')) {
            transferToId = transferSelect.value;
            if (!transferToId) {
                showSnackbar("Please select a destination district.", 'error');
                return;
            }
        }

        const btn = document.getElementById('confirmDeleteDistrictBtn');
        const btnText = btn.querySelector('span');
        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        let requestBody = {};
        if (transferToId) requestBody.transfer_to_district_id = transferToId;

        fetch(`/dashboard/districts/${deleteDistrictId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
            .then(async response => {
                if (!response.ok) throw new Error('Error deleting district.');
                return response.json();
            })
            .then(data => {
                closeDeleteDistrictModal();
                loadPartial('{{ route('schools') }}', document.getElementById('nav-schools-btn'));
            })
            .catch(error => {
                alert(error.message);
                btn.disabled = false;
                btnText.textContent = originalText;
            });
    }
</script>