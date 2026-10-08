import { Controller } from '@hotwired/stimulus';

// Menu mobile (tiroir) : ouverture/fermeture, Échap, verrouillage du scroll.
export default class extends Controller {
    static targets = ['panel', 'toggle'];

    connect() {
        this.onKey = (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
                this.toggleTarget.focus();
            }
        };
        this.onResize = () => {
            if (window.innerWidth >= 1200 && this.isOpen) this.close();
        };
        document.addEventListener('keydown', this.onKey);
        window.addEventListener('resize', this.onResize);
    }

    disconnect() {
        document.removeEventListener('keydown', this.onKey);
        window.removeEventListener('resize', this.onResize);
        document.body.classList.remove('nav-open');
    }

    get isOpen() {
        return this.element.classList.contains('is-open');
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        this.element.classList.add('is-open');
        document.body.classList.add('nav-open');
        this.toggleTarget.setAttribute('aria-expanded', 'true');
    }

    close() {
        this.element.classList.remove('is-open');
        document.body.classList.remove('nav-open');
        this.toggleTarget.setAttribute('aria-expanded', 'false');
    }
}
