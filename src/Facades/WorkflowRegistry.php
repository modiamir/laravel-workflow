<?php
/**
 * User: amir
 * Date: 5/30/19
 * Time: 3:23 PM
 */

namespace Modiamir;

use Illuminate\Support\Facades\Facade;
use Symfony\Component\Workflow\Workflow;

/**
 * @method static Workflow get($subject, $workflowName = null)
 *
 * @see \Symfony\Component\Workflow\Registry
 */
class WorkflowRegistry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'workflow.registry';
    }
}
