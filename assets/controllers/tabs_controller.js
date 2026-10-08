import { Controller } from '@hotwired/stimulus';

// Onglets accessibles (choix du médecin pour la prise de rendez-vous).
// Sans JS : tous les panneaux restent visibles, à la suite.
export default class extends Controller {
    static targets = ['tab', 'panel'];

    connect() {
        const wanted = new URLSearchParams(window.location.search).get('doctor');
        const initial = this.tabTargets.findIndex((t) => t.dataset.key === wanted);
        this.element.classList.add('is-enhanced');
        this.show(initial >= 0 ? initial : 0);
    }

    select(event) {
        this.show(this.tabTargets.indexOf(event.currentTarget));
    }

    keydown(event) {
        const i = this.tabTargets.indexOf(event.currentTarget);
        const last = this.tabTargets.length - 1;
        let n = null;
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') n = i === last ? 0 : i + 1;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') n = i === 0 ? last : i - 1;
        if (event.key === 'Home') n = 0;
        if (event.key === 'End') n = last;
        if (n !== null) {
            event.preventDefault();
            this.show(n);
            this.tabTargets[n].focus();
        }
    }

    show(index) {
        this.tabTargets.forEach((t, i) => {
            const on = i === index;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
            t.classList.toggle('is-active', on);
        });
        this.panelTargets.forEach((p, i) => {
            p.hidden = i !== index;
        });
        // Conserve le médecin choisi lors du changement de semaine.
        const key = this.tabTargets[index].dataset.key;
        this.element.querySelectorAll('[data-keep-doctor]').forEach((a) => {
            const url = new URL(a.href, window.location.origin);
            url.searchParams.set('doctor', key);
            a.href = url.toString();
        });
    }
}
