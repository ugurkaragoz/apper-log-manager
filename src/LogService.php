<?php

namespace Apper\LogManager;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class LogService
{
    /**
     * Log kaydı oluştur.
     */
    public static function record(
        string $actionType, 
        ?string $entityType = null, 
        mixed $entityId = null, 
        mixed $payload = null
    ): void {
        // Log kaydı devre dışı mı kontrol et
        if (!Config::get('log-manager.enabled', true)) {
            return;
        }

        // Kullanıcı bilgisi
        $user = Auth::user();
        
        // Kullanıcı yoksa log yapma (user_id zorunlu)
        if (!$user) {
            return;
        }

        try {
            DB::table(Config::get('log-manager.table', 'logs'))->insert([
                'user_id' => $user->id,
                'team_id' => $user->current_team_id ?? null,
                'tool_name' => Config::get('log-manager.tool_name') ?? Config::get('app.name'),
                'action_type' => $actionType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
                'url' => substr(Request::fullUrl(), 0, 255), // URL max 255 karakter
                'payload' => is_array($payload) ? json_encode($payload) : $payload,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log hatası loglanmaz (sonsuz döngü önleme)
            if (Config::get('log-manager.silent_failures', true)) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Toplu log kaydı oluştur.
     */
    public static function recordBatch(array $logs): void
    {
        if (empty($logs)) {
            return;
        }

        $user = Auth::user();
        
        // Kullanıcı yoksa log yapma
        if (!$user) {
            return;
        }

        $timestamp = now();

        $records = array_map(function ($log) use ($user, $timestamp) {
            return [
                'user_id' => $user->id,
                'team_id' => $user->current_team_id ?? null,
                'tool_name' => Config::get('log-manager.tool_name') ?? Config::get('app.name'),
                'action_type' => $log['action_type'],
                'entity_type' => $log['entity_type'] ?? null,
                'entity_id' => $log['entity_id'] ?? null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
                'url' => substr(Request::fullUrl(), 0, 255),
                'payload' => is_array($log['payload'] ?? null) ? json_encode($log['payload']) : ($log['payload'] ?? null),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $logs);

        try {
            DB::table(Config::get('log-manager.table', 'logs'))->insert($records);
        } catch (\Exception $e) {
            if (Config::get('log-manager.silent_failures', true)) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Özel action logla.
     */
    public static function logAction(string $action, array $data = []): void
    {
        static::record($action, null, null, $data);
    }

    /**
     * Kullanıcı girişi logla.
     */
    public static function logLogin(): void
    {
        static::record('login', 'User', Auth::id());
    }

    /**
     * Kullanıcı çıkışı logla.
     */
    public static function logLogout(): void
    {
        static::record('logout', 'User', Auth::id());
    }
}