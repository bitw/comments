<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
      integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
<link rel="stylesheet" href="{{ route('comments.styles') }}">
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="{{ route('comments.javascript') }}"></script>

<div class="container" id="comments-block">
    <div>@lang('comments::common.comments')</div>
    <hr>
    <ul id="js-comments-tree">
        @include('comments::comments', ['comments'=>$comments])
    </ul>
    @if(config('comments.only_auth') ? auth()->check() : true)
        @include('comments::editor')
    @endif
</div>

<script>
    $('#comments-textarea').autosize();
</script>