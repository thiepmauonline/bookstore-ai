<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFeedback extends Model
{
    protected $table = 'chatbot_feedback';
    protected $fillable = ['chatbot_history_id', 'is_helpful'];

    protected function casts(): array
    {
        return [
            'is_helpful' => 'boolean',
        ];
    }

    public function history() { return $this->belongsTo(ChatbotHistory::class, 'chatbot_history_id'); }
}
