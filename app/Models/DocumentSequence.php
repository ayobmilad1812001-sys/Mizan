<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Read-only through Eloquent (composite primary key). Numbers are issued with next().
 */
#[WithoutTimestamps]
#[WithoutIncrementing]
class DocumentSequence extends Model
{
    protected $primaryKey = null;

    /**
     * Issue the next number for a document type and year.
     *
     * A single atomic statement locks the sequence row, so concurrent callers never
     * receive the same number. Call it inside the transaction that creates the
     * document: if that transaction rolls back, the number is released too.
     */
    public static function next(DocumentType $type, ?int $year = null): int
    {
        DB::statement(
            'INSERT INTO document_sequences (document_type, year, last_number)
             VALUES (?, ?, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$type->value, $year ?? now()->year],
        );

        return (int) DB::selectOne('SELECT LAST_INSERT_ID() AS number')->number;
    }
}
