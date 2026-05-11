export class MenuProfile {
    constructor() {
        this.setupProfileMenu();
    }

    setupProfileMenu() {
        const userProfile = document.getElementById('userProfile');
        const profileMenu = document.getElementById('profileMenu');

        userProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('active');
        });
    }
}