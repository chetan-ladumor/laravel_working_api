@component('mail::message')
# Hello {{$user->name}}

You have changed your email.Please verify this new email using this button:

The body of your message.

@component('mail::button', ['url' => route('verify',$user->verification_token)])
Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
