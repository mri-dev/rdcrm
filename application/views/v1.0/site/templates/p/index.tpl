<section class="content-header">
  <h1>{$p->Name()} <small>{$p->Author()}</small></h1>
</section>
<section class="content">
  {if $form && $form->getMsg(1)}
    {$form->getMsg(1)}
  {/if}
  <div class="row">
    <div class="col-md-3">
      <div class="box box-solid">
        <div class="box-header with-border">
          Navigáció
        </div>
        <div class="box-body">
          <ul class="project-menu">
            {if $me->isAdmin()}
              <li class="{if $smarty.get.page == 'settings'}active{/if}"><a href="/p/{$p->ID()}/?page=settings"><i class="fa fa-gears"></i> Beállítások</a></li>
            {/if}
            <li class="{if $smarty.get.page == ''}active{/if}"><a href="/p/{$p->ID()}/"><i class="fa fa-dashboard"></i>  {$lng_projekt} {$lng_osszesito}</a></li>
            {if $p->TrelloBoardID()}
            <li class="{if $smarty.get.page == 'cards'}active{/if}"><a href="/p/{$p->ID()}/?page=cards"><i class="fa fa-trello"></i> Fejlesztői log</a></li>
            {/if}
            {if $p->SlackChannelID()}
            <li class="{if $smarty.get.page == 'chat'}active{/if}"><a href="/p/{$p->ID()}/?page=chat"><i class="fa fa-comments-o"></i> Kommunikáció</a></li>
            {/if}
            <li class="{if $smarty.get.page == 'docs'}active{/if}"><a href="/p/{$p->ID()}/?page=docs"><i class="fa fa-files-o"></i> Dokumentumok <span class="badge bg-red pull-right">{count($documents_list)}</span></a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="box box-solid">
        {if $controlpages && !empty($smarty.get.v)}
          {include file="$template_root/p/control/"|cat:$controlpages|cat:"-"|cat:$smarty.get.v|cat:".tpl"}
        {else}
          {if $smarty.get.page == ''}
            {include file="$template_root/p/overview.tpl"}
          {else}
              {include file="$template_root/p/"|cat:$smarty.get.page|cat:".tpl"}
          {/if}
        {/if}
      </div>
    </div>
  </div>
</section>
