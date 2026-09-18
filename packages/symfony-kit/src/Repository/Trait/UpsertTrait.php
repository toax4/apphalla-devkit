<?php

namespace Apphalla\SymfonyKit\Repository\Trait;

use Apphalla\SymfonyKit\Entity\SaveResult;
use Apphalla\SymfonyKit\Enum\SaveAction;
use Symfony\Component\String\UnicodeString;

/**
 * À utiliser dans un repository Symfony étendant
 * {@see \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository}, dont il consomme
 * la méthode `getEntityManager()` héritée.
 */
trait UpsertTrait
{
    /**
     * Persiste `$entity` si `$existing` est `null` (insert), ou reporte sur `$existing`
     * les champs de `$entity` qui diffèrent parmi `$fields` (update).
     *
     * @param object      $entity   entité porteuse des nouvelles valeurs
     * @param object|null $existing entité déjà persistée à mettre à jour, ou `null` pour un insert
     * @param string[]    $fields   noms des propriétés à comparer/reporter (doivent exposer get*/set*)
     * @param bool        $flush    si `true`, appelle `flush()` sur l'EntityManager après l'opération
     */
    private function upsert(object $entity, ?object $existing, array $fields = [], bool $flush = true): SaveResult
    {
        $changes = null;

        if (null === $existing) {
            $this->getEntityManager()->persist($entity);
        } else {
            $changes = $this->diffFields($existing, $entity, $fields);

            if (count($changes) > 0) {
                $this->getEntityManager()->persist($existing);
            }
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return (null === $changes)
            ? new SaveResult(entity: $entity, action: SaveAction::Insert)
            : new SaveResult(entity: $existing, action: SaveAction::Update, changes: $changes);
    }

    /**
     * Compare, champ par champ, `$existing` et `$entity`, reporte sur `$existing` les valeurs
     * qui diffèrent et retourne le détail des changements appliqués.
     *
     * @param object   $existing entité à mettre à jour (modifiée par effet de bord)
     * @param object   $entity   entité source des nouvelles valeurs
     * @param string[] $fields   noms des propriétés à comparer (doivent exposer get*/set*)
     *
     * @return array<string, array{existing: mixed, entity: mixed}> changements indexés par champ
     */
    private function diffFields(object $existing, object $entity, array $fields): array
    {
        $changes = [];

        foreach ($fields as $field) {
            $getter = 'get'.(new UnicodeString($field))->pascal();
            $setter = 'set'.(new UnicodeString($field))->pascal();

            $existingValue = $existing->$getter();
            $entityValue = $entity->$getter();

            if ($existingValue != $entityValue) {
                $changes[$field] = [
                    'existing' => $existingValue,
                    'entity' => $entityValue,
                ];

                $existing->$setter($entityValue);
            }
        }

        return $changes;
    }
}
