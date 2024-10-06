@props(['label', 'value', 'color' => 'green'])

<a {{ $attributes->merge(['class' => 'flex flex-row']) }}>
    <span
        class="flex items-center px-1 py-0.5 text-xs text-white rounded-l-sm bg-zinc-800 text-center dark:bg-slate-600">{{ $label }}</span>
    <span @class([
        'flex items-center px-1 py-0.5 text-xs rounded-r-sm text-white text-center ',
        'bg-zinc-800' => $color == 'gray',
        'bg-slate-500' => $color == 'slate',
        'bg-red-700' => $color == 'red',
        'bg-green-600' => $color == 'green',
        'bg-blue-500' => $color == 'blue',
        'bg-yellow-600' => $color == 'yellow',
        'bg-amber-800' => $color == 'amber',
        'bg-indigo-500' => $color == 'indigo',
        'bg-purple-500' => $color == 'purple',
    ])>{{ $value }}</span>
</a>
