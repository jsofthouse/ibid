<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head-assets')
</head>
<body class="bg-background text-ink font-sans antialiased min-h-screen flex">
    @include('partials.admin-sidebar')

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="border-b border-line bg-card px-6 py-4 flex items-center justify-between">
            <h1 class="font-serif text-lg font-semibold text-primary">@yield('page-title', 'Dashboard')</h1>

            @auth
                <form method="POST" action="{{ route('admin.logout') }}" class="flex items-center gap-3">
                    @csrf
                    <span class="text-sm text-ink/70">{{ auth()->user()->name }}</span>
                    <button type="submit" class="text-sm font-medium text-accent hover:underline">
                        Logout
                    </button>
                </form>
            @endauth
        </header>

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
