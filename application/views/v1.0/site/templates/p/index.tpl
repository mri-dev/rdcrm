<section class="content-header">
  <h1>{$p->Name()} <small>{$p->Author()}</small></h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="box box-solid">
        <div class="box-header with-border">
          Navigáció
        </div>
        <div class="box-body">
          <ul class="project-menu">
            <li class="{if $smarty.get.page == ''}active{/if}"><a href="/p/{$p->ID()}/"><i class="fa fa-dashboard"></i>  {$lng_projekt} {$lng_osszesito}</a></li>
            <li class="{if $smarty.get.page == 'cards'}active{/if}"><a href="/p/{$p->ID()}/?page=cards"><i class="fa fa-trello"></i> Fejlesztői log</a></li>
            <li class="{if $smarty.get.page == 'chat'}active{/if}"><a href="/p/{$p->ID()}/?page=chat"><i class="fa fa-comments-o"></i> Kommunikáció</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="box box-solid">
        {if $smarty.get.page == ''}
          {include file="$template_root/p/overview.tpl"}
        {else}
            {include file="$template_root/p/"|cat:$smarty.get.page|cat:".tpl"}
        {/if}
      </div>
    </div>
  </div>
</section>
