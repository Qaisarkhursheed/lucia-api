@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $user->name }},
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Your account has been approved.<br/>
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Welcome to Lucia. We know you&rsquo;re going to love it,
    </x-app-email-paragraph>
@endsection
