import { Controller } from '@hotwired/stimulus';

// Affiche / masque le mot de passe.
export default class extends Controller {
    static targets = ['input', 'icon', 'button'];

    toggle() {
        const show = this.inputTarget.type === 'password';
        this.inputTarget.type = show ? 'text' : 'password';
        this.iconTarget.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        this.buttonTarget.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        this.buttonTarget.setAttribute('aria-pressed', show ? 'true' : 'false');
    }
}
