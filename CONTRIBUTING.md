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
2. Créer une nouvelle branche pour la modification.
3. Effectuer les changements nécessaires.
4. Vérifier que l'application fonctionne correctement.
5. Exécuter les tests PHPUnit.
6. Envoyer les modifications sur GitHub et créer une **Pull Request**.

Utiliser des noms de branches et des messages de commit suffisamment clairs pour comprendre les modifications réalisées.

Le code doit rester simple et lisible, en respectant autant que possible l'organisation existante du projet.

## 4. Contribuer aux tests

Lorsqu'une fonctionnalité est ajoutée ou qu'un bug est corrigé, il est recommandé d'ajouter ou d'adapter les tests concernés.

Pour lancer les tests :

```bash
php vendor/bin/phpunit
```

Les tests utilisant PostgreSQL doivent être exécutés avec la base de test, et non avec la base principale de l'application.

Les instructions de configuration sont disponibles dans le [README.md](README.md).

## 5. Améliorer la documentation

La documentation peut être corrigée ou complétée lorsqu'une information est manquante ou qu'une fonctionnalité évolue.

Les modifications peuvent concerner :

- Le fichier `README.md`
- Le fichier `CONTRIBUTING.md`
- Les explications présentes dans le code

Les informations ajoutées doivent rester simples et compréhensibles pour faciliter la reprise du projet.

## 6. Validation des modifications

Avant de proposer une Pull Request, vérifier que :

- Le code fonctionne correctement.
- Les tests concernés passent.
- Les modifications sont clairement expliquées.
- La documentation est mise à jour si nécessaire.