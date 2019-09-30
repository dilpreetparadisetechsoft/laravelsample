<?php

use Illuminate\Http\Request;
use App\Http\Requests;
//use Validator, DateTime, DB, Hash, File, Config, Helpers, Auth, Mail;
//use Session, Redirect;
//use JWTFactory;
//use JWTAuth;
use App\Countries;
use App\Cities;
use App\States;
use App\OrganizationDocuments;
use App\BankDetails;
use App\ClientDetails;
use App\User;
use App\ServiceCategories;
use App\PhoneOtpVerification;

function ScriptCssPath($path = '/')
{
    return asset($path);
}
function CleanHtml($html = null)
{
    return preg_replace(
        array(
            '/ {2,}/',
            '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
        ),
        array(
            ' ',
            ''
        ),
        $html
    );
}

function SendSMS($mobileNumber, $message)
{
	return;	    
}

function maybe_decode( $original ) {
	if ( is_serialized( $original ) ) 
	    return @unserialize( $original );
	return $original;
}
function is_serialized( $data, $strict = true ) {
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
	if ( 'N;' == $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	} 
	else 
	{
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		if ( false === $semicolon && false === $brace )
			return false;
		if ( false !== $semicolon && $semicolon < 3 )
			return false;
		if ( false !== $brace && $brace < 4 )
			return false;
	}
	$token = $data[0];
	switch ( $token ) {
		case 's' :
			if ( $strict ) {
				if ( '"' !== substr( $data, -2, 1 ) ) {
				    return false;
				}
			} 
			elseif ( false === strpos( $data, '"' ) ) {
				return false;
			}
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	}
	return false;
}
 
function maybe_encode( $data ) {
	if ( is_array( $data ) || is_object( $data ) )
	     return serialize( $data );
	if ( is_serialized( $data, false ) )
		return serialize( $data );	 
    return $data;
}

function fileuploadmultiple($request)
{
    $files = $request->file('attachments');
    $uploaded_file = [];
    foreach($files as $file) {
        $destinationPath = 'public/images/uploads/'.date('Y').'/'.date('M');
        $filename = str_replace(array(' ','-','`',','),'_',time().'_'.$file->getClientOriginalName());
        $upload_success = $file->move($destinationPath, $filename);
        $uploaded_file[] = 'public/images/uploads/'.date('Y').'/'.date('M').'/'.$filename;        
    }
    return $uploaded_file;
}
function fileupload($request){
    $file = $request->file('image');
    $destinationPath = 'public/images/uploads/'.date('Y').'/'.date('M');
    $filename = time().'_'.$file->getClientOriginalName();
    $upload_success = $file->move($destinationPath, $filename);
    $uploaded_file = 'public/images/uploads/'.date('Y').'/'.date('M').'/'.$filename;            
    return $uploaded_file;
}
function fileuploadExtra($request, $key){
    $file = $request->file($key);
    $destinationPath = 'public/images/uploads/'.date('Y').'/'.date('M');
    $filename = time().'_'.$file->getClientOriginalName();
    $upload_success = $file->move($destinationPath, $filename);
    $uploaded_file = 'public/images/uploads/'.date('Y').'/'.date('M').'/'.$filename;            
    return $uploaded_file;
}
function fileuploadArray($file){
    $destinationPath = 'public/images/uploads/'.date('Y').'/'.date('M');
    $filename = time().'_'.$file->getClientOriginalName();
    $upload_success = $file->move($destinationPath, $filename);
    $uploaded_file = 'public/images/uploads/'.date('Y').'/'.date('M').'/'.$filename;            
    return $uploaded_file;
}
function randomPassword() {
    return mt_rand(100000, 999999);
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function getApiCurrentUser()
{
	if (empty(Request()->header('Authorization'))) {
		return new \App\User();
	}
	return JWTAuth::parseToken()->authenticate();
}
function getCurrentUser()
{
    if (empty(session('token'))) {
        return new \App\User();
    }
    return JWTAuth::toUser(session('token'));
}
function getCurrentUserByKey($key)
{
	$user = getCurrentUser();
	if (!empty($key)) {
		return isset($user->$key)?$user->$key:0;
	}
	return $user;
}
function getUser($user_id)
{
    return DB::table('users')->where('user_id', $user_id)->select('*')->get()->first();
}
function createUuid($name = 'vendorP')
{
	return Uuid::generate(5, $name, Uuid::NS_DNS);
}
function getCountry($country_name = null)
{
    if (!empty($country_name)) {
        return Countries::where('name',$country_name)->get()->pluck('name')->first();
    }
    return Countries::get();
}
function getState($country_name = null, $state_id = null)
{

    if (!empty($state_id)) {
        return States::where('id', $state_id)->get()->pluck('name')->first();
    }
	return States::where('country_id', Countries::where('name',$country_name)->get()->pluck('id')->first())->get();
}
function getStateCity($state_name = null, $city_id = null)
{
    if (!empty($city_id)) {
        return Cities::where('id', $city_id)->get()->pluck('name')->first();
    }
	return Cities::where('state_id', States::where('name', $state_name)->get()->pluck('id')->first())->get();
}
function getPercantageAmount($amount, $percent)
{
    return $amount/100*$percent;
}
function getDuration($date)
{
  $time = '';
  $t1 = \Carbon\Carbon::parse($date);
  $t2 = \Carbon\Carbon::parse();
  $diff = $t1->diff($t2);
  if ($diff->format('%y')!=0) {
    $time .= $diff->format('%y')." Year ";
  }
  if ($diff->format('%m')!=0) {
    $time .= $diff->format('%m')." Month ";
  }
  if ($diff->format('%d') && $diff->format('%m')==0) {
    $time .= $diff->format('%d')." Days ";
  }
  if ($diff->format('%h')!=0 && $diff->format('%m')==0) {
    $time .= $diff->format('%h')." Hours ";
  }
  if ($diff->format('%i')!=0 && $diff->format('%d')==0) {
    $time .= $diff->format('%i')." Minutes ";
  }
  if ($diff->format('%s')!=0 && $diff->format('%h')==0) {
    $time .= $diff->format('%s')." Seconds ";
  }
  return $time;
}
function weekOfMonth($currentMonth)
{
    $stdate = $currentMonth.'-01';
    $enddate = $currentMonth.'-31'; //get end date of month
    $begin = new \DateTime('first day of ' . $stdate);
    $end = new \DateTime('last day of ' . $enddate);
    $interval = new \DateInterval('P1W');
    $daterange = new \DatePeriod($begin, $interval, $end);

    $dates = array();
    foreach($daterange as $key => $date) {
        $check = ($date->format('W') != $end->modify('last day of this month')->format('W')) ? '+6 days' : 'last day of this week';
        $dates[$key+1] = array(
            'start' => $date->format('Y-m-d'),
            'end' => ($date->modify($check)->format('Y-m-d')),
        );    
        if ($dates[$key+1]['end']>date('Y-m-d', strtotime($enddate))) {
              $dates[$key+1]['end'] = date('Y-m-d', strtotime($enddate));
        }    
    }
    return $dates;
}

function getLatLong($address = null)
{
	$latLong = [];
	$latLong['lattitude'] = '';
	$latLong['longitude'] = '';
	if (!empty($address)) {
		$address = str_replace(" ", "+", $address);
		$json = file_get_contents("https://maps.google.com/maps/api/geocode/json?key=AIzaSyCjEHaWgv-lmblYJ-m0fp3lwfrWrgzQEPE&address=".urlencode($address)."&sensor=false");
		$json = json_decode($json);
		if ($json->status == 'OK') {
			$latLong['lattitude'] = $json->results[0]->geometry->location->lat;
			$latLong['longitude'] = $json->results[0]->geometry->location->lng;
		}
	}
	return $latLong;
}
function address($user)
{
	$address = [];
	if (isset($user->address) && !empty($user->address)) {
		$address[] = $user->address;
	}
	if (isset($user->city) && !empty($user->city)) {
		$address[] = $user->city;
	}
	if (isset($user->state) && !empty($user->state)) {
		$address[] = $user->state;
	}
	if (isset($user->country) && !empty($user->country)) {
		$address[] = $user->country;
	}
	return implode(',', $address);
}
function bindAddress($user)
{
	$address = [];
	if (isset($user->address) && !empty($user->address)) {
		$address[] = $user->address;
	}
	if (isset($user->city) && !empty($user->city)) {
		$address[] = $user->city;
	}
	if (isset($user->state) && !empty($user->state)) {
		$address[] = $user->state;
	}
	if (isset($user->country) && !empty($user->country)) {
		$address[] = $user->country;
	}
	$address = implode(' ', $address);
	echo str_replace(" ", "+", $address);
}
function ip_info($purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    $ip = $_SERVER['REMOTE_ADDR'];
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
  
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}

function phoneOtpSendVarification($email ='', $phone='')
{
    if (empty($phone)) {
        echo 'empty';
        die;
    }
    PhoneOtpVerification::where('phone', $phone)->where('otp_for', 'phone_number')->where('otp_status', 'sent')->delete();
    $otp_code = rand(1, 1000000);
    PhoneOtpVerification::insertGetId([
        'phone' => $phone,
        'otp_code' => $otp_code,
        'time' => date('H:i:s'),
        'otp_for' => 'phone_number',
        'otp_status' => 'sent',
        'created_at' => new DateTime,
        'updated_at' => new DateTime
    ]);
    $message = 'Your Otp for phone verification code is '.$otp_code;
    if (!empty($email)) {
        SendEmail($email,'Phone Otp DR Help Desk',$message,'');
    }       
    SendSMS($phone, $message);
    return $otp_code;
}
function dataPerPage(){
    $currentUser = getApiCurrentUser();
    return getSetting($currentUser->comp_id, 'data_per_page');
}
function getUserPermission($user)
{
    $privilages = \App\Privilage::where('user_id', $user->user_id)
                    ->join('modules', 'modules.module_id', 'privilage.module_id')
                    ->select('privilage.edit','privilage.add','privilage.view','privilage.delete','modules.name as module')->get();
    $modules = [];
    foreach ($privilages as $privilage) {
        $module = [];
        if ($privilage->edit == true) {
            $module[] = 'edit';
        }if ($privilage->view == true) {
            $module[] = 'view';
        }if ($privilage->delete == true) {
            $module[] = 'delete';
        }if ($privilage->add == true) {
            $module[] = 'add';
        }
        $modules[$privilage->module] = $module;
    }
    $user->role = \App\Roles::where('role_id', $user->role_id)->pluck('role')->first();
    $user->modules = $modules;
    return $user;
}
function getSettings($comp_id = null)
{
    $settings = \App\Settings::where('comp_id', $comp_id)->select('key','value')->get();
    $settingArray = [];
    foreach ($settings as $setting) {
        $settingArray[$setting->key] = maybe_decode($setting->value);
    }
    return $settingArray;
}
function getSetting($comp_id=null, $key = null)
{
    return maybe_decode(\App\Settings::where('comp_id', $comp_id)->where('key',$key)->get()->pluck('value')->first());
}
function emailTemplate($currentUser, $template = '', $taskData)
{ 
    $emailTemplate = \App\EmailTemplate::find(getSetting($currentUser->comp_id, $template));
    $msg = $emailTemplate->email_temp_message;
    $subject = $emailTemplate->email_temp_subject;
    foreach($taskData as $key => $val){           
        $msg = str_replace('%'.$key.'%', $val, $msg);
    }
    $body = view('EmailTemplate/TemplateEmail', compact('msg'));
    return compact('body', 'subject');
}
function calculateDaysAccTime($days,$start_time,$end_time)
{     
    $start_time_h = strtotime($start_time);
    $end_time_h = strtotime($end_time);
    if($end_time_h < $start_time_h) {
        $end_time_h += 24 * 60 * 60;
    }        
    $total_min = ($end_time_h - $start_time_h) / 60;
    if($total_min < 300)
    {
        $days = $days/2; 
    }
    return $days;         
}