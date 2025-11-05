<?php

namespace Apper\LogManager;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            LogService::record('create', class_basename($model), $model->id, $model->toArray());
        });

        static::updated(function ($model) {
            LogService::record('update', class_basename($model), $model->id, $model->getChanges());
        });

        static::deleted(function ($model) {
            LogService::record('delete', class_basename($model), $model->id, $model->toArray());
        });
    }
}