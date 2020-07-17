<?php  
/**
* Instagram Follow DM Like
* Last Update 28 Juni 2020
* Author : Faanteyki
*/
require "../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramHelper;
use Riedayme\InstagramKit\InstagramAuthAPI;
use Riedayme\InstagramKit\InstagramChecker;
use Riedayme\InstagramKit\InstagramResourceUser;
use Riedayme\InstagramKit\InstagramUserFollowers;

use Riedayme\InstagramKit\InstagramUserFollow;
use Riedayme\InstagramKit\InstagramUserPost;
use Riedayme\InstagramKit\InstagramDirectCreateAPI;
use Riedayme\InstagramKit\InstagramDirectBroadcastAPI;
use Riedayme\InstagramKit\InstagramPostLike;
use Riedayme\InstagramKit\InstagramPostComment;

date_default_timezone_set('Asia/Jakarta');

Class InputHelper
{
	public function GetInputUsername($data = false) {

		if ($data) return $data;

		$CheckPreviousData = InstagramFollowDMLike::CheckPreviousData();

		if ($CheckPreviousData) {
			echo "[?] Anda Memiliki Cookie yang tersimpan pilih angkanya dan gunakan kembali : ".PHP_EOL;
			foreach ($CheckPreviousData as $key => $cookie) {
				echo "[{$key}] ".$cookie['username'].PHP_EOL;

				$data_cookie[] = $key;
			}
			echo "[x] Masuk menggunakan akun baru".PHP_EOL;

			echo "[?] Pilihan Anda : ";

			$input = strtolower(trim(fgets(STDIN)));			

			if ($input != 'x') {

				if (strval($input) !== strval(intval($input))) {
					die("Salah memasukan format, pastikan hanya angka");
				}

				if (!in_array($input, $data_cookie)) {
					die("Pilihan tidak ditemukan");
				}

				return [$input];
			}
		}	

		echo "[?] Masukan Username : ";

		return trim(fgets(STDIN));
	}

	public function GetInputPassword($data = false) {

		if ($data) return $data;

		echo "[?] Masukan Password : ";

		return trim(fgets(STDIN));
	}

	public function GetInputRelog($data = false) {

		if ($data) return $data;

		echo "[?] Apakah anda ingin relogin akun ini (y/n) : ";

		$input = trim(fgets(STDIN));

		if (!in_array(strtolower($input),['y','n'])) 
		{
			die("Pilihan tidak diketahui");
		}

		return (!$input) ? die('Pilihan masih Kosong') : $input;
	}

	public function GetInputReConfig($data = false) {

		if ($data) return $data;

		echo "[?] Apakah anda mengatur ulang konfigurasi (y/n) : ";

		$input = trim(fgets(STDIN));

		if (!in_array(strtolower($input),['y','n'])) 
		{
			die("Pilihan tidak diketahui");
		}

		return (!$input) ? die('Pilihan masih Kosong') : $input;
	}

	public function GetInputChoiceVerify($data = false) {

		if ($data) return $data;

		echo "[?] Pilih cara vertifikasi : ";
		echo "[1] Kirim kode ke nomor handphone".PHP_EOL;
		echo "[2] Kirim kode ke email".PHP_EOL;

		$input = trim(fgets(STDIN));

		if (!in_array(strtolower($input),['1','2'])) 
		{
			die("Pilihan tidak diketahui");
		}

		return (!$input) ? die('Pilihan masih Kosong') : $input;
	}

	public function GetInputSecurityCode($data = false) {

		if ($data) return $data;

		echo "[?] Masukan Kode Vertifikasi : ";

		return trim(fgets(STDIN));
	}

	public function GetInputDirectMessage($data = false) {

		if ($data) return $data;

		echo "[?] Masukan pesan untuk direct message | : ".PHP_EOL;

		$input = trim(fgets(STDIN));

		return (!$input) ? die('Jawaban pertanyaan masih kosong') : $input;
	}

	public function GetInputTargets($data = false) {

		if ($data) return $data;

		echo "[?] Masukan Akun target pisah dengan tanda , : ".PHP_EOL;	

		$input = trim(fgets(STDIN));

		return (!$input) ? die('Target akun masih kosong') : $input;
	}	

	public function GetInputLimitPerday($data = false) {

		if ($data) return $data;

		echo "[?] Masukan Limit Follow per harinya (angka) : ";

		$input = trim(fgets(STDIN));

		if (strval($input) !== strval(intval($input))) 
		{
			die("Salah memasukan format, pastikan hanya angka");
		}

		return (!$input) ? die('Limit masih kosong') : $input;
	}			
}

Class InstagramFollowDMLike
{

	public $username;
	public $cookie;
	public $csrftoken;

	public $direct_message;
	public $targets;

	public $limit_per_day;

	public $current_loop_target = 0;
	public $current_loop_message = 0;		

	public $next_id = array();

	public $count_process = 0;

	public $delay_bot = 10;
	public $delay_bot_default = 15;
	public $delay_bot_count = 0;

	public $direct_checktime = false;

	public $count_delay;

	public function Auth($data) 
	{

		if (is_array($data['username'])) {

			echo "[•] Login Menggunakan Akun yang sudah ada".PHP_EOL;

			$results = self::ReadPreviousData($data['username'][0]);

			$choice_relog = InputHelper::GetInputRelog();

			if ($choice_relog == 'y') {

				$relog_data['username'] = $results['username'];
				$relog_data['password'] = $results['password'];
				$relog_data['direct_message'] = $results['direct_message'];
				$relog_data['targets'] = $results['targets'];
				$relog_data['limit_per_day'] = $results['limit_per_day'];

				return self::Auth($relog_data);
			}

			$choice_reconfig = InputHelper::GetInputReConfig();

			if ($choice_reconfig == 'y') {
				$reconfig_data['userid'] = $results['userid'];
				$reconfig_data['username'] = $results['username'];
				$reconfig_data['photo'] = $results['photo'];
				$reconfig_data['cookie'] = $results['cookie'];
				$reconfig_data['csrftoken'] = $results['csrftoken'];
				$reconfig_data['uuid'] = $results['uuid'];
				$reconfig_data['rank_token'] = $results['rank_token'];
				$reconfig_data['password'] = $results['password'];
				$reconfig_data['direct_message'] = InputHelper::GetInputDirectMessage();
				$reconfig_data['targets'] = InputHelper::GetInputTargets();
				$reconfig_data['limit_per_day'] = InputHelper::GetInputLimitPerday();

				self::SaveLogin($reconfig_data);

				$results = $reconfig_data;
			}

			echo "[•] Check Live Cookie".PHP_EOL;

			//$check_cookie = InstagramChecker::CheckLiveCookie($results['cookie']);
			//if (!$check_cookie['status']) die("[ERROR] Cookie sudah mati, silahkan relog".PHP_EOL);
		}else{	

			echo "[•] Login Menggunakan Username dan Password".PHP_EOL;

			$results = InstagramAuthAPI::Login($data['username'],$data['password']);			

			if ($results['status'] == 'checkpoint') {

				echo "[•] Akun anda terkena checkpoint".PHP_EOL;

				$choiceverify = InputHelper::GetInputChoiceVerify();
				$choiceverify = ($choiceverify == 1 ? 0 : 1);
				$sendCode = InstagramAuthAPI::CheckPointSend($results,$choiceverify);

				if (!$sendCode['status']) die($sendCode['response']);

				echo "[•] {$sendCode['response']}".PHP_EOL;

				$required['url'] = $results['url'];
				$required['cookie'] = $results['cookie'];
				$required['csrftoken'] = $results['csrftoken'];
				$required['guid'] = $results['guid'];

				$is_connected       = false;
				$is_connected_count = 1;

				do {

					$required['security_code'] = InputHelper::GetInputSecurityCode();
					$results = InstagramAuthAPI::CheckPointSolve($required);

					if ( $is_connected_count == 3 ) {
						echo "[•] 3x Kode Salah, ERROR".PHP_EOL;
						die($results['response']);
					}

					if (!isset($results['status']))
					{
						$is_connected = true;
					}else{
						echo "[•] Kode Salah, coba lagi".PHP_EOL;
					}

					$is_connected_count += 1;
				} while ( ! $is_connected );

			}

			echo "[•] Menyimpan Data Login".PHP_EOL;

			$results['response']['password'] = $data['password'];
			$results['response']['direct_message'] = $data['direct_message'];
			$results['response']['targets'] = $data['targets'];
			$results['response']['limit_per_day'] = $data['limit_per_day'];
			self::SaveLogin($results['response']);

			$results = $results['response'];
		}

		$this->cookie = $results['cookie'];
		$this->csrftoken = $results['csrftoken'];
		$this->username = $results['username'];

		$this->direct_message = $results['direct_message'];
		$this->targets = $results['targets'];
		$this->limit_per_day = $results['limit_per_day'];

		$this->active_data['username'] = $results['username']; 
		$this->active_data['password'] = $results['password'];		
		$this->active_data['direct_message'] = $results['direct_message'];		
		$this->active_data['targets'] = $results['targets'];	
		$this->active_data['limit_per_day'] = $results['limit_per_day'];											
	}

	public function SaveLogin($data){

		$filename = 'data/sc-fdl.json';

		if (file_exists($filename)) {
			$read = file_get_contents($filename);
			$read = json_decode($read,true);
			$dataexist = false;
			foreach ($read as $key => $logdata) {
				if ($logdata['userid'] == $data['userid']) {
					$inputdata[] = $data;
					$dataexist = true;
				}else{
					$inputdata[] = $logdata;
				}
			}

			if (!$dataexist) {
				$inputdata[] = $data;
			}
		}else{
			$inputdata[] = $data;
		}

		return file_put_contents($filename, json_encode($inputdata,JSON_PRETTY_PRINT));
	}

	public function CheckPreviousData()
	{

		$filename = 'data/sc-fdl.json';
		if (file_exists($filename)) {
			$read = file_get_contents($filename);
			$read = json_decode($read,TRUE);
			foreach ($read as $key => $logdata) {
				$inputdata[] = $logdata;
			}

			return $inputdata;
		}else{
			return false;
		}
	}

	public function ReadPreviousData($data)
	{

		$filename = 'data/sc-fdl.json';
		if (file_exists($filename)) {
			$read = file_get_contents($filename);
			$read = json_decode($read,TRUE);
			foreach ($read as $key => $logdata) {
				if ($key == $data) {
					$inputdata = $logdata;
					break;
				}
			}

			return $inputdata;
		}else{
			die("file tidak ditemukan");
		}
	}

	public function GetUserIdTarget()
	{

		$targetlist = explode(',', $this->targets);

		echo "[•] Membaca UserId Target".PHP_EOL;

		$this->targets = array();

		foreach ($targetlist as $username) {

			$username = trim($username);
			$getuserid = InstagramResourceUser::GetUserIdByWeb($username);						

			if ($getuserid) {
				echo "[•] User {$username} | id => [$getuserid]".PHP_EOL;

				$this->targets[] = [
					'userid' => $getuserid,
					'username' => $username
				];
			}else{
				echo "[•] Failed Read User {$username}".PHP_EOL;
			}

		}

	}

	public function GetShuffleTarget($index){

		$targetlist = $this->targets;

		/* reset index to 0 */
		if ($index >= count($targetlist)) {
			$index = 0;
			$this->current_loop_target = 1;
		}else{
			$this->current_loop_target = $this->current_loop_target + 1;
		}

		return $targetlist[$index];
	}

	public function GetFollowersTarget()
	{

		$getTarget = self::GetShuffleTarget($this->current_loop_target);

		$usernametarget = $getTarget['username'];
		$useridtarget = $getTarget['userid'];

		$type = false;
		$next_id = false;
		if (!empty($this->next_id[$useridtarget])) {
			$type = 'Lanjut-'.$this->next_id[$useridtarget."_count"].' ';
			$this->next_id[$useridtarget."_count"] = $this->next_id[$useridtarget."_count"]+1;
			$next_id = $this->next_id[$useridtarget];
		}else{
			$this->next_id[$useridtarget."_count"] = 1;
		}

		echo "[•] {$type}Mendapatkan List Followers {$usernametarget}".PHP_EOL;

		$results = false;
		$retry = 0;
		do {

			if ( $retry == 3 ) {
				echo "[•] Gagal Mendapatkan List Followers sebanyak 3x Relog Akun".PHP_EOL;

				self::Auth($this->active_data);
			}

			$results = self::GetFollowersTargetByWeb($useridtarget,$next_id);

			if (!$results)
			{
				echo "[•] Gagal Mendapatkan List Followers, Coba Lagi".PHP_EOL;
				sleep(5);
			}

			$retry += 1;
		} while ( !$results );

		echo "[•] Berhasil mendapatkan ".count($results)." User".PHP_EOL;

		/* reset value */
		$this->count_delay = 0;

		/* delay bot */
		self::DelayBot();

		return $results;
	}

	public function GetFollowersTargetByWeb($useridtarget,$next_id)
	{
		$readfollowers = new InstagramUserFollowers();
		$readfollowers->SetCookie($this->cookie);
		$userlist = $readfollowers->Process($useridtarget,$next_id);

		if (!$userlist['status']) return false;

		if ($userlist['cursor'] !== null) {
			$this->next_id[$useridtarget] = $userlist['cursor'];
		}else{
			$this->next_id[$useridtarget] = false;
		}

		$results = $readfollowers->Extract($userlist);

		return $results;
	}

	public function FollowUser($userdata)
	{
		$follow = new InstagramUserFollow();
		$follow->SetCookie($this->cookie);
		
		$results = $follow->Process($userdata['userid']);

		if ($results['status'] != false) {
			echo "[".date('d-m-Y H:i:s')."] Sukses Follow User {$userdata['username']}".PHP_EOL;
			echo "[•] Response : {$results['response']}".PHP_EOL;	

			self::SaveLog(strtolower($this->username),$userdata['userid']);

			/* delay bot */
			self::DelayBot();		

			return true;
		}else{
			echo "[".date('d-m-Y H:i:s')."] Gagal Follow User {$userdata['username']}".PHP_EOL;
			echo "[•] Response : {$results['response']}".PHP_EOL;			
			return false;
		}	
	}

	public function DirectUser($userdata)
	{

		if ($this->direct_checktime AND strtotime(date('Y-m-d H:i:s')) <= strtotime($this->direct_checktime)) {
			echo "[SKIP] Skip Direct Message sampai : {$this->direct_checktime}".PHP_EOL;
			return false;
		}

		echo "[•] Proses Kirim Pesan ke {$userdata['username']}".PHP_EOL;

		$userids[] = $userdata['userid'];
		$message = self::GetShuffleMessage($this->current_loop_message);

		$directcreate = new InstagramDirectCreateAPI();
		$directcreate->SetCookie($this->cookie);
		$get_thread_id = $directcreate->Process($userids);

		if (!$get_thread_id['status']) {
			echo "[•] Gagal membuat pesan dengan : {$userdata['username']}".PHP_EOL;					
			echo "[•] Response : {$get_thread_id['response']}".PHP_EOL;

			/* create log time for skip and check again */
			$this->direct_checktime = date('Y-m-d H:i:s',strtotime("+60 minutes"));

			return false;
		}else{

			$this->direct_checktime = false; /* reset value */

			/* delay bot */
			self::DelayBot();
		}

		$thread_ids = [$get_thread_id['response']['thread_id']];

		$directcreate = new InstagramDirectBroadcastAPI();
		$directcreate->SetCookie($this->cookie);
		$results = $directcreate->Process($message,$thread_ids);

		if ($results['status'] != false) {
			echo "[".date('d-m-Y H:i:s')."] Sukses Kirim Pesan ke {$userdata['username']} text {$message}".PHP_EOL;
			echo "[•] Response : {$results['response']}".PHP_EOL;			

			/* delay bot */
			self::DelayBot();

			return true;
		}else{
			echo "[".date('d-m-Y H:i:s')."] Gagal Kirim Pesan ke {$userdata['username']} text {$message}".PHP_EOL;
			echo "[•] Response : {$results['response']}".PHP_EOL;			
			return false;
		}	
	}

	public function LikePost($userdata,$send_comment = false)
	{

		echo "[•] Mendapatkan post dari {$userdata['username']}".PHP_EOL;

		$readpost = new InstagramUserPost();
		$readpost->SetCookie($this->cookie);

		$getpost = $readpost->Process($userdata['userid']);

		if (!$getpost['status']) {
			echo "[•] Gagal mendapatkan post dari {$userdata['username']}".PHP_EOL;					
			echo "[•] Response : {$getpost['response']}".PHP_EOL;			
			return false;
		}else{

			/* delay bot */
			self::DelayBot();
		}

		$postdata = $readpost->Extract($getpost);

		$likepost = new InstagramPostLike();
		$likepost->SetCookie($this->cookie);

		$current = 0;
		$limit = 3;
		$delay = 14;
		foreach ($postdata as $post) {

			$results = $likepost->Process($post['id']);

			if ($results['status'] != false) {
				echo "[".date('d-m-Y H:i:s')."] Sukses Like post {$post['url']}".PHP_EOL;
				echo "[•] Response : {$results['response']}".PHP_EOL;	

				echo "[•] Delay {$delay}".PHP_EOL;
				sleep($delay);
				$this->count_delay += $delay;
				$delay = $delay+5;

				$current++;
				
			}else{
				echo "[".date('d-m-Y H:i:s')."] Gagal Like post {$post['url']}".PHP_EOL;
				echo "[•] Response : {$results['response']}".PHP_EOL;
			}

			if ($current == $limit) {

				if ($send_comment) {

					$message = self::GetShuffleMessage($this->current_loop_message);

					$likecomment = new InstagramPostComment();
					$likecomment->SetCookie($this->cookie);

					$results = $likecomment->Process($post['id'],$message);

					if ($results['status'] != false) {
						echo "[".date('d-m-Y H:i:s')."] Sukses Kirim Komentar ke {$post['url']}".PHP_EOL;
						echo "[•] Response : {$results['response']}".PHP_EOL;	

						echo "[•] Delay {$delay}".PHP_EOL;
						sleep($delay);
						$delay = $delay+5;

						$current++;

					}else{
						echo "[".date('d-m-Y H:i:s')."] Gagal Kirim Komentar ke {$post['url']}".PHP_EOL;
						echo "[•] Response : {$results['response']}".PHP_EOL;
					}
				}

				break;
			}

		}

	}	

	public function GetShuffleMessage($index)
	{

		$message = explode('|', $this->direct_message);

		/* reset index to 0 */
		if ($index >= count($message)) {
			$index = 0;
			$this->current_loop_message = 1;
		}else{
			$this->current_loop_message = $this->current_loop_message + 1;
		}

		return trim($message[$index]);		
	}

	public function SyncUser($userid)
	{

		$ReadLog = self::ReadLog(strtolower($this->username));

		if (is_array($ReadLog) AND in_array($userid, $ReadLog)) 
		{
			return true;
		}

		return false;
	}

	public function ReadLog($identity)
	{		

		$logfilename = "log/user-data-fdl-{$identity}";
		$log_id = array();
		if (file_exists($logfilename)) 
		{
			$log_id = file_get_contents($logfilename);
			$log_id  = explode(PHP_EOL, $log_id);
		}

		return $log_id;
	}

	public function SaveLog($identity,$datastory)
	{
		return file_put_contents("log/user-data-fdl-{$identity}", $datastory.PHP_EOL, FILE_APPEND);
	}	

	public function DelayBot()
	{

		/* reset sleep value to default */
		if ($this->delay_bot_count >= 5) {
			$this->delay_bot = $this->delay_bot_default;
			$this->delay_bot_count = 0;
		}	

		echo "[•] Delay {$this->delay_bot}".PHP_EOL;
		sleep($this->delay_bot);
		$this->count_delay += $this->delay_bot;
		$this->delay_bot = $this->delay_bot+5;
		$this->delay_bot_count++;
	}
}

Class Worker
{
	public function Run()
	{

		echo "Instagram Follow DM Like".PHP_EOL;

		$account['username'] = InputHelper::GetInputUsername();

		if (!is_array($account['username'])) {
			$account['password'] = InputHelper::GetInputPassword();

			$account['direct_message'] = InputHelper::GetInputDirectMessage();
			$account['targets'] = InputHelper::GetInputTargets();
			$account['limit_per_day'] = InputHelper::GetInputLimitPerday();
		}

		/* Call Class */
		$Working = new InstagramFollowDMLike();
		$Working->Auth($account);
		$Working->GetUserIdTarget();

		while (true) {

			$userlist = $Working->GetFollowersTarget();				

			foreach ($userlist as $userdata) {

				if($userdata['is_private']) continue;
				if($userdata['is_verified']) continue;
				if(!$userdata['latest_reel_media']) continue;

				/* sync user data with log file */
				if ($Working->SyncUser($userdata['userid'])) continue;

				$process_follow = $Working->FollowUser($userdata);
				if (!$process_follow) {
					$startdate = date('Y-m-d H:i:s'); 
					$enddate = date('Y-m-d')."23:59:59"; 
					$remain_today = strtotime($enddate)-strtotime($startdate); 
					echo "[•] Aksi Follow diblokir menunggu hari berikutnya, delay selama {$remain_today} detik".PHP_EOL;
					sleep($remain_today);
				}

				$process_dm = $Working->DirectUser($userdata);
				$send_comment = false;
				if (!$process_dm) {
					$send_comment = true;
				}

				$process_like_post = $Working->LikePost($userdata,$send_comment);

				$Working->count_process = $Working->count_process + 1;
				echo "[•] Total Proses berjalan : {$Working->count_process}".PHP_EOL;

				/* limit delay calculate 100 process/day */
				$limit_delay = InstagramHelper::GetSleepTimeByLimit($Working->limit_per_day) - $Working->count_delay;
				echo "[•] Delay Selama : " . ceil($limit_delay / 60) ." menit".PHP_EOL;
				sleep($limit_delay);
			}

		}		

	}
}

Worker::Run();
// use at you own risk