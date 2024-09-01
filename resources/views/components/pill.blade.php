@props(['label', 'value', 'color' => 'green'])

<a {{ $attributes->merge(['class' => 'flex flex-row']) }}>
    <span class="px-1 py-0.5 text-xs text-white rounded-l-sm bg-slate-700">{{ $label }}</span>
    <span @class([
        'px-1 py-0.5 text-xs rounded-r-sm text-white flex',
        'bg-slate-500' => $color == 'slate',
        'bg-red-700' => $color == 'red',
        'bg-green-500' => $color == 'green',
        'bg-blue-500' => $color == 'blue',
        'bg-yellow-600' => $color == 'yellow',
        'bg-amber-800' => $color == 'amber',
        'bg-indigo-500' => $color == 'indigo',
        'bg-purple-500' => $color == 'purple',
    ])>{{ $value }}</span>
</a>
