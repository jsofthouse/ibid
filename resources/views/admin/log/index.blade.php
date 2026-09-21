@extends('layouts.admin')

@section('page-title', 'Log Audit')

@section('content')
    <form method="GET" action="{{ route('admin.log') }}" class="bg-card border border-line rounded-lg p-4 mb-6 grid grid-cols-1 sm:grid-cols-5 gap-3">
        <div>
            <label for="aksi" class="block text-xs font-medium text-ink/70 mb-1">Aksi</label>
            <select id="aksi" name="aksi" class="w-full rounded border border-line px-2 py-1.5 text-sm">
                <option value="">Semua</option>
                @foreach ($daftarAksi as $aksi)
                    <option value="{{ $aksi }}" @selected(($filter['aksi'] ?? null) === $aksi)>
                        {{ ucwords(str_replace('_', ' ', $aksi)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="entitas" class="block text-xs font-medium text-ink/70 mb-1">Entitas</label>
            <select id="entitas" name="entitas" class="w-full rounded border border-line px-2 py-1.5 text-sm">
                <option value="">Semua</option>
                @foreach ($daftarEntitas as $entitas)
                    <option value="{{ $entitas }}" @selected(($filter['entitas'] ?? null) === $entitas)>
                        {{ ucfirst($entitas) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="user_id" class="block text-xs font-medium text-ink/70 mb-1">User</label>
            <select id="user_id" name="user_id" class="w-full rounded border border-line px-2 py-1.5 text-sm">
                <option value="">Semua</option>
                @foreach ($daftarUser as $user)
                    <option value="{{ $user->id }}" @selected((string) ($filter['user_id'] ?? '') === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="dari" class="block text-xs font-medium text-ink/70 mb-1">Dari Tanggal</label>
            <input type="date" id="dari" name="dari" value="{{ $filter['dari'] ?? '' }}"
                   class="w-full rounded border border-line px-2 py-1.5 text-sm">
        </div>

        <div>
            <label for="sampai" class="block text-xs font-medium text-ink/70 mb-1">Sampai Tanggal</label>
            <input type="date" id="sampai" name="sampai" value="{{ $filter['sampai'] ?? '' }}"
                   class="w-full rounded border border-line px-2 py-1.5 text-sm">
        </div>

        <div class="sm:col-span-5 flex gap-2">
            <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-1.5 hover:opacity-90">
                Filter
            </button>
            <a href="{{ route('admin.log') }}" class="rounded border border-line text-sm px-4 py-1.5 hover:bg-background">
                Reset
            </a>
        </div>
    </form>

    <div class="bg-card border border-line rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-line text-left text-ink/70">
                    <th class="px-4 py-2 font-medium">Waktu</th>
                    <th class="px-4 py-2 font-medium">User</th>
                    <th class="px-4 py-2 font-medium">Aksi</th>
                    <th class="px-4 py-2 font-medium">Entitas</th>
                    <th class="px-4 py-2 font-medium">Keterangan</th>
                    <th class="px-4 py-2 font-medium">IP</th>
                    <th class="px-4 py-2 font-medium">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarLog as $log)
                    <tr class="border-b border-line last:border-0 align-top">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-2">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ ucwords(str_replace('_', ' ', $log->aksi)) }}</td>
                        <td class="px-4 py-2">
                            {{ $log->entitas ? ucfirst($log->entitas) : '—' }}
                            @if ($log->entitas_id)
                                <span class="text-ink/50">#{{ $log->entitas_id }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $log->keterangan ?? '—' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if ($log->data_before || $log->data_after)
                                <details>
                                    <summary class="cursor-pointer text-accent">Lihat</summary>
                                    <div class="mt-2 space-y-2 max-w-xs">
                                        @if ($log->data_before)
                                            <div>
                                                <p class="text-xs font-medium text-ink/70">Sebelum</p>
                                                <pre class="whitespace-pre-wrap break-all text-xs bg-background rounded p-2">{{ json_encode($log->data_before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                        @if ($log->data_after)
                                            <div>
                                                <p class="text-xs font-medium text-ink/70">Sesudah</p>
                                                <pre class="whitespace-pre-wrap break-all text-xs bg-background rounded p-2">{{ json_encode($log->data_after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-ink/50">Belum ada aktivitas tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $daftarLog])
@endsection
