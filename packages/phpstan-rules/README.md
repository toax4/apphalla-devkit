# apphalla/phpstan-rules

Règles PHPStan custom réutilisables.

## Installation

Dans le projet consommateur :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "chemin/vers/apphalla-devkit/packages/phpstan-rules"
        }
    ],
    "require-dev": {
        "toax4/phpstan-rules": "@dev"
    }
}
```

```bash
composer require --dev toax4/phpstan-rules
```

Puis inclure le fichier d'extension dans `phpstan.neon` (ou `phpstan.neon.dist`) du projet :

```neon
includes:
    - vendor/toax4/phpstan-rules/extension.neon
```

## Règles disponibles

### `RequireNamedArgumentsRule`

Oblige à nommer les arguments d'un appel de fonction, méthode ou méthode statique dès que
le nombre d'arguments passés atteint un seuil configurable.

**But** : éviter les appels du type `save($user, true, false)` où l'on ne sait pas, à la
lecture, ce que représentent `true` et `false`.

```php
// ❌ refusé (2 arguments positionnels, seuil par défaut = 2)
$repository->upsert($entity, $existing);

// ✅ accepté
$repository->upsert(entity: $entity, existing: $existing);
```

Ne s'applique pas :
- aux appels avec moins d'arguments que le seuil configuré ;
- aux arguments passés via l'opérateur de dépliage (`...$args`) ;
- aux appels en syntaxe *first-class callable* (`strlen(...)`).

#### Configuration

Le seuil (`minArguments`, par défaut `2`) se configure dans `extension.neon` :

```neon
services:
    -
        class: Apphalla\PHPStanRules\Rules\RequireNamedArgumentsRule
        arguments:
            minArguments: 3
        tags:
            - phpstan.rules.rule
```

Pour le surcharger côté projet consommateur sans modifier `extension.neon`, redéclarer le
service dans le `phpstan.neon` du projet après l'`include` :

```neon
includes:
    - vendor/toax4/phpstan-rules/extension.neon

services:
    -
        class: Apphalla\PHPStanRules\Rules\RequireNamedArgumentsRule
        arguments:
            minArguments: 3
        tags:
            - phpstan.rules.rule
```

L'identifiant de l'erreur remontée est `apphalla.namedArguments` — utile pour l'ignorer
ponctuellement via `ignoreErrors` :

```neon
parameters:
    ignoreErrors:
        -
            identifier: apphalla.namedArguments
            path: src/Legacy/*
```
