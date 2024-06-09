<html>
<title>Allow Notifications | Lucia</title>
<head>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&display=swap');
        @font-face {
            font-family: 'MADE Mirage Regular';
            font-style: normal;
            font-weight: normal;
            src: local('MADE Mirage Regular'), url('./made-mirage-cufonfonts-webfont/MADE-Mirage-Regular.woff') format('woff');
        }
        body {
            margin: 0;
        }
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(17, 11, 8, 0.75);
            width: 100%;
            height: 100vh;
        }
        .logo {
            font-size: 20px;
            line-height: 28px;
            color: #FBF8F3;
            font-weight: 700;
            font-family: 'Raleway';
            text-align: center;
            letter-spacing: 0.92em;
        }
        .page-title {
            font-size: 48px;
            line-height: 61px;
            color: #FFFFFF;
            font-weight: 400;
            font-family: 'MADE Mirage Regular';
            text-align: center;
            margin-top: 24px;
        }
        .page-note {
            font-size: 14px;
            line-height: 21px;
            color: #FFFFFF;
            font-weight: 400;
            font-family: 'Raleway';
            text-align: center;
            letter-spacing: 0.05em;
        }
        .arrow-img {
            position: absolute;
            left: 250px;
            top: 150px;
        }
    </style>
    <script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>
    <script>

        const beamsClient = new PusherPushNotifications.Client({
            instanceId: "{{ env( 'PUSHER_BEAM_INSTANCE' ) }}",
        });

        function initialize(){
            const tokenProvider = new PusherPushNotifications.TokenProvider({
                url: "{{ \Illuminate\Support\Str::of( env('APP_URL'))->rtrim("/") . '/pusher/beams-auth' }}",
                queryParams: {
                    token: getURLParameter('token')
                }
            })

            beamsClient
                .start()
                .then(() => beamsClient.setUserId( getURLParameter('userId'), tokenProvider))
                .then(function () {
                    console.log('User ID has been set');
                    window.location.href=getURLParameter('redirect_url');
                })
                .catch(e => {
                    // you can give more explanation here but still navigate away
                    console.error('Could not authenticate with Beams:', e);
                    window.location.href=getURLParameter('redirect_url');
                } )
                .catch(console.error);


            beamsClient.getDeviceId()
                .then((deviceId) =>
                    console.log("Successfully registered with Beams. Device ID:", deviceId)
                );
        }

        function getURLParameter (sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        }

    </script>
</head>
<body >
<div class="container">
    <img src="Arrow.png" alt="Arrow" width="13" height="173" class="arrow-img">
    <div>
        <h2 class="logo">
            LUCIA
        </h2>
        <h1 class="page-title">
            Allow Notifications
        </h1>
        <p class="page-note">
            To get notifications for new messages, click "Allow" above.
        </p>
    </div>
</div>
<script>
    var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    if( !isChrome ) window.location.href=getURLParameter('redirect_url');

    if( isChrome ) {
        initialize();
    }
</script>
</body>
</html>
