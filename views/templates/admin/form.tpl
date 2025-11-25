{extends file='helpers/form/form.tpl'}

{block name='field'}
  {if $input.type == 'file' && $input.lang}
    <div class="form-group">
      <label class="control-label col-lg-3">{$input.label}</label>
      <div class="col-lg-9">
        {foreach from=$languages item=language}
          <div class="translatable-field lang-{$language.id_lang}" style="margin-bottom:10px;">
            <div class="col-lg-9">
              <input type="file" name="{$input.name}_{$language.id_lang}" class="form-control">
            </div>
            <div class="col-lg-2">
              <span class="label label-default">{$language.iso_code}</span>
            </div>
          </div>
        {/foreach}
      </div>
    </div>
  {else}
    {$smarty.block.parent}
  {/if}
{/block}
