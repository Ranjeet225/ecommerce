<div class="modal fade" id="add-product" tabindex="-1" role="dialog" aria-labelledby="billing-details-edit" aria-hidden="true">						
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="submit-loader">
                <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
            </div>
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Product') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="content-area">

                    <div class="add-product-content1">
                        <div class="row d-block text-center" id="product-show">

                        </div>


                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="addProductRemoveBtn" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>

</div>
