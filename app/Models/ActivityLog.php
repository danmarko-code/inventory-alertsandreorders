<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'type', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $description, string $type = 'info'): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'type' => $type,
            'description' => $description,
        ]);
    }
}
