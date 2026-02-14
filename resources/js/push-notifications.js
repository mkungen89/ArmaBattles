/**
 * Push Notifications Handler
 *
 * Manages push notification subscriptions using the browser Push API.
 * Requires a service worker to be registered first (handled by pwa.js).
 */

// Check if push notifications are supported
export const isPushSupported = () => {
    return 'serviceWorker' in navigator && 'PushManager' in window;
};

// Get current notification permission
export const getPermissionStatus = () => {
    if (!isPushSupported()) {
        return 'unsupported';
    }
    return Notification.permission;
};

// Request notification permission
export const requestNotificationPermission = async () => {
    if (!isPushSupported()) {
        throw new Error('Push notifications are not supported in this browser');
    }

    const permission = await Notification.requestPermission();
    return permission;
};

// Subscribe to push notifications
export const subscribeToPush = async () => {
    try {
        // Check support
        if (!isPushSupported()) {
            throw new Error('Push notifications are not supported');
        }

        // Request permission
        const permission = await requestNotificationPermission();
        if (permission !== 'granted') {
            throw new Error('Notification permission denied');
        }

        // Get service worker registration
        const registration = await navigator.serviceWorker.ready;

        // Subscribe to push manager
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            // VAPID public key from environment
            applicationServerKey: urlBase64ToUint8Array(
                import.meta.env.VITE_VAPID_PUBLIC_KEY
            ),
        });

        // Send subscription to server
        const response = await fetch('/push-notifications/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(subscription.toJSON()),
        });

        if (!response.ok) {
            throw new Error('Failed to save subscription on server');
        }

        const data = await response.json();
        console.log('Push notification subscription saved:', data);

        return subscription;
    } catch (error) {
        console.error('Error subscribing to push notifications:', error);
        throw error;
    }
};

// Unsubscribe from push notifications
export const unsubscribeFromPush = async () => {
    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            // Unsubscribe from push manager
            await subscription.unsubscribe();

            // Remove subscription from server
            await fetch('/push-notifications/unsubscribe', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            console.log('Push notification subscription removed');
        }

        return true;
    } catch (error) {
        console.error('Error unsubscribing from push notifications:', error);
        throw error;
    }
};

// Check if user is currently subscribed
export const isSubscribed = async () => {
    try {
        if (!isPushSupported()) {
            return false;
        }

        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        return subscription !== null;
    } catch (error) {
        console.error('Error checking subscription status:', error);
        return false;
    }
};

// Helper function to convert base64 to Uint8Array
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Auto-init when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPushNotifications);
} else {
    initPushNotifications();
}

function initPushNotifications() {
    // Check if user is authenticated
    if (!window.authUser) {
        return; // Only for logged-in users
    }

    // Add event listeners for notification buttons if they exist
    const enableButton = document.getElementById('enable-push-notifications');
    const disableButton = document.getElementById('disable-push-notifications');

    if (enableButton) {
        enableButton.addEventListener('click', async () => {
            try {
                enableButton.disabled = true;
                enableButton.textContent = 'Enabling...';

                await subscribeToPush();

                enableButton.textContent = 'Enabled!';
                setTimeout(() => {
                    if (disableButton) disableButton.classList.remove('hidden');
                    enableButton.classList.add('hidden');
                }, 1000);
            } catch (error) {
                alert('Failed to enable push notifications: ' + error.message);
                enableButton.disabled = false;
                enableButton.textContent = 'Enable Notifications';
            }
        });
    }

    if (disableButton) {
        disableButton.addEventListener('click', async () => {
            try {
                disableButton.disabled = true;
                disableButton.textContent = 'Disabling...';

                await unsubscribeFromPush();

                disableButton.textContent = 'Disabled!';
                setTimeout(() => {
                    if (enableButton) enableButton.classList.remove('hidden');
                    disableButton.classList.add('hidden');
                }, 1000);
            } catch (error) {
                alert('Failed to disable push notifications: ' + error.message);
                disableButton.disabled = false;
                disableButton.textContent = 'Disable Notifications';
            }
        });
    }
}

// Make functions available globally for inline handlers
window.pushNotifications = {
    isPushSupported,
    getPermissionStatus,
    requestNotificationPermission,
    subscribeToPush,
    unsubscribeFromPush,
    isSubscribed,
};
