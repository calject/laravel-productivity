<?php
/**
 * Author: 沧澜
 * Date: 2019-10-25
 */

namespace Calject\LaravelProductivity\Providers;

use Calject\LaravelProductivity\Consoles\Commands\DataCommentCommand;
use Calject\LaravelProductivity\Consoles\Commands\EnvConfigCommand;
use Calject\LaravelProductivity\Consoles\Commands\ModelCommentCommand;
use Calject\LaravelProductivity\Consoles\Commands\RouteNameCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class CommandsProvider
 * @package Calject\LaravelProductivity\Providers
 */
class CommandsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelCommentCommand::class,
                DataCommentCommand::class,
                EnvConfigCommand::class,
            ]);
        }
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    
    
    }
    
}