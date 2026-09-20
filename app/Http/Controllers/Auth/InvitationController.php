<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserInvitation;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function show(string $token): View
    {
        $invitation = UserInvitation::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        abort_unless($invitation && $invitation->isUsable(), 404);

        return view('auth.invitation', ['token' => $token, 'user' => $invitation->user]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ], [], ['password' => 'كلمة السر']);

        $this->users->acceptInvitation($token, $request->string('password')->value());

        return redirect()->route('login')->with('status', 'فُعِّل حسابك. سجّل الدخول الآن.');
    }
}
