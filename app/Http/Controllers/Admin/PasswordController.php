<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UbahPasswordRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function edit(): View
    {
        return view('admin.auth.ganti-password');
    }

    public function update(UbahPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password_baru');
        $user->save();

        $this->auditService->catat(aksi: 'ganti_password', entitas: 'user', entitasId: $user->id);

        return redirect()->route('admin.password.edit')->with('sukses', 'Password berhasil diubah.');
    }
}
