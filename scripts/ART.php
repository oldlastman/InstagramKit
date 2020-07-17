<?php  
/**
* Instagram Repost Target
* Last Update 13 Juli 2020
* Author : Faanteyki
*/
require "../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramHelper;
use Riedayme\InstagramKit\InstagramAuthAPI;
use Riedayme\InstagramKit\InstagramChecker;
use Riedayme\InstagramKit\InstagramResourceUser;

use Riedayme\InstagramKit\InstagramUserPost;
use Riedayme\InstagramKit\InstagramPostUploadAPI;

date_default_timezone_set('Asia/Jakarta');

Class InputHelper
{
	public function GetInputUsername($data = false) {

		if ($data) return $data;

		$CheckPreviousData = InstagramRepostTarget::CheckPreviousData();

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

		/** 
		 * hidden password
		 * https://gist.github.com/scribu/5877523
		 */	
		
		echo "\033[30;40m";  
		$input = trim(fgets(STDIN));
		echo "\033[0m";     

		return $input;
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

		echo "[?] Pilih cara vertifikasi : ".PHP_EOL;
		echo "[1] Kirim kode ke nomor handphone".PHP_EOL;
		echo "[2] Kirim kode ke email".PHP_EOL;
		echo "[?] Pilihan anda (1/2) : ";

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

	public function GetInputTargets($data = false) {

		if ($data) return $data;

		echo "[?] Masukan Akun target pisah dengan tanda , : ".PHP_EOL;	

		$input = trim(fgets(STDIN));

		return (!$input) ? die('Target akun masih kosong') : $input;
	}	

	public function GetInputLimitPerday($data = false) {

		if ($data) return $data;

		echo "[?] Masukan Limit Repost per harinya (angka) : ";

		$input = trim(fgets(STDIN));

		if (strval($input) !== strval(intval($input))) 
		{
			die("Salah memasukan format, pastikan hanya angka");
		}

		return (!$input) ? die('Limit masih kosong') : $input;
	}			
}

Class InstagramRepostTarget
{

	public $username;
	public $cookie;
	public $csrftoken;

	public $targets;

	public $limit_per_day;

	public $current_loop_target = 0;

	public $next_id = array();

	public $count_process = 0;

	public $delay_bot = 10;
	public $delay_bot_default = 15;
	public $delay_bot_count = 0;

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
				$reconfig_data['targets'] = InputHelper::GetInputTargets();
				$reconfig_data['limit_per_day'] = InputHelper::GetInputLimitPerday();

				self::SaveLogin($reconfig_data);

				$results = $reconfig_data;
			}

			echo "[•] Check Live Cookie".PHP_EOL;

			$check_cookie = InstagramChecker::CheckLiveCookie($results['cookie']);
			if (!$check_cookie['status']) die("[!!!] Cookie sudah mati, silahkan relog".PHP_EOL);
		}else{	

			echo "[•] Login Menggunakan Username dan Password".PHP_EOL;

			$results = InstagramAuthAPI::Login($data['username'],$data['password']);			

			if ($results['status'] == 'checkpoint') {

				echo "[!!!] Akun anda terkena checkpoint".PHP_EOL;

				$results = $results['response'];

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

					if ($results['status'])
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
			$results['response']['targets'] = $data['targets'];
			$results['response']['limit_per_day'] = $data['limit_per_day'];
			self::SaveLogin($results['response']);

			$results = $results['response'];
		}

		$this->cookie = $results['cookie'];
		$this->csrftoken = $results['csrftoken'];
		$this->username = $results['username'];

		$this->targets = $results['targets'];
		$this->limit_per_day = $results['limit_per_day'];

		$this->active_data['username'] = $results['username']; 
		$this->active_data['password'] = $results['password'];		
		$this->active_data['targets'] = $results['targets'];	
		$this->active_data['limit_per_day'] = $results['limit_per_day'];	
	}

	public function SaveLogin($data){

		$filename = 'data/sc-art.json';

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

		$filename = 'data/sc-art.json';
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

		$filename = 'data/sc-art.json';
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

	public function GetUserPostTarget()
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

		echo "[•] {$type}Mendapatkan Post {$usernametarget}".PHP_EOL;

		$results = false;
		$retry = 0;
		do {

			if ( $retry == 3 ) {
				echo "[•] Gagal Mendapatkan Post sebanyak 3x Relog Akun".PHP_EOL;

				self::Auth($this->active_data);
			}

			$results = self::GetUserPostByWeb($useridtarget,$next_id);

			if (!$results)
			{
				echo "[•] Gagal Mendapatkan Post, Coba Lagi".PHP_EOL;
				sleep(5);
			}

			$retry += 1;
		} while ( !$results );

		echo "[•] Berhasil mendapatkan ".count($results)." Post".PHP_EOL;

		/* reset value */
		$this->count_delay = 0;

		/* delay bot */
		self::DelayBot();

		return $results;
	}

	public function GetUserPostByWeb($useridtarget,$next_id)
	{
		$readpost = new InstagramUserPost();
		$readpost->SetCookie($this->cookie);
		$userlist = $readpost->Process($useridtarget,$next_id);

		if (!$userlist['status']) return false;

		if ($userlist['cursor'] !== null) {
			$this->next_id[$useridtarget] = $userlist['cursor'];
		}else{
			$this->next_id[$useridtarget] = false;
		}

		$results = $readpost->Extract($userlist);

		return $results;
	}

	public function DownloadMedia($post)
	{

		echo "[•] Proses Download Media {$post['id']}".PHP_EOL;	

		if ($process_media = InstagramHelper::DownloadByURL($post['media'],'./temp/')) {

			echo "[•] Sukses Download Media {$post['id']}".PHP_EOL;

			$process_thumbnail = false;

			if ($post['type'] == 'video') {

				echo "[•] Proses Download Thumbnail {$post['id']}".PHP_EOL;	

				if ($process_thumbnail = InstagramHelper::DownloadByURL($post['thumbnail'],'./temp/')) {

					echo "[•] Sukses Download Thumbnail {$post['id']}".PHP_EOL;

					return [
						'filename' => $process_media,
						'thumbnail' => $process_thumbnail
					];

				}else{

					echo "[•] Gagal Download Media {$post['id']}".PHP_EOL;

					unlink('./temp/'.$process_media);
					unlink('./temp/'.$process_thumbnail);

					return false;
				}
			}

			return [
				'filename' => $process_media
			];

		}else{

			echo "[•] Gagal Download Media {$post['id']}".PHP_EOL;

			unlink('./temp/'.$process_media);

			return false;
		}

	}

	public function UploadMedia($post)
	{

		echo "[•] Proses Upload Media {$post['id']}".PHP_EOL;	

		$postupload = new InstagramPostUploadAPI();
		$postupload->SetCookie($this->cookie);

		if ($post['type'] == 'image') {

			$upload = $postupload->ProcessUploadPhoto('./temp/'.$post['filename']);

			if (!$upload['status']) {

				echo "[•] Gagal Upload Media {$post['id']}".PHP_EOL;
				echo "[•] Response : {$upload['response']}".PHP_EOL;	

				unlink('./temp/'.$post['filename']);

				/* delay bot */
				self::DelayBot();	

				return false;
			}

			$upload_id = $upload['response']['upload_id'];

			echo "[•] Proses Konfigurasi Media {$post['id']}".PHP_EOL;	

			$configure = $postupload->ConfigurePhoto($upload_id,$post['caption']);

			if (!$configure['status']) {

				echo "[•] Gagal Konfigurasi Photo {$post['id']}".PHP_EOL;
				echo "[•] Response : {$configure['response']}".PHP_EOL;	

				unlink('./temp/'.$post['filename']);

				/* delay bot */
				self::DelayBot();	

				return false;
			}

			unlink('./temp/'.$post['filename']);

		}elseif ($post['type'] == 'video') {

			$upload = $postupload->ProcessUploadVideo('./temp/'.$post['filename']);

			if (!$upload['status']) {

				echo "[•] Gagal Upload Media {$post['id']}".PHP_EOL;
				echo "[•] Response : {$upload['response']}".PHP_EOL;	

				unlink('./temp/'.$post['filename']);
				unlink('./temp/'.$post['thumbnail']);

				/* delay bot */
				self::DelayBot();	

				return false;
			}

			$upload_id = $upload['response']['upload_id'];

			sleep(5);

			echo "[•] Proses Upload Thumbnail {$post['id']}".PHP_EOL;	

			$upload = $postupload->ProcessUploadPhoto('./temp/'.$post['thumbnail'],$upload_id);

			if (!$upload['status']) {

				echo "[•] Gagal Upload Thumbnail {$post['id']}".PHP_EOL;
				echo "[•] Response : {$upload['response']}".PHP_EOL;	

				unlink('./temp/'.$post['filename']);
				unlink('./temp/'.$post['thumbnail']);

				/* delay bot */
				self::DelayBot();	

				return false;
			}

			echo "[•] Proses Konfigurasi Media {$post['id']}".PHP_EOL;	

			$configure = $postupload->ConfigureVideo($upload_id,$post['caption']);

			if (!$configure['status']) {

				echo "[•] Gagal Konfigurasi Video {$post['id']}".PHP_EOL;
				echo "[•] Response : {$configure['response']}".PHP_EOL;	

				unlink('./temp/'.$post['filename']);
				unlink('./temp/'.$post['thumbnail']);

				/* delay bot */
				self::DelayBot();	

				return false;
			}

			unlink('./temp/'.$post['filename']);
			unlink('./temp/'.$post['thumbnail']);
		}

		echo "[".date('d-m-Y H:i:s')."] Sukses Upload Media {$post['id']}".PHP_EOL;
		echo "[•] Response : ".json_encode($configure['response']).PHP_EOL;	

		self::SaveLog(strtolower($this->username),$post['id']);

		/* delay bot */
		self::DelayBot();		

		return true;
	}

	public function SyncPost($postid)
	{

		$ReadLog = self::ReadLog(strtolower($this->username));

		if (is_array($ReadLog) AND in_array($postid, $ReadLog)) 
		{
			return true;
		}

		return false;
	}

	public function ReadLog($identity)
	{		

		$logfilename = "log/user-data-art-{$identity}";
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
		return file_put_contents("log/user-data-art-{$identity}", $datastory.PHP_EOL, FILE_APPEND);
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

		echo "Instagram Repost Target".PHP_EOL;

		$account['username'] = InputHelper::GetInputUsername();

		if (!is_array($account['username'])) {
			$account['password'] = InputHelper::GetInputPassword();

			$account['targets'] = InputHelper::GetInputTargets();
			$account['limit_per_day'] = InputHelper::GetInputLimitPerday();
		}

		/* Call Class */
		$Working = new InstagramRepostTarget();
		$Working->Auth($account);
		$Working->GetUserIdTarget();

		while (true) {

			$postlist = $Working->GetUserPostTarget();		

			foreach ($postlist as $postdata) {

				/* sync post data with log file */
				if ($Working->SyncPost($postdata['id'])) continue;

				$process_download = $Working->DownloadMedia($postdata);
				if (!$process_download) {					
					continue;
				}else{
					$postdata = array_merge($postdata,$process_download);
				}

				$process_upload = $Working->UploadMedia($postdata);
				if (!$process_upload) {					
					continue;
				}

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