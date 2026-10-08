# Cabinet Martin

Site web d'un cabinet médical fictif, développé avec **Symfony 7.4** : prise de rendez-vous en ligne, pharmacie en ligne avec panier et paiement simulé, factures PDF et espace patient.

> Projet de démonstration : toutes les données (médecins, patients, médicaments) sont fictives et aucun paiement réel n'est effectué.

## Fonctionnalités

- **Prise de rendez-vous** : grille de créneaux de 30 min sur les jours ouvrés, par médecin. Les créneaux déjà réservés ou passés sont indisponibles. Une contrainte d'unicité en base empêche la double réservation, même en cas de requêtes concurrentes.
- **Pharmacie en ligne** : catalogue par catégorie, fiches produit et gestion du stock.
- **Panier hybride** : stocké en session pour un visiteur, en base une fois connecté, avec fusion automatique à la connexion.
- **Paiement simulé** : la commande est passée dans une transaction avec verrou pessimiste sur le stock, puis l'email de confirmation est envoyé.
- **Factures PDF**, générées avec Dompdf.
- **Comptes patients** : inscription, vérification de l'email par lien signé, espace personnel (rendez-vous à venir et passés, historique des commandes).
- **Sécurité** : CSRF sur toutes les actions qui modifient des données (POST uniquement), contrôle d'accès par rôle, vérification de la propriété des factures.

## Stack

| | |
|---|---|
| Back | PHP 8.2+, Symfony 7.4 LTS, Doctrine ORM 3 |
| Base de données | MariaDB 11.4 |
| Front | Twig, AssetMapper (sans build Node), Bootstrap 5, Stimulus |
| Emails | Symfony Mailer + Mailpit (capture locale) |
| PDF | Dompdf |
| Tests | PHPUnit 12, WebTestCase, DAMA Doctrine Test Bundle |

## Installation

Prérequis : PHP 8.2+ (extensions `pdo_mysql`, `intl`, `gd`), Composer, Docker, et éventuellement le [Symfony CLI](https://symfony.com/download).

```bash
git clone <url-du-depot> cabinet-martin && cd cabinet-martin
composer install

# MariaDB (port 3306) + Mailpit (SMTP 1025, interface http://localhost:8025)
docker compose up -d

php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n

symfony serve -d        # ou : php -S localhost:8000 -t public
```

Le site est alors accessible sur http://localhost:8000.

### Comptes de démonstration

| Rôle | Email | Mot de passe |
|---|---|---|
| Patient | `demo@cabinet-martin.fr` | `demo1234` |
| Médecin | `dr.martin@cabinet-martin.fr` | `demo1234` |

Les emails (confirmation d'inscription, de commande, de rendez-vous) ne sont pas réellement envoyés : ils sont capturés par Mailpit sur http://localhost:8025.

Les fixtures placent les rendez-vous par rapport à la semaine en cours. Relancer `doctrine:fixtures:load -n` remet la démo à zéro.

## Tests

```bash
APP_ENV=test php bin/console doctrine:database:create
APP_ENV=test php bin/console doctrine:migrations:migrate -n
php bin/phpunit
```

Chaque test s'exécute dans une transaction annulée à la fin : la base de test reste vide.

## Structure

```
src/
├── Controller/     pages, panier, paiement, rendez-vous, factures, compte
├── Entity/         User → Personne → Patient / Medecin, RendezVous, Medicament, Commande…
├── Repository/     requêtes métier (créneaux réservés, médecins…)
├── Service/        PanierService (panier session/base)
├── Twig/           extension cart_count()
└── DataFixtures/   jeu de données de démonstration
templates/          vues Twig
tests/Controller/   tests fonctionnels
```

## Configuration

Les valeurs de `.env` conviennent au développement local avec Docker. Pour toute autre configuration (autre base, vrai serveur SMTP…), surchargez-les dans un fichier `.env.local`, qui n'est pas versionné.
