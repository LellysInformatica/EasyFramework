<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\DependencyInjection\Compiler;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterKernelListenersPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('event_dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('event_dispatcher');

        foreach ($container->findTaggedServiceIds('kernel.event_listener') as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;

                if (!isset($event['event'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "event" attribute on "kernel.event_listener" tags.', $id));
                }

                if (!isset($event['method'])) {
                    $event['method'] = 'on' . preg_replace(array(
                                '/(?<=\b)[a-z]/ie',
                                '/[^a-z0-9]/i'
                                    ), array('strtoupper("\\0")', ''), $event['event']);
                }

                $definition->addMethodCall('addListenerService', array($event['event'], array($id, $event['method']), $priority));
            }
        }

        foreach ($container->findTaggedServiceIds('kernel.event_subscriber') as $id => $attributes) {
            // We must assume that the class value has been correctly filled, even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new ReflectionClass($class);
            $interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
            if (!$refClass->implementsInterface($interface)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            $definition->addMethodCall('addSubscriberService', array($id, $class));
        }
    }

}
