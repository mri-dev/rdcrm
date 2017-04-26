<form class="" action="/forms/payments/" method="post">
  <input type="hidden" name="form" value="1">
  <input type="hidden" name="for" value="remove">
  <input type="hidden" name="return" value="/p/{$p->ID()}">
  <input type="hidden" name="id" value="{if $check}{$check->ID()}{/if}">
  <div class="box-header with-border">
    <h3 class="box-title">
      {$check->Name()} díjbekérő végleges törlése.
    </h3>
  </div>
  <div class="box-body">
    Biztos benne, hogy véglegesen törli a(z) <strong>{$check->Name()}</strong> (#{$check->ID()}) díjbekérőt?
  </div>
  <div class="box-footer">
    <a class="btn btn-default btn-sm pull-left" href="/p/{$p->ID()}/"><i class="fa fa-arrow-left"></i> vissza</a>
    <button type="submit" class="btn btn-danger btn-md pull-right" name="deletePayment">Végleges törlés <i class="fa fa-trash"></i></button>
  </div>
</form>
