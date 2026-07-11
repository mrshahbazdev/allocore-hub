@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
