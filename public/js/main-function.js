"use strict()";
const body = document.querySelector("body");
const bouton = document.querySelector("#darkMode");

const btnMenu = document.querySelector(".menu-toggle");
const btnProfile = document.querySelector(".profile-button");
const menu = document.querySelector(".menu-dropdown");
const profilec = document.querySelector(".profile-dropdown-connected");
const profiled = document.querySelector(".profile-dropdown-disconnected");
class ThemeProfile {
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
    console.log(localStorage.getItem("theme"));
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme) {
      this.setTheme(savedTheme);
      if (savedTheme === "light") {
        body.classList.add("lightMode");
      }
      setThemeButtonText();
    } else {
      this.setTheme(this.getSystemTheme());
    }
  }

  toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme");
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

const setThemeButtonText = function () {
  bouton.textContent = body.classList.contains("lightMode")
    ? "Dark Mode"
    : "Light Mode";
};

setThemeButtonText();

bouton.addEventListener("click", function () {
  themeManager.toggleTheme();
  body.classList.toggle("lightMode");
  setThemeButtonText();
});

if (btnMenu && menu) {
  btnMenu.addEventListener("click", function () {
    menu.classList.toggle("menu_active");
  });
}

const logFilter = document.querySelector("#logFilter");
const logRows = document.querySelectorAll("[data-log-row]");

if (logFilter && logRows.length > 0) {
  logFilter.addEventListener("input", function () {
    const search = logFilter.value.toLowerCase();

    logRows.forEach(function (row) {
      const content = row.textContent.toLowerCase();
      row.style.display = content.includes(search) ? "" : "none";
    });
  });
}

if (btnProfile) {
  btnProfile.addEventListener("click", function () {
    if (profilec) {
      profilec.classList.toggle("profile_active");
    }
    if (profiled) {
      profiled.classList.toggle("profile_active");
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  themeManager.initialize();
});
