@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $user->name }},
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        You&rsquo;re invited as an <strong>ADMINISTRATOR</strong> to join in the Know Experiences on Lucia&mdash;the simplest way to track and manage travel commissions.
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Please, use the password below to login.
    </x-app-email-paragraph>
    <x-app-email-big-text>
        {{ $password }}
    </x-app-email-big-text>
        <x-app-email-paragraph>
            Click the button below to get started.
        </x-app-email-paragraph>
        <x-app-email-big-button bgcolor="#BA886E" link="{{ adminAppUrl( 'login' ) }}">
            Login to your account
        </x-app-email-big-button>
        <x-app-email-paragraph>
            Welcome to Lucia. We know you&rsquo;re going to love it,
        </x-app-email-paragraph>
@endsection
