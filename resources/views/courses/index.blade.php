c
    <div class="p-6">

        <h1 class="text-2xl font-bold mb-6 text-gray-800">Available Courses</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($courses as $course)

                <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden border">

                    {{-- Thumbnail --}}
                    <img src="{{ $course->thumbnail ?? 'https://via.placeholder.com/400x200' }}"
                        class="w-full h-40 object-cover">

                    <div class="p-4">

                        {{-- Title --}}
                        <h2 class="text-lg font-semibold text-gray-800 mb-1">
                            <a href="{{ route('courses.show', $course->id) }}" class="hover:text-[#a52a2a] transition">
                                {{ $course->title }}
                            </a>
                        </h2>

                        {{-- Instructor --}}
                        <p class="text-sm text-gray-500 mb-4">
                            Instructor:
                            {{ $course->instructor->first_name ?? 'Admin' }}
                            {{ $course->instructor->last_name ?? '' }}
                        </p>

                        @php
                            $enrolled = auth()->user()->enrollments()
                                ->where('course_id', $course->id)
                                ->exists();
                        @endphp

                        {{-- Button --}}
                        @if($enrolled)

                            <a href="{{ route('courses.show', $course->id) }}"
                                class="block text-center bg-green-500 text-white py-2 rounded-lg text-sm font-semibold">
                                Continue Course
                            </a>

                        @else

                            <form action="{{ route('courses.enroll', $course->id) }}" method="POST">
                                @csrf
                                <button
                                    class="w-full bg-[#a52a2a] hover:opacity-90 text-white py-2 rounded-lg text-sm font-semibold">
                                    Enroll
                                </button>
                            </form>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</body>

</html>