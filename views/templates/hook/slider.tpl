{if isset($besmartSliderSlides) && $besmartSliderSlides|@count > 0}
<div class="besmartvideoslider" data-module-path="{$besmartSliderModulePath|escape:'htmlall':'UTF-8'}">
  <div class="swiper besmartvideoslider__swiper js-besmartvideoslider-swiper">
    <div class="swiper-wrapper">
      {foreach from=$besmartSliderSlides item=slide}
        <div class="swiper-slide" data-slide-index="{$slide.id_slide|intval}">
          <div class="besmartvideoslider__video-wrapper">
            <video class="besmartvideoslider__video"
              muted
              autoplay
              loop
              playsinline
              preload="metadata"
              data-desktop-src="{$besmartSliderModulePath|escape:'htmlall':'UTF-8'}videos/{$slide.desktop_video|escape:'url'}"
              data-mobile-src="{$besmartSliderModulePath|escape:'htmlall':'UTF-8'}videos/{$slide.mobile_video|escape:'url'}">
            </video>
          </div>
          {if $slide.button_label && $slide.button_url}
            <div class="besmartvideoslider__cta">
              <a class="besmartvideoslider__btn" href="{$slide.button_url|escape:'html':'UTF-8'}">
                {$slide.button_label|escape:'html':'UTF-8'}
              </a>
            </div>
          {/if}
        </div>
      {/foreach}
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</div>
{/if}
