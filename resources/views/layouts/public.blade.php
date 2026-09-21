<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head-assets')
    @yield('meta')
</head>
<body class="bg-background text-ink font-sans antialiased min-h-screen flex flex-col">
    @include('partials.header')

    <main class="flex-1 mx-auto w-full max-w-5xl px-4 py-8">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
