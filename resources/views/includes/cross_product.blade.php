<div class="products product-style-1">
   <div
      class="row g-4 row-cols-xl-4 row-cols-md-3 row-cols-sm-2 row-cols-1 e-title-general e-title-hover-primary e-image-bg-light e-hover-image-zoom e-info-center">

      @foreach($cross_products as $rproduct)
      <div class="product type-product">
         <div class="product-wrapper">
            <div class="product-image">

               <a href="{{ route('front.product', $rproduct->slug) }}" class="woocommerce-LoopProduct-link"><img
                     src="{{ $rproduct->thumbnail ? asset('assets/images/thumbnails/'.$rproduct->thumbnail):asset('assets/images/noimage.png') }}"
                     alt="Product Image"></a>
               @if(!empty($rproduct->features))
               <div class="product-variations">
                  @foreach($rproduct->features as $key => $data1)
                  <span class="active sale"><a href="#" style="background-color: {{ $rproduct->colors[$key] }}">{{
                        $rproduct->features[$key] }}</a></span>
                  @endforeach
               </div>
               @endif
               @if (round($rproduct->offPercentage() )>0)
               <div class="on-sale">- {{ round($rproduct->offPercentage() )}}%</div>
               @endif


            </div>
            <div class="product-info">
               <h3 class="product-title"><a href="{{ route('front.product', $rproduct->slug) }}">{{
                     $rproduct->showName() }}</a></h3>
               <div class="product-price">
                  <div class="price">
                     <ins>{{ $rproduct->showPrice() }} </ins>
                     <del>{{ $rproduct->showPreviousPrice() }}</del>
                  </div>
               </div>
               <div class="shipping-feed-back">
                  <div class="star-rating">
                     <div class="rating-wrap">
                        <p><i class="fas fa-star"></i><span> {{ number_format($rproduct->ratings_avg_rating,1) }} ({{
                              $rproduct->ratings_count }})</span></p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      @endforeach
   </div>
</div>