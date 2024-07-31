@php
    $categories = App\Models\Category::with('subs')->withCount('subs')->where('status',1)->get();
    
@endphp
<div class="col-xl-3">
    <div id="sidebar" class="widget-title-bordered-full">
        <div class="dashbaord-sidebar-close d-xl-none">
    <i class="fas fa-times"></i>
  </div>
        <form id="catalogForm" action="{{ route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')]) }}" method="GET">

        <div id="woocommerce_product_categories-4" class="widget woocommerce widget_product_categories widget-toggle">
            <h2 class="widget-title">{{ __('Product categories') }}</h2>
            <ul class="product-categories">
                @foreach ($categories as $category)
                <li class="cat-item cat-parent">
                    <a href="{{route('front.category', $category->slug)}}{{!empty(request()->input('search')) ? '?search='.request()->input('search') : ''}}" class="category-link" id="cat">{{ $category->name }} <span class="count"></span></a>

                    @if($category->subs_count > 0)
                        <span class="has-child"></span>
                        <ul class="children">
                            @foreach ($category->subs()->get() as $subcategory)
                            <li class="cat-item cat-parent">
                                <a href="{{route('front.category', [$category->slug, $subcategory->slug])}}{{!empty(request()->input('search')) ? '?search='.request()->input('search') : ''}}" class="category-link {{ isset($subcat) ? ($subcat->id == $subcategory->id ? 'active' : '') : '' }}">{{$subcategory->name}} <span class="count"></span></a>

                                @if($subcategory->childs->count()!=0)
                                    <span class="has-child"></span>
                                    <ul class="children">
                                        @foreach ($subcategory->childs()->get() as $key => $childelement)
                                        <li class="cat-item ">
                                            <a href="{{route('front.category', [$category->slug, $subcategory->slug, $childelement->slug])}}{{!empty(request()->input('search')) ? '?search='.request()->input('search') : ''}}" class="category-link {{ isset($childcat) ? ($childcat->id == $childelement->id ? 'active' : '') : '' }}"> {{$childelement->name}} <span class="count"></span></a>
                                        </li>
                                        @endforeach
                                    </ul>

                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

        <div id="bigbazar-price-filter-list-1" class="widget bigbazar_widget_price_filter_list widget_layered_nav widget-toggle mx-3">
            <h2 class="widget-title">{{ __('Filter by Price') }}</h2>
            <ul class="price-filter-list">
                <div class="price-range-block">
                    <div id="slider-range" class="price-filter-range" name="rangeInput"></div>
                    <div class="livecount">
                        <input type="number" name="min"  oninput="" id="min_price" class="price-range-field" />
                        <span>
                            {{ __('To') }}
                        </span>
                        <input type="number" name="max"  oninput="" id="max_price" class="price-range-field" />
                    </div>
                </div>

                <button class="filter-btn btn btn-primary mt-3 mb-4" type="submit">{{ __('Search') }}</button>
            </ul>
        </div>

    </form>


    @if ((!empty($cat) && !empty(json_decode($cat->attributes, true))) || (!empty($subcat) && !empty(json_decode($subcat->attributes, true))) || (!empty($childcat) && !empty(json_decode($childcat->attributes, true))))

    <form id="attrForm" action="{{ route('front.category',[Request::route('category'),Request::route('subcategory'),Request::route('childcategory')]) }}" method="post">

        @if (!empty($cat) && !empty(json_decode($cat->attributes, true)))
        @foreach ($cat->attributes as $key => $attr)

        <div id="bigbazar-attributes-filter-{{$attr->name}}" class="widget woocommerce bigbazar-attributes-filter widget_layered_nav widget-toggle">
            <h2 class="widget-title">{{$attr->name}}</h2>
            <ul class="swatch-filter-pa_color">
                @if (!empty($attr->attribute_options))
                      @foreach ($attr->attribute_options as $key => $option)
                      <div class="form-check ml-0 pl-0">
                        <input name="{{$attr->input_name}}[]" class="form-check-input attribute-input" type="checkbox" id="{{$attr->input_name}}{{$option->id}}" value="{{$option->name}}">
                        <label class="form-check-label" for="{{$attr->input_name}}{{$option->id}}">{{$option->name}}</label>
                      </div>
                      @endforeach
                    @endif
            </ul>
        </div>
        @endforeach
        @endif

        @if (!empty($subcat) && !empty(json_decode($subcat->attributes, true)))
            @foreach ($subcat->attributes as $key => $attr)
                <div id="bigbazar-attributes-filter-{{$attr->name}}" class="widget woocommerce bigbazar-attributes-filter widget_layered_nav widget-toggle">
                    <h2 class="widget-title">{{$attr->name}}</h2>
                    <ul class="swatch-filter-pa_color">
                        @if (!empty($attr->attribute_options))
                              @foreach ($attr->attribute_options as $key => $option)
                              <div class="form-check ml-0 pl-0">
                                <input name="{{$attr->input_name}}[]" class="form-check-input attribute-input" type="checkbox" id="{{$attr->input_name}}{{$option->id}}" value="{{$option->name}}">
                                <label class="form-check-label" for="{{$attr->input_name}}{{$option->id}}">{{$option->name}}</label>
                              </div>
                              @endforeach
                            @endif
                    </ul>
                </div>
            @endforeach
        @endif

    @if (!empty($childcat) && !empty(json_decode($childcat->attributes, true)))
        @foreach ($childcat->attributes as $key => $attr)
            <div id="bigbazar-attributes-filter-{{$attr->name}}" class="widget woocommerce bigbazar-attributes-filter widget_layered_nav widget-toggle px-3">
                <h2 class="widget-title">{{$attr->name}}</h2>
                <ul class="swatch-filter-pa_color">
                    @if (!empty($attr->attribute_options))
                          @foreach ($attr->attribute_options as $key => $option)
                          <div class="form-check ml-0 pl-0">
                            <input name="{{$attr->input_name}}[]" class="form-check-input attribute-input" type="checkbox" id="{{$attr->input_name}}{{$option->id}}" value="{{$option->name}}">
                            <label class="form-check-label" for="{{$attr->input_name}}{{$option->id}}">{{$option->name}}</label>
                          </div>
                          @endforeach
                        @endif
                </ul>
            </div>
        @endforeach
     @endif

    </form>
    @endif
        <div class="row mx-0">
            <div class="col-12">
                <div class="section-head border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex section-head-side-title">
                        <h5 class="font-700 text-dark mb-0">{{ __('Recent Product') }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="product-style-2 owl-carousel owl-nav-hover-primary nav-top-right single-carousel dot-disable product-list e-bg-white">
                
                    @foreach ($latest_products as $item)

                    <div class="item">
                        <div class="row row-cols-1">
                       
                            @foreach ($item as $prod)
                      
                            <div class="col mb-1">
                                <div class="product type-product">
                                    <div class="product-wrapper">
                                        <div class="product-image">
                                            <a href="{{ route('front.product', $prod['slug']) }}" class="woocommerce-LoopProduct-link"><img src="{{ $prod['thumbnail'] ? asset('assets/images/thumbnails/'.$prod['thumbnail'] ):asset('assets/images/noimage.png') }}" alt="Product Image"></a>
                                            <div class="wishlist-view">
                                                <div class="quickview-button">
                                                    <a class="quickview-btn" href="{{ route('front.product', $prod['slug']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Quick View" aria-label="Quick View">{{ __('Quick View') }}</a>
                                                </div>
                                                <div class="whishlist-button">
                                                    <a class="add_to_wishlist" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Wishlist') }}" data-bs-original-title="Add to Wishlist" aria-label="Add to Wishlist">{{ __('Wishlist') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-title"><a href="{{ route('front.product', $prod['slug']) }}">{{ $prod['name']  }}</a></h3>
                                            <div class="product-price">
                                                <div class="price">
                                                    <ins>{{ PriceHelper::showPrice($prod['price'])  }}</ins>
                                                    <del>{{ PriceHelper::showPrice($prod['previous_price'])  }}</del>
                                                </div>
                                                <div class="on-sale"><span>{{ round($prod->offPercentage())}}</span><span>% off</span></div>
                                            </div>
                                            <div class="shipping-feed-back">
                                                <div class="star-rating">
                                                    <div class="rating-wrap">
                                                        <p><i class="fas fa-star"></i><span>  {{ number_format($prod->ratings_avg_rating,1) }}</span></p>
                                                    </div>
                                                    <div class="rating-counts-wrap">
                                                        <p>({{ $prod->ratings_count }})</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
