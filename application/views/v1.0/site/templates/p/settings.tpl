<form class="" action="/forms/projects/" method="post">
  <input type="hidden" name="form" value="1">
  <input type="hidden" name="for" value="settings">
  <input type="hidden" name="return" value="/p/{$p->ID()}/?page=settings">
  <input type="hidden" name="projectid" value="{$p->ID()}">
  <input type="hidden" name="session_path" value="/p/{$p->ID()}/">

<div class="box-header with-border">
  <h3 class="box-title"><i class="fa fa-inbox"></i> Projekt beállítások</h3>
</div>
<div class="box-body">

    <h3 style="margin: 4px 0;">Alapadatok</h3>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="name">Projekt elnevezése</label>
        <input type="text" class="form-control" id="name" name="name" value="{if $p}{$p->data('name')}{else}{$form->getPost('name')}{/if}">
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="user_id">Projekt tulajdonos</label>
          {assign var="user_list" value=$USERS->getUserList()}
        <select class="form-control" name="user_id" id="user_id">
          <option value="0" selected="selected">---</option>
          {foreach from=$user_list.data item=luser}
            <option value="{$luser.ID}" {if $luser.ID == $p->data('user_id')}selected="selected"{/if}>{$luser.name} ({$luser.email})</option>
          {/foreach}
        </select>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="sandbox_url">Fejlesztői Sandbox URL</label>
        <input type="text" class="form-control" id="sandbox_url" name="sandbox_url" value="{if $p}{$p->data('sandbox_url')}{else}{$form->getPost('sandbox_url')}{/if}">
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="name">Projekt rövid leírása</label>
        <textarea name="description" class="form-control" id="description">{if $p}{$p->data('description')}{else}{$form->getPost('name')}{/if}</textarea>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-12">
        <input type="checkbox" {if $p->isActive()}checked="checked"{/if} id="active" name="active" value="1"> <label for="active"> Aktív projekt</label>
      </div>
    </div>
    <br>
    <h3 style="margin: 4px 0;">API kulcsok</h3>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="trello_id">Trello Board ID - Fejlesztői log kártyák</label>
        <input type="text" class="form-control" id="trello_id" name="trello_id" value="{if $p}{$p->data('trello_id')}{else}{$form->getPost('trello_id')}{/if}">
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="slack_id">Slack Channel ID - Kommunikációs csatorna</label>
        <input type="text" class="form-control" id="slack_id" name="slack_id" value="{if $p}{$p->data('slack_id')}{else}{$form->getPost('slack_id')}{/if}">
      </div>
    </div>
</div>
<div class="box-footer">
  <a class="btn btn-default btn-sm pull-left" href="/p/{$p->ID()}/"><i class="fa fa-arrow-left"></i> vissza</a>
  <button type="submit" class="btn btn-primary btn-md pull-right" name="saveProjectSettings">Mentés <i class="fa fa-save"></i></button>
</div>


</form>
