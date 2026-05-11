export class ThemeProfile {
    constructor()  {
    }

    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    }

    initialize() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            this.setTheme(savedTheme);
        } else {
            this.setTheme(this.getSystemTheme());
        }
    }

    toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    setupSystemThemeListener() {
        window.matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', e => {
                const savedTheme = localStorage.getItem('theme');
                if (!savedTheme) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
    }
}

const themeManager = new ThemeProfile();

window.toggleTheme = () => themeManager.toggleTheme();