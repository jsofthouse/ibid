<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#17352A',
                    accent: '#B08D57',
                    background: '#F7F4EC',
                    card: '#FFFFFF',
                    ink: '#20241F',
                    line: '#E5E0CF',
                },
                fontFamily: {
                    serif: ['"Source Serif 4"', 'serif'],
                    sans: ['"Work Sans"', 'sans-serif'],
                },
            },
        },
    }
</script>
