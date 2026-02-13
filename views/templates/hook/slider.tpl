{if isset($besmartSliderSlides) && $besmartSliderSlides|@count > 0}
<div class="besmartvideoslider besmartvideoslider--{$besmartSliderVariant|default:'small'|escape:'html':'UTF-8'}" data-module-path="{$besmartSliderModulePath|escape:'htmlall':'UTF-8'}" data-placement="{$besmartSliderPlacement|escape:'html':'UTF-8'}">
  <div class="swiper besmartvideoslider__swiper js-besmartvideoslider-swiper">
    <div class="swiper-wrapper">
      {foreach from=$besmartSliderSlides item=slide}
        <div class="swiper-slide" data-slide-index="{$slide.id_slide|intval}">
          <div class="besmartvideoslider__video-wrapper">
            <video class="besmartvideoslider__video"
              muted
              loop
              playsinline
              preload="none"
              data-desktop-src="{$slide.desktop_video_src}"
              data-mobile-src="{$slide.mobile_video_src}"
              data-desktop-poster="{$slide.desktop_poster_src}"
              data-mobile-poster="{$slide.mobile_poster_src}"
            >
            </video>

            {if $slide.button_label && $slide.button_url}
              <a
                href="{$slide.button_url|escape:'html':'UTF-8'}"
                class="besmartvideoslider__desktop-link d-none d-md-block"
                aria-label="{$slide.button_label|escape:'html':'UTF-8'}"
              ></a>
            {/if}
          </div>

          {if $slide.button_label && $slide.button_url}
            <div class="video-overlay besmartvideoslider__cta d-md-none">
              <a class="besmartvideoslider__btn iqit-show-all btn btn-link" href="{$slide.button_url|escape:'html':'UTF-8'}">
                  <span class="icon-grid fs-24"></span> {$slide.button_label|escape:'html':'UTF-8'}
              </a>
            </div>
          {/if}
        </div>
      {/foreach}
    </div>

    <div class="swiper-pagination"></div>
  </div>
</div>
{/if}
