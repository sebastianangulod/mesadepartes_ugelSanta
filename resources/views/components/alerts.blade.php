@props(['success' => null, 'error' => null, 'info' => null, 'warnings' => [], 'errorsBag' => null])

@php
    $errorsBag = $errorsBag ?? $errors;
@endphp

@if ($success || session('success'))
    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
        <span class="font-semibold">Correcto:</span>
        <span>{{ $success ?? session('success') }}</span>
    </div>
@endif

@if ($error || session('error'))
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        <span class="font-semibold">Error:</span>
        <span>{{ $error ?? session('error') }}</span>
    </div>
@endif

@if ($info || session('info'))
    <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
        <span class="font-semibold">Información:</span>
        <span>{{ $info ?? session('info') }}</span>
    </div>
@endif

@if (!empty($warnings))
    <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
        <span class="font-semibold">Advertencias:</span>
        <ul class="list-disc list-inside mt-1">
            @foreach($warnings as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($errorsBag && $errorsBag->any())
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        <span class="font-semibold">Se encontraron los siguientes errores:</span>
        <ul class="list-disc list-inside mt-1">
            @foreach ($errorsBag->all() as $msg)
                <li>{{ $msg }}</li>
            @endforeach
        </ul>
    </div>
@endif


