<?php
namespace App\controller ;
use App\service\SheetService ;
use Google_Service_Sheets;
// require_once($root.'app/service/ServerService.php');
use Google_Client;
class SheetController {
   
	private $spreadsheetId = '1mDkKjw9b2ATBM76G3ZxCr8Y_gQ8nz0Whu4CIO3yvJmQ';
	private $_sheet ;

	public function __construct(){
		define('APPLICATION_NAME', 'Google Sheets API PHP Quickstart');
		// define('CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-php-quickstart.json');
		define('CREDENTIALS_PATH',  'sheets.googleapis.com-php-quickstart.json');

		define('CLIENT_SECRET_PATH', 'client_secret.json');
		// If modifying these scopes, delete your previously saved credentials
		// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
		define('SCOPES', implode(' ', array(
		  Google_Service_Sheets::SPREADSHEETS_READONLY)
		));

		$this->_sheet = SheetService::GetInstance();

	}

	public function setPublic ($request,$response, $args)
    {
        
        $parsedBody = $request->getParsedBody();
        $data = SheetService::setPublic($parsedBody);
        global $apiResult ;
        $apiResult = array_merge($apiResult,$data);
    }

    public function getData ($request,$response, $args)
    {
        $type = isset($args['public']) ? $args['public'] :  0 ;
        $public = ($type== "public" || $type== "1") ? 1 : 0 ;
        $parsedBody = $request->getParsedBody();

        if(isset($parsedBody['code'])){
            $data = SheetService::checkVersionSheetData($public,$parsedBody);
        }else{
        	$data = SheetService::getSheet($public,$parsedBody);
        }
        global $apiResult ;
        $apiResult = array_merge($apiResult,$data);
    }

    public function setData ($request,$response, $args)
    {
        // global $apiResult ;
        // var_dump($apiResult);
        // die;

       // Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new Google_Service_Sheets($client);
		// Prints the names and majors of students in a sample spreadsheet:
		// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
		$spreadsheetId = $this->spreadsheetId ;
		$response = $service->spreadsheets->get($spreadsheetId);
		$ranges = [];
		foreach ($response as $key => $res) {
		  if($this->checkRealData($res->properties->title)){
		      $ranges[] = $res->properties->title ;
		  }
		}
		// The A1 notation of the values to retrieve.
		$optParams['ranges'] = $ranges ;  // TODO: Update placeholder value.
		$response = $service->spreadsheets_values->batchGet($spreadsheetId,$optParams);
		foreach ($response['valueRanges'] as $index => $sheet) {
		     $excel[$index]['sheetName'] = $this->getSheetName($sheet->range) ;
		     $title = [];
		     $rows = 0 ;

		     if (!isset($sheet->values)){
		        continue;
		     }
		     foreach ($sheet->values as $key => $heads) {
		        if ($key==0){
		          foreach ($heads as $h => $head) {
		            if($this->checkRealData($head)){
		              $title[$h] = $head ;
		            }
		          }
		        }else{
		          foreach ($heads as $b => $body) {
		            if (isset($title[$b])){
		              $excel[$index]['sheetValue'][$rows][$title[$b]] = $body;
		            }
		          } 
		          $rows++;
		          // $excel[$index]['title'][$key] = $head;
		        }
		     }

		    if ($excel[$index]['sheetName']=="initial"){
                $this->_sheet->setInitialSheet($excel[$index]['sheetValue']);
            }

		}
        $data = $this->_sheet->setSheet($excel);
   		global $apiResult;
        $apiResult = array_merge($apiResult,$data);
    }

    private function getSheetName($text){
	    $list = explode("!", $text) ;
	    return (isset($list)) ? $list[0] : $text ;
	}
    private function getClient() {
	  $client = new Google_Client();
	  $client->setApplicationName(APPLICATION_NAME);
	  $client->setScopes(SCOPES);
	  $client->setAuthConfig(CLIENT_SECRET_PATH);
	  $client->setAccessType('offline');
	  // Load previously authorized credentials from a file.
	  $credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
	  if (file_exists($credentialsPath)) {
	    $accessToken = json_decode(file_get_contents($credentialsPath), true);
	  } else {
	    // Request authorization from the user.
	    $authUrl = $client->createAuthUrl();
	    printf("Open the following link in your browser:\n%s\n", $authUrl);
	    print 'Enter verification code: ';
	    $authCode = trim(fgets(STDIN));

	    // Exchange authorization code for an access token.
	    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

	    // Store the credentials to disk.
	    if(!file_exists(dirname($credentialsPath))) {
	      mkdir(dirname($credentialsPath), 0700, true);
	    }
	    file_put_contents($credentialsPath, json_encode($accessToken));
	    printf("Credentials saved to %s\n", $credentialsPath);
	  }
	  $client->setAccessToken($accessToken);

	  // Refresh the token if it's expired.
	  if ($client->isAccessTokenExpired()) {
	    // $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	    // file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
	    $refreshToken = $client->getRefreshToken();
	    $client->refreshToken($refreshToken);
	    $newAccessToken = $client->getAccessToken();
	    $newAccessToken['refresh_token'] = $refreshToken;
	    file_put_contents($credentialsPath, json_encode($newAccessToken));
	  }
	  return $client;
	}

		/**
		 * Expands the home directory alias '~' to the full path.
		 * @param string $path the path to expand.
		 * @return string the expanded path.
		 */
	private	function expandHomeDirectory($path) {
	  $homeDirectory = getenv('HOME');
	  if (empty($homeDirectory)) {
	    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
	  }
	  return str_replace('~', realpath($homeDirectory), $path);
	}


	private	function checkRealData($text){
	  if (substr($text,0,1)=="_"){
	    return false;
	  }
	  return true ;
	}

}