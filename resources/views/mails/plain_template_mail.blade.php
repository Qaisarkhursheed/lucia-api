@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        {!! $htmlMessage !!}
    </x-app-email-paragraph>
@endsection
