<div class="col">
    <div class="product type-product">
        <div class="product-wrapper">
            <div class="product-image">
                <a href="{{ route('front.product', $prod->slug) }}" class="woocommerce-LoopProduct-link"><img src="{{ $prod->thumbnail ? asset('assets/images/thumbnails/'.$prod->thumbnail):asset('assets/images/noimage.png') }}" alt="Product Image"></a>
                @if (round($prod->offPercentage() )>0)
                <div class="on-sale">-{{ round($prod->offPercentage() )}}%</div>
                @endif
            </div>
            <div class="product-info">
                <h3 class="product-title"><a href="{{ route('front.product', $prod->slug) }}">{{ $prod->showName() }}</a></h3>
                <div class="product-price">
                    <div class="price">
                        <ins>{{ $prod->showPrice() }} </ins>
                        <del>{{ $prod->showPreviousPrice() }}</del>
                    </div>
                </div>
               
                <div class="shipping-feed-back">
                    <div class="star-rating">
                        <div class="rating-wrap">
                            <p><i class="fas fa-star"></i><span> {{ number_format($prod->ratings_avg_rating,1) }} ({{ $prod->ratings_count }})</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>