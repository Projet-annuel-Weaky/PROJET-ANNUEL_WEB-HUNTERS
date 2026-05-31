export class ThemeProfile {
  constructor() {}

  getSystemTheme() {
    return window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  }

  setTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem("theme", theme);
  }

  initialize() {
    console.log("Initializing theme...");
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme) {
      this.setTheme(savedTheme);
    } else {
      this.setTheme(this.getSystemTheme());
    }
  }

  toggleTheme() {
    const current = localStorage.getItem("theme");
    const newTheme = current === "dark" ? "light" : "dark";
    this.setTheme(newTheme);
  }

  setupSystemThemeListener() {
    window
      .matchMedia("(prefers-color-scheme: dark)")
      .addEventListener("change", (e) => {
        const savedTheme = localStorage.getItem("theme");
        if (!savedTheme) {
          this.setTheme(e.matches ? "dark" : "light");
        }
      });
  }
}

const themeManager = new ThemeProfile();

document.addEventListener("DOMContentLoaded", () => {
  themeManager.initialize();
});
window.toggleTheme = () => themeManager.toggleTheme();
