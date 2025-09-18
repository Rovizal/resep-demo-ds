<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'changes',
    ];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $action, Model $subject, array $changes = [], ?int $userId = null): self
    {
        return static::create([
            'user_id'      => $userId ?? optional(auth()->user())->id,
            'action'       => $action,
            'subject_type' => $subject::class,
            'subject_id'   => $subject->getKey(),
            'changes'      => $changes ?: null,
        ]);
    }
}
