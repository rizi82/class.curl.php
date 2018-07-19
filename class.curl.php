<?php
/**
* FileName: class.curl.php
* OO CURL Class
* Object oriented wrapper for the cURL library.
* @author Rizwan Akram 
* @version 1.0
*/
class CURL{
  
  public $type ;  // get , post, json 
  public $level ; // use this variable to skip and break a struck curl request
  public $content = array();
  public  $timeout = '';
  public $url = ''; 
  public $connection_timeout = 1;
  public $message = array();
  public $cookie_path	=	'';
  public $log_file = ''; // log pointer
  public $debContent = array();

  # call construct function to initialize the data
   public function __construct(){
     #  echo '<br/>CURL __construct called.';
	 $this->lwrite('CURL __construct called');
	   // create file class object

	/*   $this->url 		= 	$url;
	   $this->timeout   = 	$timeout;
	   $this->type		=	$type;
	   $this->level		=	$level;
	   $this->content	=   $content;
	   $this->cookie_path =	$cookie_path;	*/

   }
  
   # call function to proccess curl  	
   public function call_curl_script(){
	$tempArray = array();
    $response 	=	'';
    $this->lwrite('function call_curl_script called..');
    $this->lwrite('parameter url ['.$this->url.'] passed as parameter..');
	$this->lwrite('parameter timeout ['.$this->timeout.'] passed as parameter..');
	$this->lwrite('parameter type ['.$this->type.'] passed as parameter..');	
	$this->lwrite('parameter level ['.$this->level.'] passed as parameter..');	
	$this->lwrite('parameter cookie_path ['.$this->cookie_path.'] passed as parameter..');
/*	 echo '<br/>url: '. $this->url;
	 echo '<br/>timeout: '. $this->timeout;
	 echo '<br/>type: '. $this->type;
	 echo '<br/>$this->cookie_path:'.$this->cookie_path;*/
	   if(($this->level == 1) && file_exists($this->cookie_path) ){ // only delete when we are going for fresh run
	     $this->lwrite('Only delete cookie if Level = 1 and cookie exits') ;
	      unlink($this->cookie_path);
	   }

	   $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // We must specify a useragent here, without westcon doesn't return the __EVENTTARGET and __EVENTARGUMENT inputs
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		// only in case of json post
		if($this->type == 3) { // only in case of json post
	     	curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json'));     
		}
        // Let's follow the redirect
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        // Let's enable Cookies
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_path);
		// CURLINFO_HEADER_OUT for testing purpose, disable in live mode
		curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
		curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking

	 if ($this->type == 1 || $this->type == 3) {
		// We have a POST
		if(!empty($this->content)){
			/*if($this->level == 13){
			  echo 'this->content:<pre> '.print_r($this->content).'</pre>';
			}*/
			$fields_string = "";
			if($this->type == 1 ){
				//url-ify the data for the POST
				while(list($key,$value) = each($this->content)) {
					 $fields_string .= urlencode($key).'='.urlencode($value).'&';
				}
				$fields_string = rtrim($fields_string,'&');
			}
			elseif($this->type == 3 ){
				$fields_string = $this->content;

			}

			curl_setopt($ch,CURLOPT_POST,count($this->content));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connection_timeout);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			
			$this->debContent['content_post']  = $fields_string;
			
			unset($fields_string);
			
		}
		else
		{
			exit('No post content found!');
		}
		curl_setopt($ch,CURLOPT_HTTPGET,FALSE);
		#echo "<br>".$this->url;
		curl_setopt($ch, CURLOPT_URL, $this->url);
	} elseif ($this->type == 2) {
		// We have a GET
		curl_setopt($ch,CURLOPT_HTTPGET,TRUE);
		//$urlified = $url . rawurlencode($content);
		$this->url = str_replace(" ","%20",$this->url);
		$content = str_replace(" ","%20",$this->content);
		$urlified = $this->url . $content;
		#echo "<br>CALLING URL: ".$urlified;
		curl_setopt($ch, CURLOPT_URL, $urlified);

	}
        $body = curl_exec($ch);
	    list($header_response, $response) = explode("\r\n\r\n", $body, 2);  
         if(!curl_errno($ch)) {
			$info = curl_getinfo($ch);
			$err  = 0;	
			$this->message['err'] = $err;
			$this->message['errmsg'] = 'All OK, no Error found in curl execution.';
			$this->debContent['header_request']  = $info['request_header'];
			$this->debContent['header_response']  = $header_response;
	/*	   echo "<pre>";
			 print_r($info);
			echo "</pre>";*/
			}
			else{
				  $err     = curl_errno( $ch );
				  $errmsg  = curl_error( $ch );
				   $this->lwrite('Curl Return Errors with  Erro ['.$err.'] and ErrMsg: ['.$errmsg.'].');
				   $this->message['err'] = $err;
				   $this->message['errmsg'] = $errmsg;
		
			}

			//    [http_code] => 200
			  if(isset($info) && !empty($info['http_code'])){
				  if($info['http_code'] == 200){
					 $this->message['http_code'] = $info['http_code'];
					 $this->message['text'] = 'Curl Return http_code = '.$info['http_code'].', all OK';
					 $this->lwrite('Curl Return http_code = '.$info['http_code'].', all OK');
					# echo '<br/>Response: '.$response;
				  }
				   elseif($info['http_code'] == 404 ){
					 $this->message['http_code'] = $info['http_code'];
					 $this->lwrite('Curl Return http_code  = '.$info['http_code'].', NOT OK');
					 if((isset($this->debContent['debug'])) && ($this->debContent['debug'] == false)){ // skip if debugger is true
					  if($this->timeout < 12 ){
						 $this->lwrite('['.$this->url.'] not found, and we are at Level ['.$this->level.'].');
						 $this->timeout	=	$this->timeout	+ 3 ; // wait for 100 sec
						 $this->lwrite('['.$this->level.']  != 8');
							if($this->level != 8 ){
								 curl_close($ch);
								 $this->message['text'] = '['.$this->url.'] not found, need a sleep and recall script after ['.$this->timeout.'].';
								 $this->lwrite('['.$this->url.'] not found, need a sleep and recall script after ['.$this->timeout.'].');
								 sleep($this->timeout);
								 $this->message['text'] = 'Calling a script again if [http_code == 404]';
								 $this->lwrite('Calling a script again if [http_code == 404 && level != 8]');
								 $this->message = null;
								 $response = null;
								 $tempArray = null;
								 list($response, $tempArray, $this->message ) = $this->call_curl_script();
							}
							 else{
								  $this->lwrite('['.$this->url.'] not found, we are at Level ['.$this->level.'], we are skipping this link after
								  interval ['.$this->timeout.']');
								  $this->message['text']  = '['.$this->url.'] not found and level ['.$this->level.']';
							}
					    }
					 } // end if debugger 
				  }
				  elseif($info['http_code'] == 500){
					if((isset($this->debContent['debug'])) && ($this->debContent['debug'] == false)){ // skip if debugger is true
						 $this->message['http_code'] = $info['http_code'];
						 $this->message['text'] 	 = '['.$this->url.'] return 500 and eed a sleep and recall script after ['.$this->timeout.'].';
						 curl_close($ch);
						 $this->timeout	=	$this->timeout	+ 10 ; // wait for 100 sec
						 $this->message['text'] = 'Calling a script again if [http_code == 500]';
						 $this->lwrite( '['.$this->url.'] return 500 and  a sleep and recall script after ['.$this->timeout.'].');
						 sleep($this->timeout);
						 $this->message = null;
						 $response = null;
						 $tempArray = null;
						 list($response, $tempArray, $this->message ) = $this->call_curl_script();
				    } // end if debugger 
				}
					
			}

			if(isset($err) && !empty($err)){
			 if((isset($this->debContent['debug'])) && ($this->debContent['debug'] == false)){ // skip if debugger is true	
			  if($err == 28){   // Operation timeout. The specified time-out period was reached according to the conditions.
				$this->lwrite('Curl Return Errors with  Erro ['.$errmsg.'] means Operation timeout.');
				$this->lwrite('IF timeout ['.$this->timeout.'] <=20, we need to call script again after 20 sec interval');
				  if($this->timeout <= 100){ // if CURLOPT_TIMEOUT <= 100
				  	  $this->message['http_code']  = 0;  // return custom code
					  $this->message['text'] = 'curl_close';
					  curl_close($ch);
					  $this->timeout = $this->timeout + 20; // adding more time to CURLOPT_TIMEOUT
					  $this->message['text'] = 'Calling a script again if [err == 28]';
					  $this->message['text'] = 'Timeout error we calling call_curl_script with time [ '.$this->timeout.' sec ]';
					  $this->lwrite('Timeout error we calling call_curl_script with time [ '.$this->timeout.' sec ]');
					  sleep($this->timeout);
					  $this->message = null;
					  $response = null;
					  $tempArray = null;
					  list($response, $tempArray, $this->message ) = $this->call_curl_script();
				  }
			 
			  }
			 elseif($err == 5 || $err == 6 || $err == 7){  // Couldn't resolve host. The given remote host was not resolved.
			     $this->lwrite('Curl Return Errors with  Erro ['.$err.'] Couldn\'t resolve host. The given remote host was not resolved..');
				 $this->message['http_code']  = 0;  // return custom code
				 $this->message['text'] = 'Curl Return Errors with  Erro ['.$err.'] Couldn\'t resolve host. The given remote host was not resolved..';
				 #curl_close($ch);
				
				 if($this->timeout <= 100){ // if CURLOPT_TIMEOUT <= 100
				     $this->message['http_code']  = 0;  // return custom code
					 $this->message['text'] = 'curl_close';
					 curl_close($ch);
					 $this->timeout = $this->timeout + 10; // adding more 30m [1800] time to CURLOPT_TIMEOUT
					 $this->message['text'] = 'Timeout error we calling call_curl_script with time [ '.$this->timeout.' sec ]';
					 $this->lwrite('Timeout error we calling call_curl_script with time [ '.$this->timeout.' sec ],if [err == 5 || err == 6 || err == 7]');
					 $this->message['text'] = 'Calling a function [call_curl_script()] again with time [ '.$this->timeout.' sec ]';
					 sleep($this->timeout);
					 $this->message = null;
					 $response = null;
					 $tempArray = null;
					 list($response, $tempArray, $this->message ) = $this->call_curl_script();
					 $this->lwrite('-----------NEXT LINE OF call_curl_script------------ ');
				 }
				 else{
				     $this->message['http_code']  = 0;  // return custom code
					 $this->message['text'] = 'We reached End of time interval [ '.$this->timeout.' sec ] and we are stoping a script,
					  if [err == 5 || err == 6 || err == 7]';
					 $this->lwrite('We reached End of time interval [ '.$this->timeout.' sec ] and we are stoping a script.');
				 }
				 
			  }
		  }  // end if debugger 
	 }
    if(is_object($ch)){
    	curl_close($ch);
	}

	if( isset($err) && ($err == 0)){
		 $this->lwrite('################## Curl Return with no errors, ALL OK, lets read hidden input.###############');
	// Let's get our Hidden Inputs
		$oldSetting = libxml_use_internal_errors( true );
        $domDocument = new DOMDocument();
        $domDocument->loadHTML($response);

        $xpath = new DOMXPath( $domDocument );
		#if($this->level != 18 ){ // 18 to read hidden value at selectPaymentMethods function in class.synnex.php
        $inputs = $xpath->query('//input');
          // Let's get our hidden inputs
          foreach ( $inputs as $input ) {
			if ($input->getAttribute('type') == "hidden") {
				$tempArray[$input->getAttribute('name')] = $input->getAttribute('value');
			}
          }
		#}
	
		libxml_clear_errors(); 
		libxml_clear_errors();
		libxml_use_internal_errors( $oldSetting ); 
	}
	
	  # $this->lwrite('We reached End script block..');
   	   return array($response,$tempArray, $this->message);
	
   }
 
 public function lwrite($message){
     $date = date('Y-m-d');
	 $time = date('Y-m-d H:i:s');
	 $file = $this->log_file.$date;
	 $log = '[ '.$time.' ]'.$message."\n";
	 error_log($log, 3, $file);
	}


 public function send_email($class, $message){
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "X-Priority: 1 (Higuest)\n";
	$headers .= "X-MSMail-Priority: High\n";
	$headers .= "Importance: High\n"; 
	// Additional headers
	$headers .= 'To: Rizwan <rizwan.@xxx.com.au>'"\r\n";
	$headers .= 'From: '.$class.' class <info@xxx.com.au>' . "\r\n";
	$headers .= 'Cc: synnex@xxx.com.au' . "\r\n";
	$to = 'rizwana@xxx.com.au';
	$subject = $class. 'class inline error';
	// Mail it
	echo "<br/> Email Msg:".$message;
	#mail($to, $subject, $message, $headers);
 }

 
} // end class

?>