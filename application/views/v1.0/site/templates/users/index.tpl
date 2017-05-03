<section class="content-header">
  <h1>Felhasználók</h1>
</section>
<section class="content">
  {if $form && $form->getMsg(1)}
    {$form->getMsg(1)}
  {/if}
  <div class="row">
    <div class="col-md-12">
      <div class="box box-solid">
        <div class="box-body">
          <table class="table table-striped no-margin">
            <thead>
              <tr>
                <th>Név</th>
                <th>Jogkör</th>
                <th>E-mail</th>
                <th class="center">Regisztráció ideje</th>
                <th class="center">Utoljára belépett</th>
                <th class="center">Engedélyezve</th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$userlist.data item=u}
              {assign var="udata" value=$USERS->getData($u.email)}
              <tr>
                <td>{$u.name}</td>
                <td>
                  <span class="label label-{$udata.user_group_color}">{$udata.user_group_text}</span>
                </td>
                <td>{$u.email}</td>
                <td class="center">{$u.register_date|date_format:($settings.date_format|cat:$settings.time_format)}</td>
                <td class="center">{$u.last_login_date|date_format:($settings.date_format|cat:$settings.time_format)}</td>
                <td class="center">{if $u.engedelyezve == '1'}<i class="fa fa-check status-i"></i>{else}<i class="fa fa-times status-i"></i>{/if}</td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
