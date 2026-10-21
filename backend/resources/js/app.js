import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

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

    Alpine.store('publicMenu', {
        open: false,

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
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
    const sidebar = document.getElementById('sidebar');

    if (checkbox && backdrop) {
        // Close sidebar when clicking the backdrop
        backdrop.addEventListener('click', () => {
            checkbox.checked = false;
        });
    }

    if (checkbox && sidebar) {
        // Close the mobile sidebar when a navigation link is selected
        sidebar.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');

            if (link) {
                checkbox.checked = false;
            }
        });

        // Close the mobile sidebar with the Escape key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && checkbox.checked) {
                checkbox.checked = false;
            }
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

const dashboardCharts = new Map();
const chartStatusColors = {
    present: '#15803D',
    absent: '#DC2626',
    late: '#D97706',
    other: '#0284C7',
};

function dashboardChartColors() {
    const styles = getComputedStyle(document.documentElement);
    const dark = document.documentElement.classList.contains('dark');

    return {
        text: styles.getPropertyValue(dark ? '--color-neutral-400' : '--color-neutral-600').trim() || (dark ? '#94A3B8' : '#475569'),
        grid: styles.getPropertyValue(dark ? '--color-dark-border' : '--color-neutral-200').trim() || (dark ? '#334155' : '#E2E8F0'),
        primary: styles.getPropertyValue('--color-primary-600').trim() || '#15803D',
    };
}

function initializeDashboardCharts() {
    document.querySelectorAll('canvas[data-dashboard-chart]').forEach((canvas) => {
        if (dashboardCharts.has(canvas)) {
            return;
        }

        let data;
        try {
            data = JSON.parse(canvas.dataset.chartData || '{}');
        } catch (error) {
            console.error('Unable to parse dashboard chart data.', error);
            return;
        }

        if (!Array.isArray(data.labels) || data.labels.length === 0) {
            return;
        }

        const type = canvas.dataset.dashboardChart;
        const colors = dashboardChartColors();
        let config;

        if (type === 'attendance') {
            const datasets = Array.isArray(data.datasets) ? data.datasets : [];
            config = {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: datasets.map((dataset) => ({
                        ...dataset,
                        backgroundColor: chartStatusColors[String(dataset.label).toLowerCase()] || chartStatusColors.other,
                        borderRadius: 3,
                        maxBarThickness: 36,
                    })),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colors.text, usePointStyle: true } },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: {
                        x: { stacked: true, ticks: { color: colors.text }, grid: { display: false } },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { color: colors.text, precision: 0 },
                            grid: { color: colors.grid },
                        },
                    },
                },
            };
        } else if (type === 'performance') {
            config = {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Average score',
                        data: data.averages || [],
                        borderColor: colors.primary,
                        backgroundColor: `${colors.primary}20`,
                        pointBackgroundColor: colors.primary,
                        pointBorderColor: document.documentElement.classList.contains('dark') ? '#0F172A' : '#FFFFFF',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.25,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Average score: ${context.parsed.y}%`,
                            },
                        },
                    },
                    scales: {
                        x: { ticks: { color: colors.text }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: 100,
                            ticks: { color: colors.text, callback: (value) => `${value}%` },
                            grid: { color: colors.grid },
                        },
                    },
                },
            };
        } else {
            return;
        }

        dashboardCharts.set(canvas, new Chart(canvas, config));
    });
}

function refreshDashboardCharts() {
    const colors = dashboardChartColors();

    dashboardCharts.forEach((chart) => {
        const dark = document.documentElement.classList.contains('dark');
        chart.options.scales.x.ticks.color = colors.text;
        chart.options.scales.y.ticks.color = colors.text;
        chart.options.scales.y.grid.color = colors.grid;

        if (chart.config.type === 'bar') {
            chart.options.plugins.legend.labels.color = colors.text;
        } else {
            chart.data.datasets[0].borderColor = colors.primary;
            chart.data.datasets[0].backgroundColor = `${colors.primary}20`;
            chart.data.datasets[0].pointBackgroundColor = colors.primary;
            chart.data.datasets[0].pointBorderColor = dark ? '#0F172A' : '#FFFFFF';
        }

        chart.update();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDashboardCharts, { once: true });
} else {
    initializeDashboardCharts();
}

const dashboardThemeObserver = new MutationObserver(refreshDashboardCharts);
dashboardThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

window.addEventListener('pagehide', () => {
    dashboardThemeObserver.disconnect();
    dashboardCharts.forEach((chart) => chart.destroy());
    dashboardCharts.clear();
}, { once: true });
