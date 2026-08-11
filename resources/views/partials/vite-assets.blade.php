@php
    $hotPath = public_path('hot');
    $manifestPath = public_path('build/manifest.json');
@endphp

@if (file_exists($hotPath))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@elseif (file_exists($manifestPath))
    @php
        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $basePath = rtrim(request()->getBasePath(), '/');
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
    @endphp

    @if ($cssFile)
        <link rel="stylesheet" href="{{ $basePath }}/build/{{ $cssFile }}">
    @endif

    @if ($jsFile)
        <script type="module" src="{{ $basePath }}/build/{{ $jsFile }}"></script>
    @endif
@else
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
