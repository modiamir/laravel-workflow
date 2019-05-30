<?php
/**
 * User: amir
 * Date: 5/30/19
 * Time: 9:34 AM
 */

namespace Modiamir\Concerns;


use Modiamir\Facades\WorkflowRegistry;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlockerList;

trait HasWorkflow
{
    /**
     * Returns the object's Marking.
     *
     * @param string $workflowName Name of workflow
     *
     * @return Marking The Marking
     *
     */
    public function getMarking($workflowName = null)
    {
        return $this->get($workflowName)->getMarking($this);
    }

    /**
     * Returns true if the transition is enabled.
     *
     * @param string $transitionName A transition
     * @param string $workflowName   Name of workflow
     *
     * @return bool true if the transition is enabled
     */
    public function can($transitionName, $workflowName = null)
    {
        return $this->get($workflowName)->can($this, $transitionName);
    }

    /**
     * Builds a TransitionBlockerList to know why a transition is blocked.
     *
     * @param string $transitionName
     * @param string $workflowName
     *
     * @return TransitionBlockerList
     */
    public function buildTransitionBlockerList(string $transitionName, $workflowName = null): TransitionBlockerList
    {
        return $this->get($workflowName)->buildTransitionBlockerList($this, $transitionName);
    }

    /**
     * Fire a transition.
     *
     * @param string $transitionName A transition
     * @param string $workflowName   Name of workflow
     *
     * @return Marking The new Marking
     *
     */
    public function apply($transitionName, $workflowName = null)
    {
        return $this->get($workflowName)->apply($this, $transitionName);
    }

    /**
     * Returns all enabled transitions.
     *
     * @param string $workflowName Name of workflow
     *
     * @return Transition[] All enabled transitions
     */
    public function getEnabledTransitions($workflowName = null)
    {
        return $this->get($workflowName)->getEnabledTransitions($this);
    }

    /**
     * @param string $workflowName
     *
     * @return \Symfony\Component\Workflow\Workflow
     */
    private function get($workflowName)
    {
        return WorkflowRegistry::get($this, $workflowName);
    }
}
