<?php

namespace Bitw\Comments\Http\Controllers;

use App\Http\Controllers\Controller;
use Bitw\Comments\Http\Requests\CommentRequest;
use Bitw\Comments\Models\Comment;
use Illuminate\Http\Response;

class CommentsController extends Controller
{
    public function styles()
    {
        return response(\File::get(__DIR__ . '/../../../resources/assets/css/comments.css'), 200, ['Content-Type' => 'text/css']);
    }

    public function javascript()
    {
        return response(\File::get(__DIR__ . '/../../../resources/assets/js/comments.' . config('comments.frontend.use') . '.js'), 200, ['Content-Type' => 'text/javascript']);
    }

    public function store(CommentRequest $request)
    {
        // ...
        if (auth()->check()) {
            $user_id = auth()->id();
            $user_name = auth()->user()->name;
            $user_email = auth()->user()->email;
        } else {
            $user_id = null;
            $user_name = $request->name;
            $user_email = $request->email;
        }
        $url = \URL::previous();
        $parsed_url = parse_url($url);
        $page_id = md5($parsed_url['path']);

        $data = [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'text' => $request->text,
            'page_id' => $page_id,
            'page_url' => $url,
        ];

        if ($request->reply_for) {
            $node = Comment::where('page_id', $page_id)
                ->where('id', $request->reply_for)->first();

            if ($node) {
                $comment = $node->children()->create($data);
            } else {
                $comment = Comment::create($data);
            }
        } else {
            $comment = Comment::create($data);
        }

        $html = view('comments::comments', ['comments' => [$comment]])->render();

        if ($request->ajax()) {
            return response()->json([
                'result' => true,
                'message' => 'Comment created',
                'comment' => $comment,
                'html' => $html
            ]);
        }
        return redirect()->back();
    }
}