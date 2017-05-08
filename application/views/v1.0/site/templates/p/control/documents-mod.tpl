<form class="" action="/forms/documents/" method="post">
  <input type="hidden" name="form" value="1">
  <input type="hidden" name="for" value="{$smarty.get.a}">
  <input type="hidden" name="return" value="{if $check}/p/{$p->ID()}{else}/p/{$p->ID()}/payments/?v=mod&a=add{/if}">
  <input type="hidden" name="id" value="{if $check}{$check->ID()}{/if}">
  <input type="hidden" name="projectid" value="{$p->ID()}">
  <input type="hidden" name="session_path" value="/p/{$p->ID()}/?page=docs">
  <div class="box-header with-border">
    <h3 class="box-title">
      {if $smarty.get.a == 'add'}
        <i class="fa fa-plus-circle"></i> Dokumentum feltöltése
      {elseif $smarty.get.a == 'edit'}
        <i class="fa fa-pencil-square-o"></i> {if $check}<strong>{$check->Name()}</strong>{/if} dokumentum szerkesztése
      {/if}
    </h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-12">
        <label for="name">Dokumentum elnevezése</label>
        <input type="text" class="form-control" id="name" name="name" value="{if $check}{$check->data('name')}{else}{$form->getPost('name')}{/if}">
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="file_path">Dokumentum elérhetősége</label>
        <div class="input-group">
            <input type="text" class="form-control" id="file_path" name="file_path" value="{if $check}{$check->data('file_path')}{else}{$form->getPost('file_path')}{/if}">
            <span class="input-group-btn">
              <a class="iframe-btn btn btn-default" data-fancybox-type="iframe" href="/plugins/tinymce/plugins/filemanager/dialog.php?type=2&lang=hu_HU&field_id=file_path"><i class="fa fa-folder-open-o"></i></a>
            </span>
        </div>
      </div>
    </div>
    <br>
  </div>
  <div class="box-footer">
    <a class="btn btn-default btn-sm pull-left" href="/p/{$p->ID()}/?page=docs"><i class="fa fa-arrow-left"></i> vissza</a>
    <button type="submit" class="btn btn-primary btn-md pull-right" name="autosaveDocuments">Mentés <i class="fa fa-save"></i></button>
  </div>
</form>

{if $check}
</div>
<div class="box">
  <div class="box-header">
    <h3 class="box-title"><i class="fa fa-gears"></i> Műveletek</h3>
  </div>
  <div class="box-body">
    <form class="" action="/forms/payments" method="post">
      <input type="hidden" name="form" value="1">
      <input type="hidden" name="for" value="actionsave">
      <input type="hidden" name="return" value="/p/{$p->ID()}">
      <input type="hidden" name="id" value="{$check->ID()}">
      <div class="row">
        <div class="col-md-12">
          <button type="submit" name="delete" value="1" class="btn btn-danger form-control" ><i class="fa fa-trash"></i> Dokumentum törlése</button>
        </div>
      </div>
    </form>
  </div>
</div>
{/if}
