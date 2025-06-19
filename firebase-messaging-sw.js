importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyAk7MDFEDq0sZTMU9GQ1OQ7x5TtaolSFw0",
    authDomain: "drivevalley-fdb7f.firebaseapp.com",
    projectId: "drivevalley-fdb7f",
    storageBucket: "drivevalley-fdb7f.appspot.com",
    messagingSenderId: "76471554747",
    appId: "1:76471554747:web:63392218660695de6d0f9e",
    measurementId: "76471554747"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});