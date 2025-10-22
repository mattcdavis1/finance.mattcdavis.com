<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UsesUuid
{
  protected static function bootUsesUuid()
  {
    // static::creating(function ($model) {
    static::saving(function ($model) {
      if (!$model->uuid) {
        $model->uuid = (string) Str::orderedUuid();
      }
    });
  }
}
