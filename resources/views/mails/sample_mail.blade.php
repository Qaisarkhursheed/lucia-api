@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi Tester,
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        If you received this email, it means you got a Sample Email.
    </x-app-email-paragraph>
@endsection
