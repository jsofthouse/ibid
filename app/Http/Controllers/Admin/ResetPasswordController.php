<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function create(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = $password;
                $user->save();

                $this->auditService->catat(aksi: 'reset_password', entitas: 'user', entitasId: $user->id);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('status', 'Password berhasil direset, silakan login.');
        }

        // Respons generik - status kegagalan (token salah/kadaluarsa/email
        // tidak ditemukan) tidak dibedakan ke pengguna.
        return back()->withErrors(['email' => 'Link reset password tidak valid atau sudah kadaluarsa.']);
    }
}
