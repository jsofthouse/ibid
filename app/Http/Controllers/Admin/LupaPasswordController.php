<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LupaPasswordRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class LupaPasswordController extends Controller
{
    private const PESAN_GENERIK = 'Kalau email tersebut terdaftar, kami sudah mengirim link reset password ke email itu.';

    public function __construct(private readonly AuditService $auditService) {}

    public function create(): View
    {
        return view('admin.auth.lupa-password');
    }

    /**
     * Respons SELALU generik (pesan sama) apapun hasil pencarian email-nya -
     * tidak pernah membocorkan apakah email terdaftar di sistem (PRD §9).
     */
    public function store(LupaPasswordRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        Password::broker('users')->sendResetLink(['email' => $email]);

        $targetUser = User::where('email', $email)->first();

        $this->auditService->catat(
            aksi: 'lupa_password_diminta',
            entitas: 'user',
            entitasId: $targetUser?->id,
            dataAfter: ['email' => $email],
        );

        return back()->with('status', self::PESAN_GENERIK);
    }
}
