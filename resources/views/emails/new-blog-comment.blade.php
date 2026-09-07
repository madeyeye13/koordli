{{-- resources/views/emails/new-blog-comment.blade.php --}}
<p>{{ $comment->author_name }} commented on "{{ $comment->post->title }}":</p>
<blockquote>{{ $comment->body }}</blockquote>
<p><a href="{{ route('platform.blog.edit', $comment->blog_post_id) }}">Review in dashboard</a></p>