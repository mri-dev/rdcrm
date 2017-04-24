<div class="box-header with-border">
  <h3 class="box-title"><i class="fa fa-inbox"></i> Projekt áttekintés</h3>
</div>
<div class="box-body">
  {if $me->isAdmin() || $me->isReferer()}
    <a href="/p/{$p->ID()}/payments/?v=mod&a=add" style="line-height: 32px;" class="pull-right"><i class="fa fa-plus-circle"></i> Új díjbekérő</a>
  {/if}
  <h3 style="margin: 4px 0;">Pénzügyek</h3>
  <div class="clearfix"></div>
  {if $payments->total_amount > 0}
  <table class="table table-striped no-margin">
    <thead>
      <tr>
        <th>Tétel</th>
        <th>Státusz</th>
        <th>Fizetendő</th>
        <th>Fizetési határidő</th>
        <th>Teljesítés ideje</th>
        {if $editor}
          <th class="center"><i class="fa fa-gear"></i></th>
        {/if}
      </tr>
    </thead>
    <tbody>
      {foreach from=$project_payments item=payment}
      <tr>
        <td>{$payment->Name()}</td>
        <td>{$payment->Status(true)}</td>
        <td><strong>{$payment->Amount()|number_format:0:"":" "} {$settings.valuta}</strong></td>
        <td>{$payment->DueDate()}</td>
        <td class="center">{$payment->PaidDate()}</td>
        {if $editor}
          <td class="editor-ai">
            <a href="/p/{$p->ID()}/payments/?v=mod&a=edit&id={$payment->ID()}" title="Szerkesztés"><i class="fa fa-pencil"></i></a> &nbsp;
            <a href="/p/{$p->ID()}/payments/?v=remove&id={$payment->ID()}" title="Végleges törlés"><i class="fa fa-trash"></i></a>
          </td>
        {/if}
      </tr>
      {/foreach}
    </tbody>
  </table>
  <br>
  <div class="progress-group">
    <span class="progress-text">Befizetett díj - {($payments->paid_amount/($payments->total_amount/100))|number_format:0}%</span>
    <span class="progress-number"><b>{$payments->paid_amount|number_format:0:"":" "} {$settings.valuta}</b> / {$payments->total_amount|number_format:0:"":" "} {$settings.valuta}</span>

    <div class="progress md">
      <div class="progress-bar progress-bar-yellow" style="width: {$payments->paid_amount/($payments->total_amount/100)}%"></div>
    </div>
  </div>
  {else}
  <br>
  <div class="alert alert-warning">
    <h4><i class="fa fa-info-circle"></i> Nincs díjbekérő meghatározva.</h4>
    Nincs további teendője.
  </div>
  {/if}
</div>
