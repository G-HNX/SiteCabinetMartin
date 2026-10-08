import { Controller } from '@hotwired/stimulus';

// Apparition douce au défilement (amélioration progressive : sans JS, tout reste visible).
export default class extends Controller {
    connect() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
        const rect = this.element.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.9) return;
        this.element.classList.add('reveal-pending');
        this.observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    this.element.classList.remove('reveal-pending');
                    this.observer.disconnect();
                }
            },
            { threshold: 0.12 },
        );
        this.observer.observe(this.element);
    }

    disconnect() {
        this.observer?.disconnect();
    }
}
