@component('mail::message')
# Hello {{ $student->first_name ?? 'Student' }},

You have been granted exclusive access to a new private learning module by your instructor.

**Module Title:** {{ $material->title }}
@if($material->description)
**Overview:** {{ Str::limit($material->description, 100) }}
@endif

Click the button below to log into your dashboard and access the material.

@component('mail::button', ['url' => url('/dashboard')])
Access Module Now
@endcomponent

If you did not expect this invitation, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent