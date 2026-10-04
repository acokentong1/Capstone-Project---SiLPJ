<?php

namespace App\Observers;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    public function created(Model $model): void
    {
        ActivityLogger::recordModel($model, 'created');
    }

    public function updated(Model $model): void
    {
        ActivityLogger::recordModel($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        ActivityLogger::recordModel($model, 'deleted');
    }
}
