<h1>{{ $course->title }}</h1>

<h2>Lessons</h2>

<ul>
@foreach($course->lessons as $lesson)
    <li>{{ $lesson->title }}</li>
@endforeach
</ul>