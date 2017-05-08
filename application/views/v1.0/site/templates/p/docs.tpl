<div class="box-header with-border">
  <div class="pull-right">
    <a href="/p/{$p->ID()}/documents/?v=mod&a=add"><i class="fa fa-plus-circle"></i> új dokumentum</a>
  </div>
  <h3 class="box-title"><i class="fa fa-files-o"></i> Dokumentumok</h3>
</div>
<div class="box-body">
  <div class="clearfix"></div>
  {if $documents_list}
  <table class="table table-striped no-margin">
    <thead>
      <tr>
        <th>Dokumentum neve</th>
        <th>Kiterjesztés</th>
        <th>Fájlméret</th>
        <th>Feltöltve</th>
        {if $editor}
          <th class="center"><i class="fa fa-gear"></i></th>
        {/if}
      </tr>
    </thead>
    <tbody>
      {foreach from=$documents_list item=document}
      <tr>
        <td><strong><a href="/p/{$p->ID()}/documents/?v=open&doc={$document->Hashkey()}">{$document->Name()}</a></strong></td>
        <td>{$document->Extension()}</td>
        <td>{$document->Size()}</td>
        <td>{$document->Uploaded()}</td>
        {if $editor}
          <td class="editor-ai">
            <a href="/p/{$p->ID()}/documents/?v=mod&a=edit&id={$document->Hashkey()}" title="Szerkesztés"><i class="fa fa-pencil"></i></a> &nbsp;
            <a href="/p/{$p->ID()}/documents/?v=remove&id={$document->Hashkey()}" title="Végleges törlés"><i class="fa fa-trash"></i></a>
          </td>
        {/if}
      </tr>
      {/foreach}
    </tbody>
  </table>
  {else}
  <br>
  <div class="no-data">
    <h4><i class="fa fa-bell-o"></i> Nincs dokumentum feltöltve.</h4>
  </div>
  {/if}
</div>
