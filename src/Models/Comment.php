<?php

namespace Bitw\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Comment extends Model
{
    use NodeTrait;

    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'text', 'page_id', 'page_url', 'uid'
    ];

    protected $hidden = ['user_email'];
}