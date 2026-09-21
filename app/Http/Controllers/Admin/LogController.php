<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterAuditLogRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(FilterAuditLogRequest $request): View
    {
        $filter = $request->validated();

        $daftarLog = AuditLog::query()
            ->with('user:id,name')
            ->when($filter['aksi'] ?? null, fn ($query, $aksi) => $query->where('aksi', $aksi))
            ->when($filter['entitas'] ?? null, fn ($query, $entitas) => $query->where('entitas', $entitas))
            ->when($filter['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filter['dari'] ?? null, fn ($query, $dari) => $query->whereDate('created_at', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn ($query, $sampai) => $query->whereDate('created_at', '<=', $sampai))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.log.index', [
            'daftarLog' => $daftarLog,
            'filter' => $filter,
            'daftarAksi' => FilterAuditLogRequest::DAFTAR_AKSI,
            'daftarEntitas' => FilterAuditLogRequest::DAFTAR_ENTITAS,
            'daftarUser' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
