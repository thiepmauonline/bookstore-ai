@if ($paginator->hasPages())
<nav aria-label="Phân trang sách"><ul class="pagination">
<li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}"><button class="page-link" wire:click="previousPage('page')" @disabled($paginator->onFirstPage()) aria-label="Trang trước">←</button></li>
@foreach ($elements as $element)
@if (is_string($element))<li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>@endif
@if (is_array($element))@foreach ($element as $page => $url)<li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}"><button class="page-link" wire:click="gotoPage({{ $page }}, 'page')" aria-label="Trang {{ $page }}" @if($page == $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</button></li>@endforeach @endif
@endforeach
<li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}"><button class="page-link" wire:click="nextPage('page')" @disabled(!$paginator->hasMorePages()) aria-label="Trang tiếp theo">→</button></li>
</ul></nav>
@endif
