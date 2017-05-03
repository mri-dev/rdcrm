<form action="/forms/projects" method="post">
  <input type="hidden" name="form" value="1">
  <input type="hidden" name="for" value="create">
  <input type="hidden" name="return" value="/new_project">
  <input type="hidden" name="session_path" value="/new_project">

  <section class="content-header">
    <h1>Új projekt létrehozása</h1>
  </section>
  <section class="content">
    {if $form && $form->getMsg(1)}
      {$form->getMsg(1)}
    {/if}
    <div class="row">
      <div class="col-md-12">
        <div class="box box-solid">
          <div class="box-header">
            <h3 class="box-title"> <i class="fa fa-bars"></i> Projekt alapadatok megadása</h3>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-12">
                <label for="name">Projekt elnevezése</label>
                <input type="text" class="form-control" id="name" name="name" value="{$form->getPost('name')}">
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
                    <option value="{$luser.ID}">{$luser.name} ({$luser.email})</option>
                  {/foreach}
                </select>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-12">
                <label for="sandbox_url">Fejlesztői Sandbox URL</label>
                <input type="text" class="form-control" id="sandbox_url" name="sandbox_url" value="{$form->getPost('sandbox_url')}">
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-12">
                <label for="name">Projekt rövid leírása</label>
                <textarea name="description" class="form-control" id="description">{$form->getPost('name')}</textarea>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-12">
                <input type="checkbox" id="active" name="active" value="1"> <label for="active"> Aktív projekt</label>
              </div>
            </div>
            <br>
            <h3 style="margin: 4px 0;">API kulcsok</h3>
            <br>
            <div class="row">
              <div class="col-md-12">
                <label for="trello_id">Trello Board ID - Fejlesztői log kártyák</label>
                <input type="text" class="form-control" id="trello_id" name="trello_id" value="{$form->getPost('trello_id')}">
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-12">
                <label for="slack_id">Slack Channel ID - Kommunikációs csatorna</label>
                <input type="text" class="form-control" id="slack_id" name="slack_id" value="{$form->getPost('slack_id')}">
              </div>
            </div>
          </div>
          <div class="box-footer">
            <a class="btn btn-default btn-sm pull-left" href="/"><i class="fa fa-arrow-left"></i> vissza</a>
            <button type="submit" class="btn btn-success btn-md pull-right" name="addProject">Projekt létrehozása <i class="fa fa-plus"></i></button>
          </div>
        </div>
      </div>
    </div>
  </section>
</form>
