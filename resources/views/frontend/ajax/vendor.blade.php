{{-- <div id="ajaxContent"> --}}

@foreach($vprods as $prod)
<div class="col-lg-12">
    @include('partials.product.product-different-view')
</div>
@endforeach

<div class="col-lg-12">
    <div class="page-center category">
        {!! $vprods->appends(['sort' => request()->input('sort'), 'min' => request()->input('min'), 'max' =>
        request()->input('max')])->links() !!}
    </div>
</div>
{{-- </div> --}}

<script>

    // Tooltip Section

    $('[data-toggle="tooltip"]').tooltip({});

    $('[rel-toggle="tooltip"]').tooltip();

    $('[data-toggle="tooltip"]').on('click', function () {
        $(this).tooltip('hide');
    })


    $('[rel-toggle="tooltip"]').on('click', function () {
        $(this).tooltip('hide');
    })

    // Tooltip Section Ends
</script>
