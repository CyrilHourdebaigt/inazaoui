# Contribuer au projet Ina Zaoui

Ce document explique comment signaler un problème, proposer une amélioration ou participer au développement du site Ina Zaoui.

## 1. Signaler un problème

Si un problème est rencontré sur le site, il est possible de le signaler dans l'onglet **Issues** du dépôt GitHub.

Pour faciliter sa résolution, préciser :

- Une description du problème
- La page concernée
- Les étapes permettant de reproduire le problème
- Le résultat attendu et le résultat obtenu

Une capture d'écran peut également être ajoutée si nécessaire.

## 2. Proposer une fonctionnalité

Pour proposer une nouvelle fonctionnalité, créer une **Issue** sur GitHub.

La proposition doit expliquer :

- La fonctionnalité souhaitée
- Son utilité pour le site
- Le fonctionnement attendu

Il est préférable de discuter de la proposition avant de commencer son développement.

## 3. Contribuer au code

Pour apporter une modification au projet :

1. Récupérer le code depuis GitHub.
2. Créer une branche dédiée à la modification.
3. Effectuer les changements nécessaires.
4. Vérifier que l'application fonctionne correctement.
5. Exécuter les tests PHPUnit et l'analyse PHPStan.
6. Envoyer les modifications sur GitHub et créer une **Pull Request**.

### Bonnes pratiques

Le code doit respecter autant que possible les conventions **PSR-12**, avec une indentation cohérente et des noms de classes, méthodes et variables explicites.

Conserver l'organisation existante du projet Symfony et privilégier un code simple, lisible et maintenable.

Utiliser des noms de branches explicites, par exemple :

- `feature/ajout-fonctionnalite` pour une nouvelle fonctionnalité
- `fix/correction-upload` pour la correction d'un bug
- `docs/mise-a-jour-readme` pour la documentation

Les messages de commit doivent décrire clairement les modifications réalisées, par exemple :

`Correction de la validation des fichiers`

Éviter les messages trop vagues comme `modifications` ou `corrections`.

## 4. Contribuer aux tests

Lorsqu'une fonctionnalité est ajoutée ou qu'un bug est corrigé, ajouter ou adapter les tests concernés.

Pour lancer les tests PHPUnit :

```bash
php vendor/bin/phpunit
```

Pour analyser le code avec PHPStan au niveau 2 :

```bash
php vendor/bin/phpstan analyse src --level=2
```

PHPUnit vérifie le comportement de l'application à travers les tests automatisés.

PHPStan effectue une analyse statique du code PHP afin de détecter certaines erreurs potentielles.

Les tests utilisant PostgreSQL doivent être exécutés avec la base de test et non avec la base principale de l'application.

Les instructions de configuration sont disponibles dans le [README.md](README.md).

## 5. Améliorer la documentation

La documentation doit être corrigée ou complétée lorsqu'une information est manquante ou qu'une fonctionnalité évolue.

Les modifications peuvent concerner :

- Le fichier `README.md`
- Le fichier `CONTRIBUTING.md`
- Les commentaires et explications présents dans le code

Les informations ajoutées doivent rester simples et compréhensibles pour faciliter la maintenance et la reprise du projet.

## 6. Validation des modifications et Code Review

Avant de proposer une Pull Request, vérifier que :

- Le code fonctionne correctement.
- Les tests PHPUnit réussissent.
- L'analyse PHPStan ne détecte aucune erreur.
- Les modifications sont clairement expliquées.
- La documentation est mise à jour si nécessaire.

### Relecture des Pull Requests

Toute Pull Request doit être relue avant sa fusion dans la branche principale.

La Code Review permet de vérifier :

- La qualité et la lisibilité du code.
- Le respect des conventions du projet.
- La cohérence des modifications avec les fonctionnalités existantes.
- La présence des tests nécessaires.
- L'absence de problèmes de sécurité évidents.

Les remarques formulées pendant la relecture doivent être prises en compte avant la validation.

La Pull Request ne doit être fusionnée qu'après validation de la relecture et réussite du pipeline d'intégration continue.

## 7. Maintenance du projet

Pour maintenir l'application dans de bonnes conditions :

- Surveiller les mises à jour des dépendances proposées par **Dependabot** sur GitHub.
- Examiner les éventuelles alertes de sécurité.
- Tester les mises à jour avant de les fusionner dans la branche principale.
- Vérifier que le pipeline GitHub Actions reste fonctionnel.
- Maintenir les tests et la documentation à jour lorsque le code évolue.

Le pipeline d'intégration continue exécute les tests PHPUnit et l'analyse PHPStan afin de détecter les régressions avant l'intégration des modifications.

Une mise à jour ne doit pas être fusionnée si les vérifications du pipeline échouent.