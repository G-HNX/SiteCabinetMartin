# Cabinet Martin — Site Médical

## Stack
- **PHP** 8.2+ | **Symfony** 7.4 LTS | **Doctrine ORM** 3.7
- **DB** MariaDB 11.4 (docker compose, port 3306) | **Mailer** Mailpit (SMTP 1025, UI 8025) | **Frontend** AssetMapper, Bootstrap CSS 5.3, Stimulus
- **Testing** PHPUnit 12 + DAMA doctrine-test-bundle

## Domaine
Site e-commerce et gestion de cabinet médical. Patients, médecins, rendez-vous, téléconsultation, pharmacie en ligne, commandes, paiements. Authentification User + rôles Patient/Médecin.

**Entités :** User, Personne, Patient (OneToOne→Personne), Medecin (OneToOne→Personne), RendezVous, Medicament, Categorie, Commande, LigneCommande, PanierItem, Contact.

## Contrôleurs
HomeController, AboutController, SecurityController, RegistrationController, ProfilController, MedecinController, RendezVousController, TeleconsultationController, PanierController, PaiementController, FactureController, PharmacieController, ContactController, PolitiqueController.

## Commandes
```bash
docker compose up -d                              # Services (MariaDB, Mailpit)
php bin/console doctrine:migrations:migrate      # Appliquer migrations (base cabinet)
php bin/console doctrine:fixtures:load -n        # Charger fixtures (démo, mdp demo1234)
symfony serve -d                                 # Serveur local (port 8000)
php bin/phpunit                                  # Tests (base cabinet_test, APP_ENV=test)
```

## Conventions
- **Commits** : Conventional Commits français (feat:, fix:, chore:, security:) ; `.env.local` jamais commité
- **POST + CSRF** : toutes actions modifiant données exigent POST + `isCsrfTokenValid()` (IDs : panier, paiement, rdv, verify_resend)
- **Login** : CSRF stateless via `data-controller="csrf-protection"`
- **Email** : config/packages/mailer.yaml (From = `%app.mail_from_name% <%app.mail_from%>`, paramètres en services.yaml)
- **Timezone** : Europe/Paris fixé dans src/Kernel.php
- **Panier** : PanierService (session anonyme / PanierItem connecté) ; fonction Twig `cart_count()`
- **Fixtures** : src/DataFixtures/AppFixtures.php (demo@cabinet-martin.fr, dr.martin@cabinet-martin.fr)
- **Frontend** : Bootstrap CSS seul, Stimulus en assets/controllers/, styles assets/styles/app.css, **Turbo Drive désactivé** (data-turbo="false" body)
- **Routing** : `#[MapEntity(id: ...)]` requis (controller_resolver.auto_mapping: false)
- **Messenger** : emails synchrones (pas de worker pour démo)
