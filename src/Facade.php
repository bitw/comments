<?php

namespace Bitw\Comments;

use Bitw\Comments\Facades\Comment;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(){
        return Comment::class;
    }
}