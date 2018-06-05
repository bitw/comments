<?php

namespace Bitw\Comments;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton(LaravelComments::class, function () {
            $comments = new LaravelComments($this->app);

            return $comments;
        }
        );
    }
}