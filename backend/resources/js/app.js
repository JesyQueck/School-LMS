import 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        dark: false,

        init() {
            const stored = localStorage.getItem('theme');
            if (stored !== null) {
                this.dark = stored === 'dark';
            } else {
                this.dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            this.apply();
        },

        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            this.apply();
        },

        apply() {
            document.documentElement.classList.toggle('dark', this.dark);
        }
    });

    Alpine.store('sidebar', {
        open: false,

        toggle() {
            this.open = !this.open;
            this.updateBodyScroll();
        },

        open() {
            this.open = true;
            this.updateBodyScroll();
        },

        close() {
            this.open = false;
            this.updateBodyScroll();
        },

        updateBodyScroll() {
            document.body.style.overflow = this.open ? 'hidden' : '';
        }
    });
});
