<?php

namespace Apper\LogManager;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class LogService
{
    public static function record($actionType, $entityType = null, $entityId = null, $payload = null)
    {
        $user = Auth::user();
        if (!$user) return;

        DB::table('logs')->insert([
            'user_id' => $user->id,
            'team_id' => $user->current_team_id ?? null,
            'tool_name' => config('app.tool_name'),
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'url' => Request::fullUrl(),
            'payload' => $payload ? json_encode($payload) : null,
            'created_at' => now(),
        ]);
    }
}