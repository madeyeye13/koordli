{{-- resources/views/emails/comment-approved.blade.php --}}
<p>Hi {{ $comment->author_name }},</p>
<p>Your comment on "{{ $comment->post->title }}" has been approved and is now live.</p>
<p><a href="{{ $comment->post->canonicalUrl() }}">View it here</a></p>