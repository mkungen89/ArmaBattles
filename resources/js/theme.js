/**
 * Theme Management (Dark/Light Mode)
 *
 * Handles theme switching with localStorage persistence and
 * respects system preferences if no theme is set.
 */

// Get initial theme
function getInitialTheme() {
    // Check localStorage first
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
        return storedTheme;
    }

    // Check system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
        return 'light';
    }

    // Default to dark
    return 'dark';
}

// Apply theme to document
function applyTheme(theme) {
    if (theme === 'light') {
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    } else {
        document.documentElement.classList.remove('light');
        document.documentElement.classList.add('dark');
    }

    // Store preference
    localStorage.setItem('theme', theme);

    // Update toggle button if it exists
    updateThemeToggleButton(theme);

    // Dispatch event for other components
    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
}

// Update toggle button state
function updateThemeToggleButton(theme) {
    const toggleButton = document.getElementById('theme-toggle');
    if (!toggleButton) return;

    const sunIcon = toggleButton.querySelector('.sun-icon');
    const moonIcon = toggleButton.querySelector('.moon-icon');

    if (theme === 'light') {
        if (sunIcon) sunIcon.classList.add('hidden');
        if (moonIcon) moonIcon.classList.remove('hidden');
    } else {
        if (sunIcon) sunIcon.classList.remove('hidden');
        if (moonIcon) moonIcon.classList.add('hidden');
    }
}

// Toggle theme
function toggleTheme() {
    const currentTheme = localStorage.getItem('theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);

    // Track theme change
    if (window.gtag) {
        gtag('event', 'theme_toggle', {
            event_category: 'engagement',
            event_label: newTheme,
        });
    }
}

// Initialize theme on page load
function initTheme() {
    const theme = getInitialTheme();
    applyTheme(theme);

    // Listen for system theme changes
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => {
            // Only auto-switch if user hasn't set a preference
            if (!localStorage.getItem('theme')) {
                applyTheme(e.matches ? 'light' : 'dark');
            }
        });
    }

    // Add click handler to toggle button
    const toggleButton = document.getElementById('theme-toggle');
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleTheme);
    }
}

// Run on DOM content loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}

// Make functions available globally
window.themeManager = {
    getTheme: () => localStorage.getItem('theme') || getInitialTheme(),
    setTheme: applyTheme,
    toggleTheme,
};
