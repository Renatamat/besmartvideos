{extends file='helpers/list/list.tpl'}

{block name='list_header'}
<div class="alert alert-info">
  {l s='Drag and drop rows to reorder slides. Videos are not loaded here to keep the back office fast.' mod='besmartvideoslider'}
</div>
{$smarty.block.parent}
{/block}
