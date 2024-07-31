<div class="modal fade" id="vendor_package{{$vendor_id}}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">@lang('Packaging')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="packeging-area">
                    @forelse($packaging as $data)
                    <div class="radio-design">
                        <input type="radio" class="packing" view="{{ $curr->sign }}{{ round($data->price * $curr->value,2) }}" data-form="{{$data->title}}"
                            id="free-package{{ $data->id }}" ref="{{$vendor_id}}" data-price="{{ round($data->price * $curr->value,2) }}" name="packeging[{{$vendor_id}}]"
                            value="{{ $data->id }}" {{ ($loop->first) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <label for="free-package{{ $data->id }}">
                            {{ $data->title }}
                            @if($data->price != 0)
                            + {{ $curr->sign }}{{ round($data->price * $curr->value,2) }}
                            @endif
                            <small>{{ $data->subtitle }}</small>
                        </label>
                    </div>
                    @empty
                    <p>
                        @lang('No Packaging Method Available')
                    </p>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mybtn1" data-bs-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>