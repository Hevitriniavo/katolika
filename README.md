
---

# Commandes de Console pour Générer des Composants MVC

Ce script PHP vous permet de générer rapidement des composants MVC tels que des contrôleurs, des vues, des repositories, des entités et des services dans votre projet PHP. Vous pouvez utiliser ces commandes pour créer des fichiers avec les noms spécifiés.

## Installation

Assurez-vous d'avoir installé toutes les dépendances requises en exécutant la commande suivante dans le répertoire de votre projet :

```bash
composer install
```

## Utilisation

Vous pouvez exécuter le script de console pour créer divers composants dans votre projet.

### Commandes Disponibles

1. **do:controller** : Crée un contrôleur.
2. **do:view** : Crée une vue.
3. **do:repository** : Crée un repository.
4. **do:entity** : Crée une entité.
5. **do:service** : Crée un service.
6. **do:mvc** : Crée un ensemble complet de composants MVC (contrôleur, vue, repository et entité).

### Commandes de Base

- **Créer un Contrôleur :**

  Pour créer un contrôleur, exécutez :

  ```bash
  php bin/console do:controller NomDuControleur
  ```

  Exemple :

  ```bash
  php bin/console do:controller PostController
  ```

- **Créer une Vue :**

  Pour créer une vue, exécutez :

  ```bash
  php bin/console do:view NomDeLaVue
  ```

  Exemple :

  ```bash
  php bin/console do:view PostView
  ```

- **Créer un Repository :**

  Pour créer un repository, exécutez :

  ```bash
  php bin/console do:repository NomDuRepository
  ```

  Exemple :

  ```bash
  php bin/console do:repository PostRepository
  ```

- **Créer une Entité :**

  Pour créer une entité, exécutez :

  ```bash
  php bin/console do:entity NomDeLEntite
  ```

  Exemple :

  ```bash
  php bin/console do:entity Post
  ```

- **Créer un Service :**

  Pour créer un service, exécutez :

  ```bash
  php bin/console do:service NomDuService
  ```

  Exemple :

  ```bash
  php bin/console do:service PostService
  ```

- **Créer un Ensemble Complet MVC :**

  Pour créer un contrôleur, une vue, un repository et une entité avec un seul nom, exécutez :

  ```bash
  php bin/console do:mvc Nom
  ```

  Exemple :

  ```bash
  php bin/console do:mvc Post
  ```

### Notes Importantes

- Les commandes `do:controller`, `do:view`, `do:repository`, `do:entity`, et `do:service` créeront des fichiers avec le nom spécifié en PascalCase.

- La commande `do:mvc` va créer un contrôleur, une vue, un repository, et une entité basés sur le nom donné. **Si un des fichiers existe déjà, la création sera annulée**.

- Le nom spécifié sera converti automatiquement en PascalCase pour les fichiers PHP et en kebab-case pour les vues.

### Exemple d'Utilisation

Si vous voulez créer un ensemble MVC pour un composant `Post`, exécutez :

```bash
php bin/console do:mvc Post
```

Cette commande va créer les fichiers suivants :

- `PostController.php` dans le dossier `src/Controller/`
- `post.view.php` dans le dossier `src/View/`
- `PostRepository.php` dans le dossier `src/Repository/`
- `Post.php` dans le dossier `src/Entity/`

Assurez-vous que ces fichiers n'existent pas déjà avant d'exécuter cette commande pour éviter tout conflit.

### Erreurs Courantes

- **Fichier Existant** : Si un fichier pour le nom spécifié existe déjà, le script annulera la création pour éviter de l'écraser.

  Exemple de message d'erreur :

  ```
  One or more files for Post already exist. MVC creation aborted.
  ```

## Conclusion

Ce script de console facilite la création et la gestion de composants MVC pour les projets PHP. Utilisez-le pour accélérer le développement de vos applications en créant rapidement des fichiers de structure standard.

---

Avec ce README, les utilisateurs peuvent comprendre comment utiliser les commandes disponibles pour générer les composants nécessaires dans leur projet.