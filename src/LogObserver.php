<?php

namespace Apper\LogManager;

use Illuminate\Database\Eloquent\Model;

class LogObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction('created', $model, [
            'attributes' => $this->getLoggableAttributes($model, $model->getAttributes()),
        ]);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $original = $model->getOriginal();
        
        // Sadece değişen alanları logla
        $logData = [];
        foreach ($changes as $key => $newValue) {
            if ($this->shouldLogAttribute($model, $key)) {
                $logData[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($logData)) {
            $this->logAction('updated', $model, ['changes' => $logData]);
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction('deleted', $model, [
            'attributes' => $this->getLoggableAttributes($model, $model->getAttributes()),
        ]);
    }

    /**
     * Handle the Model "restored" event (soft delete).
     */
    public function restored(Model $model): void
    {
        $this->logAction('restored', $model, [
            'attributes' => $this->getLoggableAttributes($model, $model->getAttributes()),
        ]);
    }

    /**
     * Handle the Model "forceDeleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->logAction('force_deleted', $model, [
            'attributes' => $this->getLoggableAttributes($model, $model->getAttributes()),
        ]);
    }

    /**
     * Log the action using LogService.
     */
    protected function logAction(string $action, Model $model, array $data): void
    {
        LogService::record(
            actionType: $action,
            entityType: $this->getEntityType($model),
            entityId: $model->getKey(),
            payload: array_merge($data, [
                'model_class' => get_class($model),
                'timestamp' => now()->toIso8601String(),
            ])
        );
    }

    /**
     * Get the entity type name.
     */
    protected function getEntityType(Model $model): string
    {
        if (method_exists($model, 'getLogEntityType')) {
            return $model->getLogEntityType();
        }

        return class_basename($model);
    }

    /**
     * Check if an attribute should be logged.
     */
    protected function shouldLogAttribute(Model $model, string $attribute): bool
    {
        // Belirli alanları hariç tut
        $excludedAttributes = [
            'password',
            'remember_token',
            'created_at',
            'updated_at',
        ];

        // Model'de excludedFromLog tanımlıysa kullan
        if (property_exists($model, 'excludedFromLog')) {
            $excludedAttributes = array_merge($excludedAttributes, $model->excludedFromLog);
        }

        // Model'de loggableFields tanımlıysa sadece onları logla
        if (property_exists($model, 'loggableFields')) {
            return in_array($attribute, $model->loggableFields) && !in_array($attribute, $excludedAttributes);
        }

        return !in_array($attribute, $excludedAttributes);
    }

    /**
     * Get only loggable attributes.
     */
    protected function getLoggableAttributes(Model $model, array $attributes): array
    {
        $loggable = [];
        foreach ($attributes as $key => $value) {
            if ($this->shouldLogAttribute($model, $key)) {
                $loggable[$key] = $value;
            }
        }
        return $loggable;
    }
}
