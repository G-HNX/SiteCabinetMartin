import { Controller } from '@hotwired/stimulus';

// Évite le double envoi d'un formulaire et affiche un état de chargement.
export default class extends Controller {
    static targets = ['button'];
    static values = { label: { type: String, default: 'Traitement en cours…' } };

    lock() {
        if (!this.hasButtonTarget) return;
        const btn = this.buttonTarget;
        // Différé : le navigateur doit d'abord collecter les données du formulaire.
        setTimeout(() => {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner" aria-hidden="true"></span> ' + this.labelValue;
        }, 0);
    }
}
