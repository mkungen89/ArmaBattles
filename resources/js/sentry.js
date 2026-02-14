import * as Sentry from "@sentry/browser";

// Initialize Sentry for frontend error tracking
if (import.meta.env.VITE_SENTRY_DSN) {
    Sentry.init({
        dsn: import.meta.env.VITE_SENTRY_DSN,
        integrations: [
            Sentry.browserTracingIntegration({
                // Set sampling rate for navigation and other browser events
                tracePropagationTargets: ["localhost", /^\//],
            }),
        ],
        // Set tracesSampleRate to 1.0 to capture 100% of transactions for performance monitoring.
        // We recommend adjusting this value in production
        tracesSampleRate: parseFloat(import.meta.env.VITE_SENTRY_TRACES_SAMPLE_RATE || '0.1'),

        // Set environment
        environment: import.meta.env.VITE_APP_ENV || 'production',

        // Release tracking (automatically set during build if available)
        release: import.meta.env.VITE_SENTRY_RELEASE,

        // Filter out common errors
        ignoreErrors: [
            // Browser extensions
            'top.GLOBALS',
            'chrome-extension://',
            'moz-extension://',
            // Network errors
            'NetworkError',
            'Network request failed',
            // Random plugins
            'ResizeObserver loop limit exceeded',
        ],

        beforeSend(event, hint) {
            // Don't send errors in development
            if (import.meta.env.VITE_APP_ENV === 'local') {
                console.error('Sentry would capture:', hint.originalException || hint.syntheticException);
                return null;
            }
            return event;
        },
    });

    // Set user context if authenticated
    if (window.authUser) {
        Sentry.setUser({
            id: window.authUser.id,
            username: window.authUser.name,
            email: window.authUser.email,
        });
    }
}

// Export Sentry instance for manual error reporting
export default Sentry;
