@props(['success' => null, 'error' => null, 'info' => null, 'warnings' => [], 'errorsBag' => null])

@php
    $errorsBag = $errorsBag ?? $errors;
@endphp

<div class="space-y-3">
    @if ($success || session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm" role="alert">
            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </span>
            <div class="min-w-0"><p class="font-bold">Operación correcta</p><p class="text-emerald-800">{{ $success ?? session('success') }}</p></div>
        </div>
    @endif

    @if ($error || session('error'))
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm" role="alert">
            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-500 text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
            <div class="min-w-0"><p class="font-bold">Ocurrió un error</p><p>{{ $error ?? session('error') }}</p></div>
        </div>
    @endif

    @if ($info || session('info'))
        <div class="flex items-start gap-3 rounded-2xl border border-primary-100 bg-primary-50 px-4 py-3 text-sm text-primary-900 shadow-sm" role="status">
            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0"><p class="font-bold">Información</p><p>{{ $info ?? session('info') }}</p></div>
        </div>
    @endif

    @if (!empty($warnings))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm" role="alert">
            <p class="font-bold">Advertencias</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach($warnings as $warning)<li>{{ $warning }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if ($errorsBag && $errorsBag->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm" role="alert">
            <p class="font-bold">Revise los siguientes errores:</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach ($errorsBag->all() as $msg)<li>{{ $msg }}</li>@endforeach
            </ul>
        </div>
    @endif
</div>
