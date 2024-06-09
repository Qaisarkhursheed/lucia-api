@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $user->name }},
    </x-app-email-paragraph>
    <x-app-email-paragraph>
        Your Account's Reset Code:
    </x-app-email-paragraph>
    <x-app-email-big-text>
        {{ $code }}
    </x-app-email-big-text>
{{--    <x-app-email-big-button bgcolor="#BA886E">--}}
{{--        Reset Password--}}
{{--    </x-app-email-big-button>--}}
    <x-app-email-paragraph>
        <small>The code will expire in </small><strong>{{ \Illuminate\Support\Carbon::now()->diffInMinutes( $user->password_reset_token_expiry ) }}</strong><small> minutes.</small>
    </x-app-email-paragraph>
@endsection
