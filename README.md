# class.curl.php

This class handle GET/POST method
User can post data in form of post array or json
This class is build to crawl a product data from synnex

<b>How to use this class</b>

include_once('class.curl.php');

$synnex = new SYNNEX('domain url');

class SYNNEX extends CURL{

  public $username ;  
  
  public $password ; // use this variable to skip and break a struck curl request
  
  public $reqContent 	 = array();
  
  public $prod_detail 	 = array();
  
  public $customEventArg = "";
  
  public $log			 = null; // give current log file pointer
  
  public $url           = '';
  
  public function __construct($siteURL){
  
  $this->url = $siteURL;
  
      list($response, $reqContent, $msgCodes ) = $this->call_curl_script();
      
        # degugger code start for getLoginPage functon
        
        if((isset($this->debContent['debug'])) && ($this->debContent['debug'] == true)){
        
          $debugArr 					= array();
          
          $debugArr['func'] 			= 'getLoginPage';	
          
          $debugArr['welcome']  		= 'debugger mode is enable and we are inside function [getLoginPage].';
          
          $debugArr['url']				=	$this->url;
          
          $debugArr['class']			=	'class.synnex.php';
          
          $debugArr['header_request']	=	$this->debContent['header_request'];
          
          $debugArr['header_response']	=	$this->debContent['header_response'];
        }
        if(isset($response) && $msgCodes['http_code'] == 200 ){
        
          if(isset($this->debContent['func'])){
          
             $debugArr['http_code']  	  =   $msgCodes['http_code'];
             
             $debugArr['err']   	  	  =   false;
             
             $debugArr['css'] 		  =   'good';	 
             
             $debugArr['text'] 		  =   'We get http_code = ['.$msgCodes['http_code'].'] and we expected [200].';
             
            }
            
        }
        
   }
   
  }
