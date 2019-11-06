<?php

namespace Calject\LaravelProductivity\Providers;

use Calject\LaravelProductivity\Components\Routes\AnnotationRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AnnotationRouteLocalProvider extends ServiceProvider
{
    
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        (new AnnotationRoute())->envs('local')->mapRefRoutes();
    }
    
}
