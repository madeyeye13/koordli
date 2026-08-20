<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'category', 'subject', 'body', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function render(array $data): array
    {
        $replacements = [];
        foreach ($data as $key => $value) {
            $replacements['{{' . $key . '}}'] = $value;
        }

        return [
            'subject' => str_replace(array_keys($replacements), array_values($replacements), $this->subject),
            'body'    => str_replace(array_keys($replacements), array_values($replacements), $this->body),
        ];
    }
}