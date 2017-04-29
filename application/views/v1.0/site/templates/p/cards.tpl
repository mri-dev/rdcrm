<div class="box-header with-border">
  <h3 class="box-title"><i class="fa fa-trello"></i> Fejlesztői log</h3>
</div>
<div class="box-body">
  {if $p->TrelloBoardID() == ''}
    <div class="no-trello-support">
      <div class="alert alert-warning">
        <h4><i class="fa fa-info-circle"></i> Rendszerüzenet</h4>
        Nincs fejlesztői log konfigurálva ehhez a projekthez.
      </div>
    </div>
  {else}
  <div id="trello-list-holder" class="trello-lists"></div>
  {/if}
</div>
{if $p->TrelloBoardID() != ''}
{literal}
<script type="text/javascript">
var authenticationSuccess = function() {
  Trello.get('/boards/{/literal}{$p->TrelloBoardID()}{literal}/lists', loadLists, error);
};
var authenticationFailure = function() {

};
var loadLists = function(lists) {
  $.each(lists, function(i,list){
    Trello.get('/lists/'+list.id+"/cards", loadCards, error);
    $('#trello-list-holder').append('<div class="list list-'+list.name+'" data-list="'+list.id+'"><h2>'+list.name+'</h2><div class="cards" id="trello-list'+list.id+'-cards"></div></div>');
  });
};

var loadCards = function(cards) {
  $.each(cards, function(i,card){
    $('#trello-list'+card.idList+'-cards').append('<div class="card" data-card="'+card.id+'"><div class="title">'+card.name+'</div>'+((card.due)?'<span class="due '+((card.dueComplete)?'duesuccess':'')+'">'+((card.dueComplete)?'<i class="fa fa-check"></i> ':'<i class="fa fa-clock-o"></i> ')+$.datepicker.formatDate('yy/mm/dd', new Date(card.due))+'</span>':'')+((card.desc != '')?'<div class="desc">'+card.desc+'</div>':'')+'<div class="checklists" id="card'+card.id+'-checklists"></div><div class="labels" id="card'+card.id+'-labels"></div></div>');

    if(card.labels.length > 0) {
      $.each(card.labels, function(li, label){
        $('#card'+card.id+'-labels').append('<span class="label" style="background: '+label.color+';">'+label.name+'<span>');
      });
    }

    if(card.idChecklists.length > 0) {
      $.each(card.idChecklists, function(li, cl){
        Trello.get('/checklist/'+cl, loadChecklist, error);
      });
    }
  });
};

var loadChecklist = function(c){
  var citems = '';
  if(c.checkItems.length > 0) {
    $.each(c.checkItems, function(i, ci){
      citems += '<div class="check-item '+ci.state+'">'+((ci.state == 'complete')?'<i class="fa fa-check-circle"></i> ':'<i class="fa fa-circle-o"></i> ')+ci.name+'</div>';
    });
  }
  $('#card'+c.idCard+'-checklists').append('<div class="checklist" data-checklist="'+c.id+'"><h3><i class="fa fa-list-ul"></i> '+c.name+'</h3>'+citems+'</div>');
}

var error = function(errorMSG) {
  console.log(errorMSG);
};

$(function(){
  Trello.authorize({
    type: 'popup',
    name: 'App',
    scope: {
      read: 'true',
      write: 'true' },
    expiration: 'never',
    success: authenticationSuccess,
    error: authenticationFailure
  });
})
</script>
{/literal}
{/if}
