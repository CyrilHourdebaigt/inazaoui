# Ina Zaoui

## 1. Présentation

Ina Zaoui est un site de photographie développé avec PHP et Symfony.

Il permet aux visiteurs de consulter les albums, de découvrir les photographies et d'accéder aux profils des photographes invités.

Le site dispose également d'un espace d'administration permettant de gérer les utilisateurs, les albums et les photographies.

## 2. Prérequis

Pour installer le projet, les outils suivants sont nécessaires :

- PHP 8.2, avec les extensions nécessaires à Symfony et PostgreSQL
- Composer
- PostgreSQL 17
- Git
- Symfony CLI

Le projet utilise **Symfony 6.4**, Doctrine pour la base de données et Twig pour l'affichage des pages.

## 3. Installation

### Récupérer le projet

Cloner le dépôt GitHub, puis accéder au dossier :

```bash
git clone https://github.com/CyrilHourdebaigt/inazaoui.git
cd inazaoui
```

### Installer les dépendances

```bash
composer install
```

### Configurer PostgreSQL

Créer un fichier `.env.local` à la racine du projet et renseigner la connexion à PostgreSQL :

```dotenv
DATABASE_URL="postgresql://postgres:VOTRE_MOT_DE_PASSE@127.0.0.1:5432/ina_zaoui?serverVersion=17&charset=utf8"
```

Remplacer `VOTRE_MOT_DE_PASSE` par le mot de passe PostgreSQL de l'ordinateur.

Adapter également les autres paramètres si nécessaire.

### Créer la base de données

```bash
php bin/console doctrine:database:create
```

Cette commande crée la base `ina_zaoui` à partir de la configuration du fichier `.env.local`.

### Restaurer les données

Le fichier `ina_zaoui_sauvegarde.sql`, fourni séparément, contient les tables, les utilisateurs, les albums et les informations sur les photographies.

Placer ce fichier à la racine du projet, puis exécuter :

```bash
psql -U postgres -d ina_zaoui -v ON_ERROR_STOP=1 -f ina_zaoui_sauvegarde.sql
```

Cette commande importe les données dans PostgreSQL.

La commande `psql` doit être accessible depuis le terminal. Sous Windows, il peut être nécessaire d'indiquer son chemin complet.

**Important :** la base doit être vide avant l'importation. Il n'est pas nécessaire d'exécuter les migrations Doctrine après la restauration, car la sauvegarde contient déjà les tables et l'historique des migrations.

### Restaurer les photographies

Les photographies sont fournies séparément dans la sauvegarde d'origine du site.

Copier les fichiers dans le dossier :

```text
public/uploads
```

Conserver leurs noms afin que les chemins enregistrés dans la base de données correspondent aux fichiers.

## 4. Utilisation

### Lancer le site

Démarrer le serveur local :

```bash
symfony server:start
```

Ouvrir l'adresse indiquée dans le terminal, généralement :

`http://127.0.0.1:8000`

### Accéder à l'administration

La page de connexion se trouve à l'adresse :

`http://127.0.0.1:8000/login`

Identifiants du compte administrateur de démonstration :

- **Adresse e-mail :** `ina@zaoui.com`
- **Mot de passe :** `password`

Ces identifiants sont réservés à l'environnement de démonstration et ne doivent pas être utilisés en production.

L'administration permet notamment de gérer les albums, les photographies et les comptes invités.

## 5. Tests

Le projet contient des tests unitaires et fonctionnels réalisés avec PHPUnit.

### Préparer la base de test

Le fichier `.env.test` est déjà présent dans le projet.

La configuration de Doctrine ajoute automatiquement le suffixe `_test` au nom de la base principale. La base utilisée pour les tests est donc `ina_zaoui_test`.

Il n'est pas nécessaire de créer un nouveau fichier `.env.test` ni d'ajouter ce suffixe dans `DATABASE_URL`.

Créer la base de test :

```bash
php bin/console doctrine:database:create --env=test
```

Préparer les tables :

```bash
php bin/console doctrine:migrations:migrate --env=test
```

Il n'est pas nécessaire d'importer la sauvegarde SQL principale dans cette base.

### Exécuter les tests

```bash
php vendor/bin/phpunit
```

Le projet utilise également DAMA DoctrineTestBundle pour annuler les modifications effectuées dans la base pendant les tests.

Lors de la dernière vérification, **36 tests et 134 assertions ont été validés**.

Des avertissements de dépréciation peuvent apparaître sans empêcher les tests de réussir.

## 6. Organisation du projet

Les principaux dossiers sont :

| Dossier | Contenu |
|---|---|
| `src/` | Code PHP de l'application |
| `templates/` | Pages Twig |
| `config/` | Configuration Symfony |
| `migrations/` | Modifications de la base de données |
| `public/uploads/` | Photographies |
| `tests/` | Tests PHPUnit |

## 7. Contribution

Pour signaler un problème, proposer une fonctionnalité ou participer au développement, consulter le fichier [CONTRIBUTING.md](CONTRIBUTING.md).