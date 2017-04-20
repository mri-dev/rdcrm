<div class="box-header with-border">
  <h3 class="box-title"><i class="fa fa-inbox"></i> Projekt áttekintés</h3>
</div>
<div class="box-body">
  <h3 style="margin: 4px 0;">Pénzügyek</h3>
  <table class="table table-striped no-margin">
    <thead>
      <tr>
        <th>Tétel</th>
        <th>Státusz</th>
        <th>Fizetendő</th>
        <th>Fizetési határidő</th>
        <th>Teljesítés ideje</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$project_payments item=payment}
      <tr>
        <td>{$payment->Name()}</td>
        <td>{$payment->Status(true)}</td>
        <td><strong>{$payment->Amount()|number_format:0:"":" "} {$settings.valuta}</strong></td>
        <td>{$payment->DueDate()}</td>
        <td>{$payment->PaidDate()}</td>
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
</div>
