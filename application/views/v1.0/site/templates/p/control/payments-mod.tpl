<form class="" action="/forms/payments/" method="post">
  <input type="hidden" name="form" value="1">
  <input type="hidden" name="for" value="{$smarty.get.a}">
  <input type="hidden" name="return" value="{if $check}/p/{$p->ID()}/payments/{else}/p/{$p->ID()}{/if}">
  <input type="hidden" name="id" value="{if $check}{$check->ID()}{/if}">
  <div class="box-header with-border">
    <h3 class="box-title">
      {if $smarty.get.a == 'add'}
        <i class="fa fa-plus-circle"></i> Díjbekérő létrehozása
      {elseif $smarty.get.a == 'edit'}
        <i class="fa fa-pencil-square-o"></i> {if $check}<strong>{$check->Name()}</strong>{/if} díjbekérő szerkesztése
      {/if}
    </h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-12">
        <label for="name">Díjbekérő megnevezése</label>
        <input type="text" class="form-control" id="name" name="name" value="{if $check}{$check->data('name')}{/if}">
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-md-8">
        <label for="due_date">Határidő nap</label>
        <input type="text" class="form-control datepicker" id="due_date" name="due_date" value="{if $check}{$check->data('due_date')|date_format:"%Y-%m-%d"}{/if}">
      </div>
      <div class="col-md-4">
        <label for="due_date_time">Határidő ideje</label>
        <div class="input-group bootstrap-timepicker timepicker">
          <input type="text" class="form-control timepicker" id="due_date_time" name="due_date_time" value="{if $check}{$check->data('due_date')|date_format:"%R:%S"}{/if}">
          <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
        </div>
      </div>
    </div>
    {if $check && $check->isCompleted()}
    <br>
    <div class="row">
      <div class="col-md-8">
        <label for="paid_date">Befizetés napja</label>
        <input type="text" class="form-control datepicker" id="paid_date" name="paid_date" value="{if $check}{$check->data('paid_date')|date_format:"%Y-%m-%d"}{/if}">
        {if is_null($check->data('paid_date'))}
          <p class="text-red help-block">A díjbekérő még befizetetlen.</p>
        {else}
          <p class="text-success help-block">A díjbekérő teljesítésre került.</p>
        {/if}
      </div>
      <div class="col-md-4">
        <label for="paid_date">Befizetés időpont</label>
        <div class="input-group bootstrap-timepicker timepicker">
          <input type="text" class="form-control timepicker" id="paid_date" name="paid_date_time" value="{if $check}{$check->data('paid_date')|date_format:"%R:%S"}{/if}">
          <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
        </div>
      </div>
    </div>
    {/if}
    <br>
    <div class="row">
      <div class="col-md-12">
        <label for="amount">Díjbekérő összege</label>
        <input type="text" class="form-control" id="amount" name="amount" value="{if $check}{$check->data('amount')}{/if}">
      </div>
    </div>
  </div>
  <div class="box-footer">
    <a class="btn btn-default btn-sm pull-left" href="/p/{$p->ID()}/"><i class="fa fa-arrow-left"></i> vissza</a>
    <button type="submit" class="btn btn-primary btn-md pull-right" name="autosavePayments">Mentés <i class="fa fa-save"></i></button>
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
        <div class="col-md-6">
          {if $check->isCompleted()}
            <button type="submit" name="setUncompleted" value="1" class="btn btn-warning form-control"><i class="fa fa-dot-circle-o"></i> Befizetetlenné jelölés</button>
          {else}
            <button type="submit" name="setCompleted" value="1" class="btn btn-success form-control"><i class="fa fa-dot-circle-o"></i> Befizetetté jelölés</button>
          {/if}
        </div>
        <div class="col-md-6">
          <button type="submit" name="delete" value="1" class="btn btn-danger form-control" ><i class="fa fa-trash"></i> Díjbekérő törlése</button>
        </div>
      </div>
    </form>
  </div>
</div>
{/if}
