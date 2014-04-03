<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;

/**
 * @require Doctrine\ODM\MongoDB\Events
 * @require Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\ODM\MongoDB\Mapping\ClassMetadata
 */
class EntitiesTypeSubscriberSpec extends ObjectBehavior
{
    function let(ReferencedCollectionFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_is_an_event_subsriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn([Events::postLoad]);
    }

    function it_transforms_values_of_an_entity_collection_field_into_entities(
        LifecycleEventArgs $args,
        ValuesStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflBar,
        BarStub $bar4,
        BarStub $bar8,
        BarStub $bar15,
        ReferencedCollectionFactory $factory,
        ReferencedCollection $collection
    ) {
        $args->getDocument()->willReturn($entity);
        $args->getDocumentManager()->willReturn($dm);

        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = ['bar' => $reflBar];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities', 'targetEntity' => 'Acme/Entity/Bar'],
        ];

        $reflBar->getValue($entity)->willReturn([4, 8, 15]);
        $factory->create('Acme/Entity/Bar', [4, 8, 15])->willReturn($collection);

        $reflBar->setValue($entity, $collection)->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_does_not_convert_value_if_initial_value_is_already_a_referenced_collection(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflBar,
        ReferencedCollection $collection
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = ['bar' => $reflBar];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities', 'targetEntity' => 'Acme\Entity\Bar'],
        ];
        $reflBar->getValue($document)->willReturn($collection);

        $reflBar->setValue($document, Argument::any())->shouldNotBeCalled();

        $this->postLoad($args);

    }

    function it_throws_exception_when_entity_collection_field_has_no_target_entity(
        LifecycleEventArgs $args,
        ValuesStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getDocument()->willReturn($entity->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities'],
        ];
        $documentMetadata->name = 'Acme/Entity';

        $exception = new \RuntimeException('Please provide the "targetEntity" of the Acme/Entity::$bar field mapping');
        $this->shouldThrow($exception)->duringPostLoad($args);
    }
}

class ValuesStub
{
}

class BarStub
{
}