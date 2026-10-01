const STORAGE_KEY = 'theme';

// Apply theme
function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Get system theme
function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
}

// Initialize theme
export function initTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);

    // Use stored preference if available
    if (stored === 'dark' || stored === 'light') {
        applyTheme(stored);
    } else {
        applyTheme(getSystemTheme());
    }
}

// Pick 'light', 'dark' or 'system' (forget the choice and follow the device)
export function setTheme(mode) {
    try {
        if (mode === 'system') {
            localStorage.removeItem(STORAGE_KEY);
        } else {
            localStorage.setItem(STORAGE_KEY, mode);
        }
    } catch (e) {}
    applyTheme(mode === 'system' ? getSystemTheme() : mode);
}

// Toggle theme manually (temporary)
export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    const newTheme = isDark ? 'light' : 'dark';
    localStorage.setItem(STORAGE_KEY, newTheme);
    applyTheme(newTheme);
}
