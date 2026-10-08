import { Controller } from '@hotwired/stimulus';

// Menu déroulant accessible (bouton + liste), fermé au clic extérieur ou sur Échap.
export default class extends Controller {
    static targets = ['button', 'menu'];

    connect() {
        this.onClick = (e) => {
            if (!this.element.contains(e.target)) this.close();
        };
        this.onKey = (e) => {
            if (e.key === 'Escape') {
                this.close();
                this.buttonTarget.focus();
            }
        };
        document.addEventListener('click', this.onClick);
        document.addEventListener('keydown', this.onKey);
    }

    disconnect() {
        document.removeEventListener('click', this.onClick);
        document.removeEventListener('keydown', this.onKey);
    }

    toggle() {
        const open = this.buttonTarget.getAttribute('aria-expanded') === 'true';
        open ? this.close() : this.open();
    }

    open() {
        this.buttonTarget.setAttribute('aria-expanded', 'true');
        this.menuTarget.classList.add('is-open');
    }

    close() {
        this.buttonTarget.setAttribute('aria-expanded', 'false');
        this.menuTarget.classList.remove('is-open');
    }
}
