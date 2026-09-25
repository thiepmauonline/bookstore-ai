<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotHistory extends Model
{
    public const SOURCE_AI = 'ai';
    public const SOURCE_FALLBACK = 'fallback';

    protected $fillable = ['user_id', 'session_id', 'question', 'answer', 'book_ids', 'source', 'response_ms'];

    protected function casts(): array
    {
        return [
            'book_ids' => 'array',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function feedbacks() { return $this->hasMany(ChatbotFeedback::class); }
    public function feedback() { return $this->hasOne(ChatbotFeedback::class); }
}
