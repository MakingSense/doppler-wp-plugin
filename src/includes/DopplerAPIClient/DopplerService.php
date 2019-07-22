<?php

if( ! class_exists( 'Doppler_Service' ) ) :

class Doppler_Service
{

  private $config;

  private $resources;

  private $httpClient;

  function __construct($credentials = null) {
    
    $this->config = ['credentials' => []];

    $usr_account = '';

    if ($credentials)
      $this->setCredentials($credentials);

    if(isset($config['credentials'][ 'user_account'])){
      $usr_account = $config['credentials'][ 'user_account'] . '/';
    }

    $this->baseUrl = 'https://restapi.fromdoppler.com/accounts/'. $usr_account;

    $this->resources = [
	  'home'	=> new Doppler_Service_Home_Resource(
	    $this,
		array(
		  'methods' => array(
		  	'get' => array(
				'route' => '',
				'httpMethod' => 'get',
				'parameters' => null
			)
		  )
		)
	  ),
      'lists'   => new Doppler_Service_Lists_Resource(
        $this,
        array(
          'methods' => array(
            'get' => array(
              'route'        => 'lists/:listId',
              'httpMethod'  => 'get',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            ),
            'list' => array(
              'route'       => 'lists',
              'httpMethod'  => 'get',
              'state' => 'active',
              'parameters'  => array(
                'page' => array(
                  'on_query_string' => true
                ),
                'per_page' => 200
              )
            )
          )
        )
      ),
      'fields'  => new Doppler_Service_Fields(
        $this,
        array(
          'methods' => array(
            'list' => array(
              'route'       => 'fields',
              'httpMethod'  => 'get',
              'parameters'  => null
            )
          )
        )
      ),
      'subscribers'  => new Doppler_Service_Subscribers(
        $this,
        array(
          'methods' => array(
            'post' => array(
              'route'       => 'lists/:listId/subscribers',
              'httpMethod'  => 'post',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            )
          )
        )
      )
    ];
  }

  public function setCredentials($credentials) {

    $this->config['credentials'] = array_merge($credentials, $this->config['credentials'] );
    $connectionStatus = $this->connectionStatus();
    
    switch($connectionStatus['response']['code']) {
      case 200:
        return true;
        break;
      case 401:
        //TODO: Return formated error 
        break;
      case 403:
        //TODO: Return formated error
        break;
    }


  }

  public function connectionStatus() {
    $response = $this->call(array('route' => '', 'httpMethod' => 'get'));
	   return $response;
  }

  function call( $method, $args=null, $body=null ) {

    $url = 'https://restapi.fromdoppler.com/accounts/'. $this->config['credentials']['user_account'] . '/';
    
    $url .= $method[ 'route' ];
    $query = "";
    
    if( $args && count($args)>0 ){
      
      $resourceArg = $method[ 'parameters' ];
      
      foreach ($args as $name => $val) {
        
        isset( $resourceArg[ $name ])? $parameter = $resourceArg[ $name ] : $parameter = ''; 
        
        if( $parameter && $parameter[ 'on_query_string' ] ){
          $query .= $name . "=" . $val ;
        }else{
          $url = str_replace(":".$name, $val, $url);
        }
      
      }

      if(isset($resourceArg["per_page"])){
        $url.="?per_page=".$resourceArg["per_page"];
      }

      if(isset($resourceArg["page"])){
        $url.='&'.$query;
      }

    }


    $headers=array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-Doppler-Subscriber-Origin" => "Wordpress",
            "Authorization" => "token ". $this->config["credentials"]["api_key"]
             );
    $response = "";

    try{

      switch($method['httpMethod']){
        
        case 'get':
            
          /*
            $response = \Httpful\Request::get($url)
            ->addHeaders( $headers )
            ->timeoutIn(12)
            ->send();
            */
            $response = wp_remote_get($url, array(
              'headers'=>$headers,
              'timeout' => 12
            ));
            break;
        
        case 'post':
            /*
            $response = \Httpful\Request::post($url)
              ->body( json_encode($body) )
              ->addHeaders( $headers )
              ->timeoutIn(12)
              ->send();
            */
            $response = wp_remote_post($url, array(
              'headers'=>$headers,
              'timeout' => 12,
              'body'=> json_encode($body)
            ));
            break;
      }

    }
    catch(\Exception $e){
      $this->throwConnectionErr();
      return;
    }

    if(is_array($response)){
      return $response;
    }else{
      $this->throwConnectionErr();
      return;
    }

  }

  function getResource( $resourceName ) {
    return $this->resources[ $resourceName ];
  }

  function throwConnectionErr(){
    add_action( 'admin_notices', function(){
      ?>
      <div class="notice notice-error">
				<p>
					<?php _e( '<b>Doppler Forms:</b> Connection error. Please contact support.', 'doppler-form');?>
				</p>
			</div>
      <?php
    } );
  }

 }

endif;

  /**
   * These classes represent the different resources of the API.
   */

  
if( ! class_exists( 'Doppler_Service_Home_Resource' ) ) :

  class Doppler_Service_Home_Resource {
    
  	private $service;

    private $client;

    private $methods;

	function __construct( $service, $args )
    {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

	public function getUserAccount(){
    $method = $methods['get'];
      return $this->service->call($method, array());
	  }
  }

endif;

if( ! class_exists( 'Doppler_Service_Lists_Resource' ) ) :

  class Doppler_Service_Lists_Resource {

    private $service;

    private $client;

    private $methods;

    function __construct( $service, $args )
    {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

    public function getList( $listId ){
      //$method = $methods['get'];
      $method = $this->methods['get'];
     // return $this->service->call($method, array("listId" => $listId) )->body;
      return json_decode($this->service->call($method, array("listId" => $listId))['body']);
    }

    
    public function getAllLists( $listId = null, $lists = [], $page = 1  ){
      
      $method = $this->methods['list'];
      
      //$z = $this->service->call($method, array("listId" => $listId, 'page' => $page))->body;

      $z = json_decode($this->service->call($method, array("listId" => $listId, 'page' => $page))['body']);
      
      $lists[] = $z->items;

      if($z->currentPage < $z->pagesCount && $page<4){
        
        $page = $page+1;
        return $this->getAllLists(null, $lists, $page);

      }else{

        return $lists;
        
      }
      
    }
    
  }

endif;


if( ! class_exists( 'Doppler_Service_Fields' ) ) :

  class Doppler_Service_Fields {

    private $service;

    private $client;

    private $methods;

    function __construct( $service, $args )
    {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

    public function getAllFields( $listId = null ){
      $method = $this->methods['list'];
      return json_decode($this->service->call($method, array("listId" => $listId) )['body']);
    }

  }

endif;

if( ! class_exists( 'Doppler_Service_Subscribers' ) ) :

  class Doppler_Service_Subscribers {

    private $service;

    private $client;

    private $methods;

    function __construct( $service, $args )
    {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

    public function addSubscriber( $listId, $subscriber ){
      $method = $this->methods['post'];
      return $this->service->call( $method, array( 'listId' => $listId ),  $subscriber );
    }

  }

endif;

if( ! class_exists( 'Doppler_Exception_Invalid_Account' ) ){
  class Doppler_Exception_Invalid_Account extends Exception {};
}

if( ! class_exists( 'Doppler_Exception_Invalid_APIKey' ) ){
  class Doppler_Exception_Invalid_APIKey extends Exception {};
}

?>