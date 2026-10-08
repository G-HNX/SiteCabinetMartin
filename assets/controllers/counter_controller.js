import { Controller } from '@hotwired/stimulus';

// Compteur animé déclenché à l'apparition à l'écran.
export default class extends Controller {
    static values = { end: Number, duration: { type: Number, default: 1400 } };

    connect() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
            this.element.textContent = this.endValue;
            return;
        }
        this.element.textContent = '0';
        this.observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    this.observer.disconnect();
                    this.run();
                }
            },
            { threshold: 0.4 },
        );
        this.observer.observe(this.element);
    }

    disconnect() {
        this.observer?.disconnect();
    }

    run() {
        const start = performance.now();
        const step = (now) => {
            const p = Math.min((now - start) / this.durationValue, 1);
            this.element.textContent = Math.round((1 - Math.pow(1 - p, 3)) * this.endValue);
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }
}
