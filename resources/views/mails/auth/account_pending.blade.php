@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $user->name }},
    </x-app-email-paragraph>
    <x-app-email-paragraph>
       <strong style="text-decoration: underline">Your account approval is pending.</strong><br/>
        Please, know that we are moving fast in processing your account.
    </x-app-email-paragraph>
@endsection
