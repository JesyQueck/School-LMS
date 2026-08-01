import Alpine from 'alpinejs';

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

        show() {
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

window.Alpine = Alpine;
window.Alpine.start();


/* ============================================
   Sidebar checkbox toggle
   The sidebar and backdrop slide in/out via Tailwind's
   peer-checked: variant on #sidebar-menu-checkbox.
   Here we only handle closing when the backdrop is clicked.
   ============================================ */
document.addEventListener('DOMContentLoaded', () => {
    const checkbox = document.getElementById('sidebar-menu-checkbox');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (checkbox && backdrop) {
        // Close sidebar when clicking the backdrop
        backdrop.addEventListener('click', () => {
            checkbox.checked = false;
        });
    }
});

/* ============================================
   Scroll-triggered animations for premium feel
   ============================================ */
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');

    const reveal = (element) => {
        element.classList.add('is-visible');
    };

    if (!('IntersectionObserver' in window) || animatedElements.length === 0) {
        animatedElements.forEach((el, index) => {
            window.setTimeout(() => reveal(el), index * 40);
        });
    } else {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    reveal(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px',
        });

        animatedElements.forEach(el => observer.observe(el));
    }

});
