@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi,
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        You&rsquo;re invited to join Lucia&mdash;the simplest way to track and manage travel commissions.
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Please, use the registration access code below to create an account.
    </x-app-email-paragraph>
    <x-app-email-big-text>
        {{ $code }}
    </x-app-email-big-text>
    <x-app-email-paragraph>
        Click the button below to get started.
    </x-app-email-paragraph>
    <x-app-email-big-button bgcolor="#BA886E" link="{{ uiAppUrl( 'access-code?access_code=' . $code ) }}">
        Create an account
    </x-app-email-big-button>
    <x-app-email-paragraph>
        Welcome to Lucia. We know you&rsquo;re going to love it,
    </x-app-email-paragraph>
@endsection
