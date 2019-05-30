<?php
/**
 * User: amir
 * Date: 5/30/19
 * Time: 10:28 AM
 */

namespace Modiamir;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class WorkflowConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('workflow');
        $rootNode = $treeBuilder->getRootNode();

        $this->addWorkflowSection($rootNode);

        return $treeBuilder;
    }

    private function addWorkflowSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('workflow')
            ->children()
                ->arrayNode('workflows')
                    ->canBeEnabled()
                    ->beforeNormalization()
                        ->always(function ($v) {
                            if (true === $v['enabled']) {
                                $workflows = $v;
                                unset($workflows['enabled']);

                                if (1 === \count($workflows) && isset($workflows[0]['enabled']) && 1 === \count($workflows[0])) {
                                    $workflows = [];
                                }

                                if (1 === \count($workflows) && isset($workflows['workflows']) && array_keys($workflows['workflows']) !== range(0, \count($workflows) - 1) && !empty(array_diff(array_keys($workflows['workflows']), ['audit_trail', 'type', 'marking_store', 'supports', 'support_strategy', 'initial_place', 'places', 'transitions']))) {
                                    $workflows = $workflows['workflows'];
                                }

                                foreach ($workflows as $key => $workflow) {
                                    if (isset($workflow['enabled']) && false === $workflow['enabled']) {
                                        throw new LogicException(sprintf('Cannot disable a single workflow. Remove the configuration for the workflow "%s" instead.', $workflow['name']));
                                    }

                                    unset($workflows[$key]['enabled']);
                                }

                                $v = [
                                    'enabled' => true,
                                    'workflows' => $workflows,
                                ];
                            }

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->arrayNode('workflows')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->arrayNode('marking_store')
                                        ->isRequired()
                                        ->children()
                                            ->enumNode('type')
                                                ->defaultValue('single_state')
                                                ->values(['multiple_state', 'single_state'])
                                            ->end()
                                            ->scalarNode('property')
                                                ->defaultValue('status')
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('supports')
                                        ->isRequired()
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) { return [$v]; })
                                        ->end()
                                        ->requiresAtLeastOneElement()
                                        ->prototype('scalar')
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function ($v) { return !class_exists($v) && !interface_exists($v, false); })
                                                ->thenInvalid('The supported class or interface "%s" does not exist.')
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->scalarNode('initial_place')
                                        ->defaultNull()
                                    ->end()
                                    ->arrayNode('places')
                                        ->beforeNormalization()
                                            ->always()
                                            ->then(function ($places) {
                                                // It's an indexed array of shape  ['place1', 'place2']
                                                if (isset($places[0]) && \is_string($places[0])) {
                                                    return array_map(function (string $place) {
                                                        return ['name' => $place];
                                                    }, $places);
                                                }

                                                // It's an indexed array, we let the validation occur
                                                if (isset($places[0]) && \is_array($places[0])) {
                                                    return $places;
                                                }

                                                foreach ($places as $name => $place) {
                                                    if (\is_array($place) && \array_key_exists('name', $place)) {
                                                        continue;
                                                    }
                                                    $place['name'] = $name;
                                                    $places[$name] = $place;
                                                }

                                                return array_values($places);
                                            })
                                        ->end()
                                        ->isRequired()
                                        ->requiresAtLeastOneElement()
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('name')
                                                    ->isRequired()
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->arrayNode('metadata')
                                                    ->normalizeKeys(false)
                                                    ->defaultValue([])
                                                    ->example(['color' => 'blue', 'description' => 'Workflow to manage article.'])
                                                    ->prototype('variable')
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('transitions')
                                        ->beforeNormalization()
                                            ->always()
                                            ->then(function ($transitions) {
                                                // It's an indexed array, we let the validation occur
                                                if (isset($transitions[0]) && \is_array($transitions[0])) {
                                                    return $transitions;
                                                }

                                                foreach ($transitions as $name => $transition) {
                                                    if (\is_array($transition) && \array_key_exists('name', $transition)) {
                                                        continue;
                                                    }
                                                    $transition['name'] = $name;
                                                    $transitions[$name] = $transition;
                                                }

                                                return $transitions;
                                            })
                                        ->end()
                                        ->isRequired()
                                        ->requiresAtLeastOneElement()
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('name')
                                                    ->isRequired()
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->scalarNode('guard')
                                                    ->cannotBeEmpty()
                                                    ->info('An expression to block the transition')
                                                    ->example('is_fully_authenticated() and is_granted(\'ROLE_JOURNALIST\') and subject.getTitle() == \'My first article\'')
                                                ->end()
                                                ->arrayNode('from')
                                                    ->beforeNormalization()
                                                        ->ifString()
                                                        ->then(function ($v) { return [$v]; })
                                                    ->end()
                                                    ->requiresAtLeastOneElement()
                                                    ->prototype('scalar')
                                                        ->cannotBeEmpty()
                                                    ->end()
                                                ->end()
                                                ->arrayNode('to')
                                                    ->beforeNormalization()
                                                        ->ifString()
                                                        ->then(function ($v) { return [$v]; })
                                                    ->end()
                                                    ->requiresAtLeastOneElement()
                                                    ->prototype('scalar')
                                                        ->cannotBeEmpty()
                                                    ->end()
                                                ->end()
                                                ->arrayNode('metadata')
                                                    ->normalizeKeys(false)
                                                    ->defaultValue([])
                                                    ->example(['color' => 'blue', 'description' => 'Workflow to manage article.'])
                                                    ->prototype('variable')
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('metadata')
                                        ->normalizeKeys(false)
                                        ->defaultValue([])
                                        ->example(['color' => 'blue', 'description' => 'Workflow to manage article.'])
                                        ->prototype('variable')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
