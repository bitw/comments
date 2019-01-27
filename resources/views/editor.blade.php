<form action="{{ route('comments.create') }}" method="post" id="comments-form" data-page_id="{{ md5(parse_url(URL::current())['path'] ?? '/') }}">
    <div id="comments-editor">
        @if(auth()->guest())
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="name" id="comments-name" class="form-control" placeholder="@lang('comments::common.name')">
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" id="comments-email" class="form-control" placeholder="@lang('comments::common.email')">
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <textarea name="text" id="comments-text" cols="30" rows="1" class="form-control"
                          placeholder="@lang('comments::common.comment-field')"></textarea>
            </div>
            <div class="col-md-4" id="js-comments-reply" hidden>
                @lang('comments::common.reply_for')
                <span id="js-comments-reply_for_name"></span>
                <a href="javascript:void(0)" class="btn btn-sm btn-link" id="js-comments-reply-cancel">&times;</a>
                <input type="hidden" name="reply_for" id="js-comments-reply_for_id">
            </div>
        </div>
        {{ csrf_field() }}
    </div>
</form>