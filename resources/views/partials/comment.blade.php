<li class="comment">
    <div class="comment-content">
        <strong>{{ $comment->user->name ?? 'Anonymous' }}</strong>
        <p>{{ $comment->content }}</p>
        <small>Posted on: {{ $comment->creation_date }}</small>
    </div>
 
    <!-- Reply Form -->
    <div class="reply-form">
        <form action="{{ route('comments.store') }}" method="POST">
            @csrf
            <textarea name="content" placeholder="Reply to this comment..." required></textarea>
            <input type="hidden" name="post_id" value="{{ $comment->post_id }}">
            <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
            <button type="submit">Reply</button>
        </form>
    </div>

    <!-- Display Children Comments Recursively -->
    @if ($comment->children->isNotEmpty())
        <ul class="children">
            @foreach ($comment->children as $child)
                @include('partials.comment', ['comment' => $child])
            @endforeach
        </ul>
    @endif
</li>
