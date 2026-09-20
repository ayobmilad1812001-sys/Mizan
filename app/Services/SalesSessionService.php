<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\SessionStatus;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SalesSession;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesSessionService
{
    public function open(User $user, string $openingFloat): SalesSession
    {
        try {
            $session = new SalesSession;
            $session->forceFill([
                'user_id' => $user->id,
                'status' => SessionStatus::Open,
                'opening_float' => $openingFloat,
            ]);
            $session->save();

            return $session;
        } catch (UniqueConstraintViolationException) {
            // active_key is a generated column with a unique index, so the database
            // itself refuses a second open session for the same employee even if two
            // browser tabs submit at the same instant.
            throw new RuntimeException('لديك جلسة بيع مفتوحة بالفعل.');
        }
    }

    /**
     * The employee's own open session, or null. Never accept a session id from the
     * client: it may point at a session that was closed in another tab, or at another
     * employee's session.
     */
    public function currentFor(User $user): ?SalesSession
    {
        return SalesSession::where('user_id', $user->id)
            ->where('status', SessionStatus::Open)
            ->first();
    }

    /**
     * Cash that should be in the drawer:
     * opening float + cash sales − cash refunds.
     */
    public function expectedCash(SalesSession $session): string
    {
        $cashSales = (string) (Sale::where('sales_session_id', $session->id)
            ->where('payment_method', PaymentMethod::Cash)
            ->sum('total') ?: '0');

        $cashRefunds = (string) (Refund::whereIn(
            'return_id',
            SaleReturn::where('sales_session_id', $session->id)->select('id'),
        )->sum('amount') ?: '0');

        return bcsub(bcadd($session->opening_float, $cashSales, 3), $cashRefunds, 3);
    }

    /**
     * A shortage never blocks closing; it is recorded as a negative difference.
     * Closed sessions are never reopened.
     */
    public function close(SalesSession $session, User $user, string $actualCash, ?string $notes = null): SalesSession
    {
        return DB::transaction(function () use ($session, $user, $actualCash, $notes) {
            // Lock the session before reading its sales: a sale being posted right now
            // must either be counted in the expected cash or be refused, never slip in
            // after the drawer has been reconciled.
            $session = SalesSession::where('id', $session->id)->lockForUpdate()->firstOrFail();

            if ($session->user_id !== $user->id) {
                throw new RuntimeException('لا يمكنك إغلاق جلسة موظف آخر.');
            }

            if (! $session->isOpen()) {
                throw new RuntimeException('هذه الجلسة مغلقة بالفعل.');
            }

            $expected = $this->expectedCash($session);

            $session->forceFill([
                'status' => SessionStatus::Closed,
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'difference' => bcsub($actualCash, $expected, 3),
                'closing_notes' => $notes,
                'closed_at' => now(),
            ])->save();

            return $session;
        });
    }
}
