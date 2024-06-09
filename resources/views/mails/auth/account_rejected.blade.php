@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $user->name }},
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        <strong style="text-decoration: underline">Your account has been waitlisted. We will reach back with any updates</strong><br/>
    </x-app-email-paragraph>
@endsection
