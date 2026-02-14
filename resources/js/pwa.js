import { registerSW } from 'virtual:pwa-register';

// Service Worker Registration
const updateSW = registerSW({
    immediate: true,
    onNeedRefresh() {
        if (confirm('New content available. Reload to update?')) {
            updateSW(true);
        }
    },
    onOfflineReady() {
        console.log('App ready to work offline');
        showOfflineNotification();
    },
    onRegistered(registration) {
        console.log('Service Worker registered:', registration);
    },
    onRegisterError(error) {
        console.error('Service Worker registration error:', error);
    },
});

// Show offline ready notification
function showOfflineNotification() {
    if (window.Notification && Notification.permission === 'granted') {
        new Notification('ArmaBattles is ready!', {
            body: 'You can now view your stats offline',
            icon: '/images/icons/icon-192x192.png',
            badge: '/images/icons/icon-96x96.png',
        });
    }
}

// Install prompt handling
let deferredPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();

    // Stash the event so it can be triggered later
    deferredPrompt = e;

    // Show install button/banner
    showInstallPromotion();
});

window.addEventListener('appinstalled', () => {
    console.log('PWA installed successfully');
    deferredPrompt = null;
    hideInstallPromotion();

    // Track installation
    if (window.gtag) {
        gtag('event', 'pwa_install', {
            event_category: 'engagement',
            event_label: 'PWA Installed',
        });
    }
});

// Show install promotion banner
function showInstallPromotion() {
    const installBanner = document.getElementById('pwa-install-banner');
    if (installBanner) {
        installBanner.classList.remove('hidden');
    }
}

// Hide install promotion banner
function hideInstallPromotion() {
    const installBanner = document.getElementById('pwa-install-banner');
    if (installBanner) {
        installBanner.classList.add('hidden');
    }
}

// Install button click handler
window.installPWA = async function() {
    if (!deferredPrompt) {
        return;
    }

    // Show the install prompt
    deferredPrompt.prompt();

    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;

    console.log(`User response to the install prompt: ${outcome}`);

    // Track the outcome
    if (window.gtag) {
        gtag('event', 'pwa_install_prompt', {
            event_category: 'engagement',
            event_label: outcome,
        });
    }

    // Clear the deferredPrompt
    deferredPrompt = null;

    // Hide the promotion
    hideInstallPromotion();
};

// Dismiss install banner
window.dismissInstallBanner = function() {
    hideInstallPromotion();

    // Remember dismissal in localStorage
    localStorage.setItem('pwa_install_dismissed', Date.now());
};

// Check if we should show the install banner
window.addEventListener('DOMContentLoaded', () => {
    // Don't show if already dismissed in the last 7 days
    const dismissedAt = localStorage.getItem('pwa_install_dismissed');
    if (dismissedAt) {
        const daysSinceDismissed = (Date.now() - parseInt(dismissedAt)) / (1000 * 60 * 60 * 24);
        if (daysSinceDismissed < 7) {
            hideInstallPromotion();
        }
    }

    // Don't show if already in standalone mode (installed)
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        hideInstallPromotion();
    }
});

// Check for updates periodically (every hour)
if (updateSW) {
    setInterval(() => {
        updateSW(true);
    }, 60 * 60 * 1000);
}
