<form class="" action="/forms/user/" method="post">
  <input type="hidden" name="form" value="1">
  <input type="hidden" name="for" value="creator">
  <input type="hidden" name="return" value="{if $edituser}/users/edit/{$edituser->getID()}{else}/users{/if}">
  <input type="hidden" name="id" value="{if $edituser}{$edituser->getID()}{/if}">
  <input type="hidden" name="session_path" value="/users/">

  <section class="content-header">
    <h1>{if $smarty.get.action == 'create'}Új felhasználó hozzáadása{else}<strong>{$edituser->getName()}</strong> adatainak szerkesztése{/if}</h1>
  </section>
  <section class="content">
    <div class="box box-warning">
      <div class="box-body">
        asd
      </div>
      <div class="box-footer">
        <a class="btn btn-default btn-sm pull-left" href="/users"><i class="fa fa-arrow-left"></i> vissza</a>
        <button type="submit" class="btn btn-primary btn-md pull-right" name="autosaveUser">Művelet végrehajtása <i class="fa fa-save"></i></button>
      </div>
    </div>
  </section>
</form>
