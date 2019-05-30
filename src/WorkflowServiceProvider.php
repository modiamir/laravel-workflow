<?php
/**
 * User: amir
 * Date: 5/30/19
 * Time: 8:39 AM
 */

namespace Modiamir;


use Illuminate\Support\ServiceProvider;
use Symfony\Component\Workflow\DefinitionBuilder;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/workflow.php' => config_path('workflow.php'),
        ]);
    }
}
