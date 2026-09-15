<?php

namespace App\Models\Concerns;

use LogicException;

/**
 * Historical records: once written they cannot be updated or deleted.
 * A model may allow specific columns to change by overriding mutableAfterCreation().
 */
trait Immutable
{
    protected static function bootImmutable(): void
    {
        static::updating(function (self $model) {
            $changed = array_keys($model->getDirty());

            if (array_diff($changed, $model->mutableAfterCreation()) !== []) {
                throw new LogicException(class_basename($model).' records are immutable.');
            }
        });

        static::deleting(fn (self $model) => throw new LogicException(class_basename($model).' records are immutable.'));
    }

    /**
     * Columns that may still change after the record is created.
     *
     * @return list<string>
     */
    public function mutableAfterCreation(): array
    {
        return [];
    }
}
