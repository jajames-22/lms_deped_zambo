<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">School Directory</h1>
            <p class="text-gray-500 text-sm">Manage registered institutions within the Zamboanga Division.</p>
        </div>

        <button onclick="loadPartial('{{ route('schools.create') }}', document.getElementById('nav-schools-btn'))"
            class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#a52a2a] text-white font-bold rounded-xl shadow-lg ...">
            <i class="fas fa-plus-circle"></i>
            <span>Add New School</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Schools</p>
                <h3 class="text-2xl font-black text-gray-900">{{ $schools->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"><i
                    class="fas fa-school text-lg"></i></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-center w-20">Logo</th>
                        <th class="px-6 py-4">School Details</th>
                        <th class="px-6 py-4">Level</th>
                        <th class="px-6 py-4">District</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($schools as $school)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div
                                    class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shadow-sm">
                                    @if ($school->logo)
                                        <img src="{{ asset('storage/' . $school->logo) }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-image text-gray-300"></i>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900 leading-tight">{{ $school->name }}</p>
                                <p class="text-xs text-gray-500 mt-1 max-w-[250px] truncate"
                                    title="{{ $school->address }}">
                                    <i class="fas fa-map-marker-alt text-[10px] mr-1"></i>
                                    {{ $school->address ?? 'No address provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                @php
                                    $badgeStyles = [
                                        'elementary' => 'bg-green-50 text-green-700 border-green-200',
                                        'highschool' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'seniorhighschool' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'integrated' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    ];
                                    $style = $badgeStyles[$school->level] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                @endphp
                                <span
                                    class="px-2 py-1 {{ $style }} text-[10px] font-bold rounded-md border uppercase tracking-tighter">
                                    {{ preg_replace('/(?<!^)([A-Z])/', ' $1', $school->level) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-gray-700">{{ $school->district->name ?? 'N/A' }}</span>
                                    <span
                                        class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $school->district->quadrant->name ?? '' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition shadow-none"
                                        title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button
                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shadow-none"
                                        title="Delete">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
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
    </div>
</div>
