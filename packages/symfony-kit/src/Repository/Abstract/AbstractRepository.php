<?php

use Apphalla\SymfonyKit\Entity\SaveResult;
use Apphalla\SymfonyKit\Enum\SaveAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\UnicodeString;

abstract class AbstractRepository 
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /////////////////////////////
    //  ABSTRACTS 
    /////////////////////////////
    abstract protected function getEntityClassName(): string ;
    /////////////////////////////
    //  UTILS / COMMONS
    /////////////////////////////
    protected function getEditableProperties(): array {
        return [];
    }

    protected function getRepository() {
        return $this->em->getRepository($this->getEntityClassName());
    }
    /////////////////////////////
    //  CRUD METHODS 
    /////////////////////////////
    public function save(object $entity, bool $flush = true) {
        $this->em->persist($entity);

        if($flush) {
            $this->em->flush();
        }
    }
    public function delete(object $entity, bool $flush = true) {
        $this->em->remove($entity);

        if($flush) {
            $this->em->flush();
        }
    }

    public function savePlus(object $entity, ?object $existing, bool $flush = true): SaveResult
    {
        $changes = null;

        if (null === $existing) {
            $this->em->save(entity: $entity, flush: $flush);
        } else {
            $changes = $this->diffFields($existing, $entity);

            if (count($changes) > 0) {
                $this->em->save(entity: $existing, flush: $flush);
            }
        }

        return (null === $changes)
            ? new SaveResult(entity: $entity, action: SaveAction::Insert)
            : new SaveResult(entity: $existing, action: SaveAction::Update, changes: $changes);
    }
    /////////////////////////////
    //  TOOLS
    /////////////////////////////
    protected function diffFields(object $existing, object $entity): array
    {
        $changes = [];

        foreach ($this->getEditableProperties() as $field) {
            $getter = $this->buildGetterName($field);
            $setter = $this->buildSetterName($field);

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
    protected function buildGetterName($propertyName): string {
        return 'get'.(new UnicodeString($propertyName))->pascal();
    }
    protected function buildSetterName($propertyName): string {
        return 'set'.(new UnicodeString($propertyName))->pascal();
    }
}
