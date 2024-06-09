@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $user->name }},
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Your account has been approved.<br/>
        Please, use the credential below to login. You can change your password later.
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Your temporary password is:
    </x-app-email-paragraph>
    <x-app-email-big-text>
        {{ $password }}
    </x-app-email-big-text>
    <x-app-email-paragraph>
        Click the button below to get started.
    </x-app-email-paragraph>
    <x-app-email-big-button bgcolor="#BA886E" link="{{ uiAppUrl( 'signin' ) }}">
        Login to your account
    </x-app-email-big-button>
    <x-app-email-paragraph>
        Welcome to Lucia. We know you&rsquo;re going to love it,
    </x-app-email-paragraph>
@endsection
