<?
class Start
{
	function __construct(){
		
		$url = Helper::GET();
		
		$openControl = null;
		
		if (count($url) == 0)
		{
			$openControl = 'home';
		}
		else if (count($url) != 0)
		{
			$openControl = $url[0];
		}

		/**
		 * PHPIDS
		 *  Detect hacking
		 * */
		if (substr( $_GET['tag'], 0, 2 ) !== "cp" ) 
		{
			/*
			$filters = new \Expose\FilterCollection();
			$filters->load();

			$logger = new \Expose\Log\Detector();

			$manager = new \Expose\Manager($filters, $logger);
			$data = array(
				'GET' => $_GET,
				'POST' => $_POST
			);

			$manager->setException('POST.details');
			$manager->setException('POST.href');
			$manager->setException('POST.data.description');
			

			$manager->run($data);

			$reports = $manager->getReports();
			if ($reports)
			{
				include VIEW."site/templates/hack_report.tpl";
				die();	
			}
			*/
		}
		

		if(!file_exists(CONTROL . $openControl . '.php')){
			$openControl = "PageNotFound";
		}
		
		require CONTROL . $openControl . '.php';

		$control = new $openControl();
		if(count($url) > 1){
			if(method_exists($control,$url[1])){
				$control->fnTemp = $url[1];
				$control->$url[1]();
			}
		}			
	}
	
}

?>