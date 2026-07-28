<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['major_id', 'name'];

    public function major() { return $this->belongsTo(Major::class); }
    public function books() { return $this->hasMany(Book::class); }
}