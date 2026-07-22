<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class SupportFaq extends Model
{
    protected $fillable = [
        'question', 'answer', 'keywords', 'category', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'keywords'  => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Simple keyword-match scoring against a user's typed message.
     * Returns FAQs ranked by number of matching keywords found in the input.
     */
    public static function searchByMessage(string $message, int $limit = 3): \Illuminate\Support\Collection
    {
        $message = strtolower($message);
        $words   = preg_split('/\W+/', $message, -1, PREG_SPLIT_NO_EMPTY);

        return static::where('is_active', true)
            ->get()
            ->map(function (SupportFaq $faq) use ($words) {
                $keywords = array_map('strtolower', $faq->keywords ?? []);
                $score = count(array_intersect($words, $keywords));
                $faq->matchScore = $score;
                return $faq;
            })
            ->filter(fn($faq) => $faq->matchScore > 0)
            ->sortByDesc('matchScore')
            ->take($limit)
            ->values();
    }
}