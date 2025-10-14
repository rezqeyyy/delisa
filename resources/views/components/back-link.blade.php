@props(['to' => null, 'label' => '← Kembali'])
@php($href = $to ?? (url()->previous() === url()->current() ? route('login') : url()->previous()))
<a href="{{ $href }}"
   class="px-4 py-2 rounded-full border border-[#D91A8B] text-[#D91A8B] text-sm hover:bg-[#fff0f7] inline-flex items-center">
  {{ $label }}
</a>
