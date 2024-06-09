{{--npm install firebase--}}

{{--// Import the functions you need from the SDKs you need--}}
{{--import { initializeApp } from "firebase/app";--}}
{{--import { getAnalytics } from "firebase/analytics";--}}
{{--// TODO: Add SDKs for Firebase products that you want to use--}}
{{--// https://firebase.google.com/docs/web/setup#available-libraries--}}

{{--// Your web app's Firebase configuration--}}
{{--// For Firebase JS SDK v7.20.0 and later, measurementId is optional--}}
{{--const firebaseConfig = {--}}
{{--apiKey: "",--}}
{{--authDomain: "lucia-333911.firebaseapp.com",--}}
{{--projectId: "lucia-333911",--}}
{{--storageBucket: "lucia-333911.appspot.com",--}}
{{--messagingSenderId: "815119771948",--}}
{{--appId: "1:815119771948:web:5aa7285d810188dc44e489",--}}
{{--measurementId: "G-MD2M1B2SPN"--}}
{{--};--}}

{{--// Initialize Firebase--}}
{{--const app = initializeApp(firebaseConfig);--}}
{{--const analytics = getAnalytics(app);--}}


<script type="module">
    // Import the functions you need from the SDKs you need
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.9.0/firebase-app.js'
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/9.9.0/firebase-analytics.js";
    import { getMessaging } from "https://www.gstatic.com/firebasejs/9.9.0/firebase-messaging.js";

    // TODO: Add SDKs for Firebase products that you want to use
    // https://firebase.google.com/docs/web/setup#available-libraries

    // Your web app's Firebase configuration
    // For Firebase JS SDK v7.20.0 and later, measurementId is optional
    const firebaseConfig = {
        apiKey: "",
        authDomain: "lucia-333911.firebaseapp.com",
        projectId: "lucia-333911",
        storageBucket: "lucia-333911.appspot.com",
        messagingSenderId: "815119771948",
        appId: "1:815119771948:web:5aa7285d810188dc44e489",
        measurementId: "G-MD2M1B2SPN"
    };

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);

    // Retrieve an instance of Firebase Messaging so that it can handle background
    // messages.
    const messaging = getMessaging(app);

</script>
