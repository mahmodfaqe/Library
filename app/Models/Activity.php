<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

#[Fillable(['user_id', 'actor_name', 'action', 'subject', 'ip'])]
class Activity extends Model
{
    protected $table = 'activity_log';

    /**
     * Record something an administrator did.
     *
     * The actor's name is stored alongside the id so the trail still reads
     * correctly after an account is deleted.
     */
    public static function record(string $action, ?string $subject = null): self
    {
        $user = Auth::user();

        return self::create([
            'user_id' => $user?->id,
            'actor_name' => $user?->name ?? 'system',
            'action' => $action,
            'subject' => $subject,
            'ip' => Request::ip(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
