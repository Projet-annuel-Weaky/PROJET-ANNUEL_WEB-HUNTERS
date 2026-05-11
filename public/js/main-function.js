"use strict()";
const body = document.querySelector("body");
const bouton = document.querySelector("#darkMode");

const btnMenu = document.querySelector(".menu-toggle");
const btnProfile = document.querySelector(".profile-button");
const menu = document.querySelector(".menu-dropdown");
const profilec = document.querySelector(".profile-dropdown-connected");
const profiled = document.querySelector(".profile-dropdown-disconnected");

if (bouton) {
  const setThemeButtonText = function () {
    bouton.textContent = body.classList.contains("lightMode") ? "Dark Mode" : "Light Mode";
  };

  setThemeButtonText();

  bouton.addEventListener("click", function () {
    body.classList.toggle("lightMode");
    setThemeButtonText();
  });
}

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
