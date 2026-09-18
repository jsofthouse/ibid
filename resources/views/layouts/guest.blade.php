<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head-assets')
</head>
<body class="bg-background text-ink font-sans antialiased min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <p class="text-center font-serif text-2xl font-semibold text-primary mb-6">
            IBID <span class="text-accent">·</span> Irfani Book Identity
        </p>

        <div class="bg-card border border-line rounded-lg shadow-sm p-6">
            @yield('content')
        </div>
    </div>
</body>
</html>
