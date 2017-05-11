<section class="content-header">
  <h1>Gépház</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-md-6">
      <div class="box box-warning">
        <div class="box-header">
          <h3 class="box-title"><i class="fa fa-money"></i> 15 napon belül esedékes befizetések</h3>
        </div>
        <div class="box-body">
          {if count($actual_payments) > 0}
          <table class="table table-striped no-margin smaller-table">
            <thead>
              <tr>
                <th>Díjbekérő</th>
                <th>Projekt</th>
                <th>Fizetendő</th>
                <th>Fizetési határidő</th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$actual_payments item=payment}
              <tr>
                <td>{$payment->Name()}</td>
                <td><a href="/p/{$payment->ProjectID()}">{$payment->ProjectName()}</a></td>
                <td><strong>{$payment->Amount()|number_format:0:"":" "} {$settings.valuta}</strong> + ÁFA</td>
                <td>{$payment->DueDate()}</td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          {else}
          <div class="no-data smaller">
            <h4><i class="fa fa-calendar-check-o"></i> Nincs esedékes befizetésre váró díjbekérő.</h4>
          </div>
          {/if}
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="box box-success">
        <div class="box-header">
          <h3 class="box-title"><i class="fa fa-check-circle-o"></i> Utoljára befizetett díjak</h3>
        </div>
        <div class="box-body">
          {if count($paid_payments) > 0}
          <table class="table table-striped no-margin smaller-table">
            <thead>
              <tr>
                <th>Díjbekérő</th>
                <th>Befizetve</th>
                <th>Befizetés ideje</th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$paid_payments item=payment}
              <tr>
                <td>
                  <div><strong>{$payment->Name()}</strong></div>
                  <a href="/p/{$payment->ProjectID()}">{$payment->ProjectName()}</a>
                </td>
                <td><strong>{$payment->Amount()|number_format:0:"":" "} {$settings.valuta}</strong> + ÁFA</td>
                <td>{$payment->PaidDate()}</td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          {else}
          <div class="no-data smaller">
            <h4><i class="fa fa-file-text-o"></i> Nincs befizetett díjbekérője.</h4>
          </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</section>
