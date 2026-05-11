const STORAGE_KEY = 'theme';

// apply theme
function applyTheme(theme) {

    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

}

// get system theme
function getSystemTheme() {

    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';

}

// init theme
export function initTheme() {

    const stored = localStorage.getItem(STORAGE_KEY);

    // use stored only as initial preference
    if (stored === 'dark' || stored === 'light') {

        applyTheme(stored);

    } else {

        applyTheme(getSystemTheme());

    }

}

// toggle manual (temporary)
export function toggleTheme() {

    const isDark = document.documentElement.classList.contains('dark');

    const newTheme = isDark ? 'light' : 'dark';

    localStorage.setItem(STORAGE_KEY, newTheme);

    applyTheme(newTheme);

}

export function watchSystemTheme() {
    // REMOVED: No longer auto-overrides user preference
    // User choice now persists until manually changed
}
