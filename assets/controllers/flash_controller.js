import { Controller } from '@hotwired/stimulus';

// Notification (toast) : disparition automatique + fermeture manuelle.
export default class extends Controller {
    static values = { delay: { type: Number, default: 7000 } };

    connect() {
        this.timer = setTimeout(() => this.close(), this.delayValue);
    }

    disconnect() {
        clearTimeout(this.timer);
    }

    close() {
        clearTimeout(this.timer);
        this.element.classList.add('is-leaving');
        setTimeout(() => this.element.remove(), 250);
    }
}
