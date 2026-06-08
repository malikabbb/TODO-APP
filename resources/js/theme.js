const THEME_STORAGE_KEY = 'theme';
const VALID_THEMES = ['light', 'dark'];

const isValidTheme = (theme) => VALID_THEMES.includes(theme);

const getStoredTheme = () => {
    try {
        const storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY);

        return isValidTheme(storedTheme) ? storedTheme : null;
    } catch (error) {
        return null;
    }
};

const getSystemTheme = () => {
    if (window.matchMedia?.('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }

    return 'light';
};

const resolveTheme = () => getStoredTheme() || getSystemTheme();

const applyTheme = (theme) => {
    const nextTheme = isValidTheme(theme) ? theme : resolveTheme();

    document.documentElement.dataset.theme = nextTheme;
    updateThemeControls(nextTheme);

    return nextTheme;
};

const persistTheme = (theme) => {
    try {
        window.localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (error) {
        // Theme preference is non-critical if storage is unavailable.
    }
};

const updateThemeControls = (theme) => {
    document.querySelectorAll('[data-theme-toggle]').forEach((control) => {
        const nextTheme = theme === 'dark' ? 'light' : 'dark';

        control.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
        control.setAttribute('title', `Switch to ${nextTheme} mode`);
        control.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
    });
};

const toggleTheme = () => {
    const currentTheme = document.documentElement.dataset.theme || resolveTheme();
    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

    persistTheme(nextTheme);
    applyTheme(nextTheme);
    window.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme: nextTheme } }));

    return nextTheme;
};

const initTheme = () => {
    applyTheme(resolveTheme());

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-theme-toggle]');

        if (!toggle) {
            return;
        }

        event.preventDefault();
        toggleTheme();
    });

    window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (!getStoredTheme()) {
            applyTheme(getSystemTheme());
        }
    });
};

window.theme = {
    init: initTheme,
    toggle: toggleTheme,
    apply: applyTheme,
};
window.toggleTheme = toggleTheme;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}
