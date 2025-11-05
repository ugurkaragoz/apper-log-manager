<?php

namespace Apper\LogManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'logs';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'tool_name',
        'action_type',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
        'url',
        'payload',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Scope: Belirli bir action type'a göre filtrele.
     */
    public function scopeOfAction($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope: Belirli bir entity'ye ait loglar.
     */
    public function scopeForEntity($query, string $entityType, mixed $entityId = null)
    {
        $query->where('entity_type', $entityType);
        
        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }
        
        return $query;
    }

    /**
     * Scope: Belirli bir kullanıcıya ait loglar.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Tarih aralığına göre filtrele.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Son N günün logları.
     */
    public function scopeLastDays($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
