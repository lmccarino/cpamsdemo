self.importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
self.importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

firebase.initializeApp({
  apiKey: "AIzaSyAN-k9DjrfillU5XWD7dzquxHGqVUhM3EQ",
  authDomain: "cpams-d51ad.firebaseapp.com",
  projectId: "cpams-d51ad",
  storageBucket: "cpams-d51ad.appspot.com",
  messagingSenderId: "164282235570",
  appId: "1:164282235570:web:aa2cc9955939fb7938e7ab",
  measurementId: "G-GPTZK3RQDD"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    setTimeout(() => {
        // Customize notification here
        const title = `onBackgroundMessage: ${payload.notification.title}`;
        const options = {
            body: payload.notification.body,
            icon: 'assets/images/cpams_logosample2.png',
            vibrate: [200, 100, 200, 100, 200, 100, 200],
            tag: 'CPAMS v2 Notification',
            actions: [{action: "get", title: "Go now."}]
        };

        self.registration.showNotification(title, options);

        self.addEventListener('notificationclick', function (event) {
            event.notification.close();
            
            event.waitUntil(
                clients.openWindow("https://cpams2.davaocity.gov.ph/")
            );
        });
    
    }, 5000);
});

messaging.setBackgroundMessageHandler(payload => {
    console.log("[firebase-messaging-sw.js] Received background message ", payload);

    setTimeout(() => {
        // Customize notification here
        const title = `setBackgroundMessageHandler: ${payload.notification.title}`;
        const options = {
            body: payload.notification.body,
            icon: 'assets/images/cpams_logosample2.png',
            vibrate: [200, 100, 200, 100, 200, 100, 200],
            tag: 'CPAMS v2 Notification',
            actions: [{action: "get", title: "Go now."}]
        };

        self.registration.showNotification(title, options);

        self.addEventListener('notificationclick', function (event) {
            event.notification.close();
            
            event.waitUntil(
                clients.openWindow("https://cpams2.davaocity.gov.ph/")
            );
        });
    
    }, 5000);
});