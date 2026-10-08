@props([
    'title',
    'subtitle' => '',
    'eyebrow' => 'TOKOMINI · ADMINISTRASI',
])

<section class="workspace-page">
    <div class="workspace-container">
        <header class="workspace-header">
            <div>
                <p class="workspace-eyebrow">{{ $eyebrow }}</p>
                <h1>{{ $title }}</h1>
                @if($subtitle)
                    <p>{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="workspace-actions">{{ $actions }}</div>
            @endisset
        </header>
        {{ $slot }}
    </div>
</section>
