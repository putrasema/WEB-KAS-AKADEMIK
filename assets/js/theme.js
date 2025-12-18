/**
 * assets/js/theme.js
 * Handles Dark Mode toggling and persistence
 */

const ThemeManager = {
    init: function() {
        // Check local storage or system preference
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            this.setTheme('dark');
        } else {
            this.setTheme('light');
        }

        // Add event listener to toggle button if it exists
        const toggler = document.getElementById('theme-toggle');
        if (toggler) {
            toggler.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
        
        // Also mobile toggle if different
        const mobileToggler = document.getElementById('theme-toggle-mobile');
        if (mobileToggler) {
            mobileToggler.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
    },

    setTheme: function(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.updateIcon(theme);
    },

    toggleTheme: function() {
        const current = document.documentElement.getAttribute('data-theme');
        const target = current === 'dark' ? 'light' : 'dark';
        this.setTheme(target);
    },

    updateIcon: function(theme) {
        const icons = document.querySelectorAll('.theme-icon');
        icons.forEach(icon => {
            if (theme === 'dark') {
                icon.classList.remove('bi-moon-stars-fill');
                icon.classList.add('bi-sun-fill');
            } else {
                icon.classList.remove('bi-sun-fill');
                icon.classList.add('bi-moon-stars-fill');
            }
        });
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
});
