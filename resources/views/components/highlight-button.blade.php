{{--
    Verzendknop die oplicht zodra alle verplichte velden goed zijn ingevuld.
    Gebruik binnen een <form x-data="requiredForm()" @input="check" @change="check">.
    De knop blijft klikbaar, zodat de browser bij een leeg veld zegt wat er nog ontbreekt.
--}}
<button type="submit"
    {{ $attributes->merge(['class' => 'px-6 py-2 font-semibold transition duration-300 bg-[#7d848c] text-white opacity-60']) }}
    :class="complete
        ? '!bg-[#f3b05a] !text-gray-900 !opacity-100 shadow-lg ring-4 ring-[#f3b05a]/40 hover:!bg-[#e09a3e]'
        : 'hover:opacity-80'">
    {{ $slot }}
</button>
