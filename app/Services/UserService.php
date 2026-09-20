<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class UserService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Create a pending account and issue a single-use invitation.
     * Returns the raw token, which is shown once and never stored: only its
     * SHA-256 hash is kept, so a leaked database cannot be used to take an account.
     *
     * @param  array{name:string, email:string, phone:?string, role_id:int}  $attributes
     * @return array{user: User, token: string}
     */
    public function invite(array $attributes, User $invitedBy): array
    {
        return DB::transaction(function () use ($attributes, $invitedBy) {
            $user = new User;
            $user->forceFill([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'role_id' => $attributes['role_id'],
                'password' => null,
                'status' => UserStatus::Pending,
            ]);
            $user->save();

            $token = $this->issueInvitation($user, $invitedBy);

            $this->audit->record(
                action: 'user.invited',
                auditable: $user,
                user: $invitedBy,
                newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $user->role->name],
            );

            return ['user' => $user, 'token' => $token];
        });
    }

    /**
     * Replace any outstanding invitation with a fresh one.
     */
    public function reinvite(User $user, User $invitedBy): string
    {
        if ($user->status !== UserStatus::Pending) {
            throw new RuntimeException('هذا الحساب مفعَّل بالفعل ولا يحتاج دعوة جديدة.');
        }

        return DB::transaction(function () use ($user, $invitedBy) {
            $token = $this->issueInvitation($user, $invitedBy);

            $this->audit->record(action: 'user.reinvited', auditable: $user, user: $invitedBy);

            return $token;
        });
    }

    /**
     * Accept an invitation and set the account's first password.
     */
    public function acceptInvitation(string $token, string $password): User
    {
        return DB::transaction(function () use ($token, $password) {
            $invitation = UserInvitation::with('user')
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $invitation || ! $invitation->isUsable()) {
                throw new RuntimeException('رابط الدعوة غير صالح أو منتهي الصلاحية.');
            }

            $invitation->forceFill(['accepted_at' => now()])->save();

            $user = $invitation->user;
            $user->forceFill([
                'password' => Hash::make($password),
                'status' => UserStatus::Active,
            ])->save();

            return $user;
        });
    }

    /**
     * @param  array{name:string, email:string, phone:?string, role_id:int}  $attributes
     */
    public function update(User $user, array $attributes, User $actor): User
    {
        $old = ['name' => $user->name, 'email' => $user->email, 'role_id' => $user->role_id];

        $user->forceFill([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'role_id' => $attributes['role_id'],
        ])->save();

        $this->audit->record(
            action: 'user.updated',
            auditable: $user,
            user: $actor,
            oldValues: $old,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role_id' => $user->role_id],
        );

        return $user;
    }

    public function toggleStatus(User $user, User $actor): User
    {
        if ($user->id === $actor->id) {
            throw new RuntimeException('لا يمكنك إيقاف حسابك أنت.');
        }

        if ($user->status === UserStatus::Pending) {
            throw new RuntimeException('هذا الحساب لم يُفعَّل بعد؛ أعد إرسال الدعوة بدل إيقافه.');
        }

        $disabling = $user->status === UserStatus::Active;

        $user->forceFill(['status' => $disabling ? UserStatus::Disabled : UserStatus::Active])->save();

        $this->audit->record(
            action: $disabling ? 'user.disabled' : 'user.enabled',
            auditable: $user,
            user: $actor,
        );

        return $user;
    }

    private function issueInvitation(User $user, User $invitedBy): string
    {
        UserInvitation::where('user_id', $user->id)->whereNull('accepted_at')->delete();

        $token = Str::random(64);

        (new UserInvitation)->forceFill([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(24),
            'invited_by' => $invitedBy->id,
        ])->save();

        return $token;
    }
}
