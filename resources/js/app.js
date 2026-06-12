import './bootstrap';


 // Feature toggle component — isolated per feature key
    Alpine.data('featureToggle', (key, initialValue) => ({
        key: key,
        val: initialValue,
        set(newVal) {
            this.val = newVal;
            this.$wire.setFeature(this.key, newVal);
        }
    }));

// ── Koordli Toast System ──────────────────────────────────────
window.KrdToast = {
    container: null,

    init() {
        this.container = document.getElementById('krd-toast-container');
    },

    show(message, type = 'success', title = null, duration = 4000) {
        if (!this.container) this.init();
        if (!this.container) return;

        const icons = {
            success: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
            error:   `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
            warning: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
            info:    `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
        };

        const defaultTitles = {
            success: 'Success',
            error:   'Error',
            warning: 'Warning',
            info:    'Info',
        };

        const toast = document.createElement('div');
        toast.className = `krd-toast krd-toast-${type}`;
        toast.innerHTML = `
            <div class="krd-toast-icon">${icons[type] ?? icons.info}</div>
            <div class="krd-toast-body">
                <div class="krd-toast-title">${title ?? defaultTitles[type]}</div>
                <div class="krd-toast-message">${message}</div>
            </div>
            <button class="krd-toast-close" onclick="this.closest('.krd-toast').remove()">×</button>
        `;

        this.container.appendChild(toast);

        // Auto dismiss
        setTimeout(() => {
            toast.style.animation = 'krd-toast-out 200ms ease forwards';
            setTimeout(() => toast.remove(), 200);
        }, duration);
    },

    success(message, title = null) { this.show(message, 'success', title); },
    error(message, title = null)   { this.show(message, 'error',   title); },
    warning(message, title = null) { this.show(message, 'warning', title); },
    info(message, title = null)    { this.show(message, 'info',    title); },
};

// ── Livewire toast event listener ─────────────────────────────
document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (events) => {
        events.forEach(event => {
            KrdToast.show(
                event.message,
                event.type    ?? 'success',
                event.title   ?? null,
                event.duration ?? 4000
            );
        });
    });
});