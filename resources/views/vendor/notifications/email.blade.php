<x-mail::message>
<div style="text-align: center; width: 100%; 15px; background-color: #a52a2a; padding: 20px;">
    <img src="https://res.cloudinary.com/dojat3l92/image/upload/v1773727469/deped_zambo_header_doq02k.png" alt="">
</div>

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('System Alert')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
<div style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 15px;">
{{ $line }}
</div>
@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success' => 'success',
        'error' => 'error',
        default => 'primary', // We will style 'primary' as Maroon in CSS
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
@foreach ($outroLines as $line)
<p style="font-size: 14px; color: #6b7280; font-style: italic;">{{ $line }}</p>
@endforeach
</div>

{{-- Salutation --}}
<div style="margin-top: 30px; color: #374151;">
@if (! empty($salutation))
{{ $salutation }}
@else
**Regards,**<br>
{{ config('app.name') }} Team
@endif
</div>

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
<div style="font-size: 12px; color: #9ca3af;">
@lang("Trouble with the button? Copy and paste this link into your browser:")
<br>
<span class="break-all" style="color: #a52a2a;">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</div>
</x-slot:subcopy>
@endisset
</x-mail::message>