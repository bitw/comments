<?php

namespace Bitw\Comments;

use Bitw\Comments\Http\Middleware\InjectComments;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $configPath = __DIR__ . '/../config/comments.php';
        $this->mergeConfigFrom($configPath, 'comments');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'comments');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'comments');

        // Publish
        // php artisan vendor:publish --provider="Bitw\Comments\ServiceProvider"
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
        $this->publishes([__DIR__.'/../database/migrations/' => database_path('migrations')],'migrations');
        $this->publishes([__DIR__.'/../resources/views/' => resource_path('views/vendor/comments')],'view');

        $routeConfig = [
            'namespace' => 'Bitw\Comments\Http\Controllers',
            'prefix' => 'comment',
        ];

        $this->getRouter()->group($routeConfig, function ($router) {
            $router->get('/styles.css', ['as'=>'comments.styles', 'uses'=>'CommentsController@styles']);
            $router->get('/script.js', ['as'=>'comments.javascript', 'uses'=>'CommentsController@javascript']);
            $router->post('/', ['as'=>'comments.create', 'uses'=>'CommentsController@store', 'middleware'=>['web']]);
        });

        $this->registerMiddleware(InjectComments::class);
    }

    public function register()
    {
        $this->app->singleton(
            LaravelComments::class,
            function () {
                $comments = new LaravelComments($this->app);

                return $comments;
            }
        );
    }

    protected function getRouter()
    {
        return $this->app['router'];
    }

    protected function getConfigPath()
    {
        return config_path('comments.php');
    }

    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[\Illuminate\Contracts\Http\Kernel::class];
        $kernel->pushMiddleware($middleware);
    }
}