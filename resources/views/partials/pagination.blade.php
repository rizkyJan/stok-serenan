@if($paginator->hasPages())
<div class="pagination-box"><span>Menampilkan {{$paginator->firstItem()}}–{{$paginator->lastItem()}} dari {{$paginator->total()}} data</span><div class="pagination-links">@if($paginator->onFirstPage())<span class="disabled">← Sebelumnya</span>@else<a href="{{$paginator->previousPageUrl()}}">← Sebelumnya</a>@endif <span>Halaman {{$paginator->currentPage()}} / {{$paginator->lastPage()}}</span> @if($paginator->hasMorePages())<a href="{{$paginator->nextPageUrl()}}">Berikutnya →</a>@else<span class="disabled">Berikutnya →</span>@endif</div></div>
@endif
