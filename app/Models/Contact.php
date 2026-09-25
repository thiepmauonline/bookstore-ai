<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'message', 'is_handled', 'handled_at'];

    protected function casts(): array
    {
        return [
            'is_handled' => 'boolean',
            'handled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
