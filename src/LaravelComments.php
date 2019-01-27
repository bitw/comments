<?php

namespace Bitw\Comments;

use Bitw\Comments\Models\Comment;

class LaravelComments
{
    const MODE_INJECT = 'inject';
    const MODE_WIDGET = 'widget';

    protected $booted = false;
    protected $enabled = null;

    public function enable()
    {
        $this->enabled = true;

        if (!$this->booted) {
            $this->boot();
        }
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }
    }

    public function modifyResponse($request, $response)
    {
        if (config('comments.mode') == 'inject') {
            $this->injectComments($response);
        }
        return $response;
    }

    public function injectComments($response)
    {
        $content = $response->getContent();

        if (false === $pos = strripos($content, '</body>')) {
            return;
        }

        $parsed_url = parse_url(\URL::current());
        $parsed_url['path'] = $parsed_url['path'] ?? '/';

        $allow_by_urls = (bool)collect(config('comments.allow_url'))
            ->filter(function ($item) use ($parsed_url) {
                $current_url = $parsed_url['path'];
                return $current_url == $item || str_is($item, $current_url)
                    ? true
                    : false;
            })->count();

        $allow_by_routes = (bool)collect(config('comments.allow_routes'))
            ->filter(function ($item) {
                $current_route_name = optional(\Route::current())->getName();
                return $current_route_name == $item || str_is($item, $current_route_name)
                    ? true
                    : false;
            })->count();

        if (!$allow_by_routes && !$allow_by_urls) {
            return;
        }

        $comments = Comment::where('page_id', md5($parsed_url['path'] ?? '/'))
            //->withDepth()
            ->defaultOrder()
            ->get();

        $inject = view('comments::index')
            ->with('comments', $comments->toTree())
            ->render();

        $content = substr($content, 0, $pos) . $inject . substr($content, $pos);

        $response->setContent($content);
        $response->headers->remove('Content-Length');
    }
}