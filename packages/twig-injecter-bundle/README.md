# Twig Injecter Bundle

Bundle Symfony qui injecte automatiquement des variables globales Twig avant chaque requête, sans configuration manuelle : il suffit d'implémenter une interface.

## Installation

```bash
composer require apphalla/twig-injecter-bundle
```

Le bundle s'enregistre automatiquement (`AbstractBundle` + Symfony Flex ou déclaration manuelle dans `config/bundles.php`) :

```php
return [
    Apphalla\TwigInjecterBundle\TwigInjecterBundle::class => ['all' => true],
];
```

## Utilisation

Créez un service qui implémente `TwigInjecterInterface` :

```php
use Apphalla\TwigInjecterBundle\Contract\TwigInjecterInterface;
use Symfony\Component\HttpFoundation\Request;

final class CurrentTenantInjecter implements TwigInjecterInterface
{
    public function __construct(private TenantResolver $resolver) {}

    public function supports(Request $request): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'current_tenant';
    }

    public function getValue(): mixed
    {
        return $this->resolver->resolve();
    }
}
```

Le service est détecté automatiquement (autoconfiguration via l'interface) : aucune déclaration de tag manuelle n'est nécessaire. À chaque requête principale (`kernel.controller`), le bundle appelle `supports()` sur chaque injecter enregistré et, s'il renvoie `true`, ajoute `getValue()` comme variable globale Twig sous le nom `getName()`.

La variable est ensuite disponible dans tous les templates :

```twig
{{ current_tenant }}
```

## Fonctionnement interne

- `TwigInjecterBundle` tague automatiquement toute classe implémentant `TwigInjecterInterface` (`app.twig_injecter`) et enregistre `TwigInjecterListener` sur l'événement `kernel.controller`.
- `TwigInjecterListener` ignore les sous-requêtes (`isMainRequest()`) et n'agit que sur la requête principale.

## Tests

```bash
composer install
vendor/bin/phpunit
```
