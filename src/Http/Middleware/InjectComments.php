<?php

namespace Bitw\Comments\Http\Middleware;

use Closure;
use Bitw\Comments\LaravelComments;
use Illuminate\Container\Container;

class InjectComments
{
    protected $container;
    protected $comments;
    protected $except;

    public function __construct(Container $container, LaravelComments $comments)
    {
        $this->container = $container;
        $this->comments = $comments;
    }

    /**
     * Handler of middleware
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->comments->boot();

        $response = $next($request);

        $this->comments->modifyResponse($request, $response);

        return $response;
    }
}