<?php
namespace App\Mail;

use App\Models\Central\BlogComment;
use Illuminate\Mail\Mailable;

class NewBlogCommentMail extends Mailable
{
    public function __construct(public BlogComment $comment) {}

    public function build()
    {
        return $this->subject('New comment awaiting approval')
            ->view('emails.new-blog-comment');
    }
}