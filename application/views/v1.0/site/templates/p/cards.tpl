<div class="box-header with-border">
  <h3 class="box-title"><i class="fa fa-trello"></i> Fejlesztői log</h3>
</div>
<div class="box-body">
asd
</div>
{literal}
<script type="text/javascript">
var authenticationSuccess = function() { console.log('Successful authentication'); };
var authenticationFailure = function() { console.log('Failed authentication'); };
var success = function(successMsg) {
  asyncOutput(successMsg);
};
var error = function(errorMsg) {
  asyncOutput(errorMsg);
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

    //Trello.get('/member/me/boards', success, error);
  })
</script>
{/literal}
