<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseSessionRequest;
use App\Http\Requests\OpenSessionRequest;
use App\Models\SalesSession;
use App\Services\SalesSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SalesSessionController extends Controller
{
    public function __construct(private readonly SalesSessionService $sessions) {}

    public function index(): View
    {
        $user = auth()->user();

        $sessions = SalesSession::with('user')
            ->withCount('sales')
            // Without sessions.view_all an employee sees only their own sessions.
            ->unless($user->hasPermission('sessions.view_all'), fn ($q) => $q->where('user_id', $user->id))
            ->latest('opened_at')
            ->paginate(20);

        return view('sales-sessions.index', compact('sessions'));
    }

    public function store(OpenSessionRequest $request): RedirectResponse
    {
        $session = $this->sessions->open(auth()->user(), $request->validated('opening_float'));

        return redirect()->route('sales-sessions.show', $session)->with('status', 'فُتحت جلسة البيع.');
    }

    public function show(SalesSession $session): View
    {
        $user = auth()->user();

        abort_unless(
            $session->user_id === $user->id || $user->hasPermission('sessions.view_all'),
            403,
        );

        $session->load(['user', 'sales.customer', 'returns.sale']);
        $expectedCash = $session->isOpen() ? $this->sessions->expectedCash($session) : $session->expected_cash;

        return view('sales-sessions.show', compact('session', 'expectedCash'));
    }

    public function close(CloseSessionRequest $request, SalesSession $session): RedirectResponse
    {
        $this->sessions->close(
            $session,
            auth()->user(),
            $request->validated('actual_cash'),
            $request->validated('closing_notes'),
        );

        return redirect()->route('sales-sessions.show', $session)->with('status', 'أُغلقت جلسة البيع.');
    }
}
