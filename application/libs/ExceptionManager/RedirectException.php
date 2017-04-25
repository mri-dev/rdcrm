<?
namespace ExceptionManager;

class RedirectException extends \Exception { 
	private $url = false;
	private $session_path = false;
	private $post = null;
	private $ecode = 0;
	private $emsg = '';

	public function __construct( $message, $code = 0, $url = false, $session_path = false, \Exception $previous = null) {
	    parent::__construct($message, $code, $previous);

	    $this->ecode 	= $code;
	    $this->emsg 	= $message;
	    $this->post 	= (!empty($_POST)) ? json_encode( $_POST, JSON_UNESCAPED_UNICODE ) : null;
	    $this->session_path = $session_path;

	    if( $url ) {
	    	$this->url = $url;
	    }
	}

	public function redirect( $anchor = false )
	{
		
		if( $this->url ) {
			ob_start();
			
			setcookie( "_form_post", null, time() - 60 );
			unset($_SESSION['_form_post'][trim($this->session_path,"/")]);

			if( $this->session_path ) {
				//setcookie( "_form_post", $this->post, time() + 60*5, $this->session_path );
				$_SESSION['_form_post'][trim($this->session_path,"/")] = $this->post;
			}

			$xurl = explode('?',$this->url);
			$ret_get = '';
			
			if( count( $xurl ) > 1 ) {
				$ret_get = end($xurl).'&';
				$this->url = $xurl[0];
			}	


			$this->url = rtrim( $this->url, '/' ) . '/?'.$ret_get.'response='.base64_encode( $this->ecode.'::error::'.$this->emsg );

			if( $anchor ) {
				$this->url = $this->url . "#".$anchor;
			}

			header( 'Location: '.$this->url );

			ob_end_flush();
		}
		
	}
}
?>