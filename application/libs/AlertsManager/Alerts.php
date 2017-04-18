<?
namespace AlertsManager;

use MailManager\Mails;

class Alerts 
{
	const DB_TABLE_AD 		= 'log_alerts_hirdetmeny_expire';
	const DB_TABLE_AD_RENEW = 'log_alerts_hirdetmeny_renew';

	private $db;
	public 	$arg;
	public 	$settings;
	public 	$smarty;
	
	private $offline = true;

	public function __construct( $arg = array() )
	{
		$this->db 		= $arg[db];
		$this->arg 		= $arg;
		$this->settings = $arg[settings];
		$this->smarty 	= $arg[smarty];
	}

	/**
	 * Lejáró hirdetések tulajainak kiértesítése
	 * http://jobabc.hu/cron/ad/alertAdsToRenew
	 * */
	public function adExpiredAdAuthors()
	{
		// Hirdetések összeszedése
		$qry = "
		SELECT 				u.nev, u.email, 
							adv.cim, adv.id as ad_id, adv.lejarat_ido
		FROM				".\PortalManager\Ad::TABLE." as adv
		LEFT OUTER JOIN 	".\PortalManager\Users::TABLE_NAME." as u ON u.ID = adv.fiok_id
		WHERE 				1=1 and adv.active = 1 and now() < adv.lejarat_ido and now() >= adv.feladas_ido and DATEDIFF(adv.lejarat_ido,now()) <= 3
		;";

		$data = $this->db->query($qry)->fetchAll(\PDO::FETCH_ASSOC);

		//echo $qry;

		foreach ($data as $d) 
		{			
			$lejarat 		= strtotime($d['lejarat_ido']);
			$now 			= strtotime(NOW);
			$af 			= ceil(($lejarat - $now) / (3600 * 24));
			$day3_alerted 	= ($af >= 3) ? false : true;
			$day1_alerted 	= ($af <= 1) ? false : true;
			$to 			= array();
			$arg 			= array();

			$to[] 	= $d['email'];
			$arg 	= $d;
			
			// Értesítések ellenőrzése
			$alert_times = $this->db->query("SELECT GROUP_CONCAT(idopont) FROM ".self::DB_TABLE_AD." WHERE hird_id = ".$d['ad_id'])->fetchColumn();

			$xtimes = explode(",",$alert_times);

			foreach ($xtimes as $time) 
			{
				$etime = ceil( ($lejarat - strtotime($time)) / (3600 * 24) );
				
				echo $af.'/'.$etime . ' | ';
				echo $d['email'] . ' | '.$d['cim'].' ('.$d['ad_id'].') @ '. $d['lejarat_ido'];

				if( $etime == 1 && !$day1_alerted ) $day1_alerted = true;
				if( $etime == 3 && !$day3_alerted ) $day3_alerted = true;
			}

			if( $af <= 1 && !$day3_alerted ) {
				$day3_alerted = true;
			}
		
			// Értesítés
			if( !$day3_alerted || !$day1_alerted ) 
			{
				if( true ) 
				{
					echo '++++++++';

					$arg[expire_day] 	= ceil($af);
					$arg[settings] 		= $this->settings;

					/* */
					if( !$this->offline ) 
					{
						$mail = new Mails( $this, 'alerts_ad_expire', $to, $arg);
						$mail->setSubject('Hirdetése '.$arg[expire_day].' nap múlva lejár: '.$d['cim']);
						$mail->send();

						// Log 					
						$this->db->insert(
							self::DB_TABLE_AD,
							array(
								'hird_id' => $d['ad_id'],
								'idopont' => NOW
							)
						);
					}
					/* */
				}
			}
			
			echo '<br><br>';
		
		}
	}

	/**
	* Lejárt hirdetések újraindítása emlékeztető
	* http://jobabc.hu/cron/ad/alertAdsToRenewExpired
	*/
	public function adExpiredAdAuthorsAfterExpired()
	{
		// Hirdetések összeszedése
		$qry = "
		SELECT 				u.nev, u.email, 
							adv.cim, adv.id as ad_id, adv.lejarat_ido
		FROM				".\PortalManager\Ad::TABLE." as adv
		LEFT OUTER JOIN 	".\PortalManager\Users::TABLE_NAME." as u ON u.ID = adv.fiok_id
		WHERE 				1=1 and adv.active = 1 and now() > adv.lejarat_ido and DATEDIFF(now(),adv.lejarat_ido) <= 7
		;";

		$data = $this->db->query($qry)->fetchAll(\PDO::FETCH_ASSOC);

		$stack 			= array();
		$day1_alerted 	= true;
		$day3_alerted 	= true;
		$day7_alerted 	= true;

		foreach ($data as $d) 
		{		
			$lejarat 		= strtotime($d['lejarat_ido']);
			$now 			= strtotime(NOW);
			$af 			= floor(($now - $lejarat) / (3600 * 24));
			$day1_alerted 	= ($af >= 1) ? false : true;
			$day3_alerted 	= ($af >= 3) ? false : true;
			$day7_alerted 	= ($af >= 7) ? false : true;

			$to 			= array();
			$arg 			= array();

			$to[] 	= $d['email'];
			$arg 	= $d;

			// Értesítések ellenőrzése
			$alert_times = $this->db->query("SELECT GROUP_CONCAT(idopont) FROM ".self::DB_TABLE_AD_RENEW." WHERE hird_id = ".$d['ad_id'])->fetchColumn();

			$xtimes = explode(",",$alert_times);

			foreach ($xtimes as $time) 
			{
				$etime = floor( (strtotime($time)-$lejarat) / (3600 * 24) );

				if( $etime >= 1 && !$day1_alerted ) $day1_alerted = true;
				if( $etime >= 3 && !$day3_alerted ) $day3_alerted = true;
				if( $etime >= 7 && !$day7_alerted ) $day7_alerted = true;
			}

			if( !$day7_alerted ) {
				$day3_alerted = true;
				$day1_alerted = true;
			}

			if( !$day3_alerted ) {
				$day1_alerted = true;
			}

			/* */
			echo $af.' / '.$etime.' ## ';
			echo '['.$d['ad_id'].']'.$d['cim'] . ' | '.$d['email'].': '.$d['lejarat_ido'] .'(';
			echo ($day1_alerted) ? ' 1 ': ' 0 ';
			echo ($day3_alerted) ? ' 1 ': ' 0 ';
			echo ($day7_alerted) ? ' 1 ': ' 0 ';					
			echo ')';
			/* */		

			if( !$day7_alerted || !$day3_alerted || !$day1_alerted ) 
			{
				if( true ) {
					$d['expired_day'] 		= $af;

					if( !$day1_alerted ) $d[eday] = 1;
					if( !$day3_alerted ) $d[eday] = 3;
					if( !$day7_alerted ) $d[eday] = 7;
					
					$stack[$d['email']][] 	= $d;
				}
			}

			echo '<br>';echo '<br>';
		}

		foreach ( $stack as $email => $alert ) {
			$arg[settings] 		= $this->settings;
			$arg[ads] 			= $alert;
			$arg[expired_ads] 	= count($alert);

			if( count($alert) == 0 ) continue;

			/* * /
			if( !$this->offline ) 
			{
				$mail = new Mails( $this, 'alerts_ad_expired_renew', $to, $arg);
				$mail->setSubject('Önnek '.count($alert). ' db lejárt hirdetése van');
				$mail->send();
				
				// Log 
				foreach( $alert as $a ) 
				{
					
					$this->db->insert(
						self::DB_TABLE_AD_RENEW,
						array(
							'hird_id' => $a['ad_id'],
							'idopont' => NOW
						)
					);
				
				}
			}			
			/* */			
			
		}
	}

	public function __destruct()
	{
		$this->db 		= null;
		$this->arg 		= null;
		$this->smarty 	= null;
		$this->settings = null;
	}
}

?>