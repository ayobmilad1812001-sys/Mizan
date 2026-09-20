<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index(): View
    {
        $users = User::with(['role', 'invitations' => fn ($q) => $q->whereNull('accepted_at')->latest()])
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        ['user' => $user, 'token' => $token] = $this->users->invite($request->validated(), auth()->user());

        // Shown once, on the next screen only: the raw token is never stored.
        return redirect()->route('users.index')
            ->with('status', "أُنشئ حساب \"{$user->name}\". أرسل له رابط الدعوة أدناه، وهو صالح 24 ساعة.")
            ->with('invite_link', route('invitations.show', $token));
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->update($user, $request->validated(), auth()->user());

        return redirect()->route('users.index')->with('status', 'حُدِّثت بيانات الموظف.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->users->toggleStatus($user, auth()->user());

        return back()->with('status', 'تغيّرت حالة الحساب.');
    }

    public function reinvite(User $user): RedirectResponse
    {
        $token = $this->users->reinvite($user, auth()->user());

        return back()
            ->with('status', "أُنشئ رابط دعوة جديد لـ \"{$user->name}\"، صالح 24 ساعة.")
            ->with('invite_link', route('invitations.show', $token));
    }
}
