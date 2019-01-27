@foreach($comments as $comment)
    <li data-id="{{ $comment->id }}" class="js-comments-node">
        <div>
            <strong class="js-comments-author_name">{{ $comment->user_name }}</strong> {{ $comment->created_at }} [{{ $comment->id }}]
        </div>
        <div>
            {!! strip_tags(nl2br($comment->text), "<br>") !!}
            <div><button class="btn btn-sm btn-link js-comments-reply">@lang('comments::common.reply')</button></div>
        </div>
        @if($comment->children->count())
            <ul>
                @include('comments::comments', ['comments'=>$comment->children])
            </ul>
        @endif
    </li>
@endforeach
