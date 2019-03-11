<?php

include('lib/httpful.phar');

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

    switch($connectionStatus->code) {
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
            "Authorization" => "token ". $this->config["credentials"]["api_key"]
             );
    $response = "";

    switch($method['httpMethod']){
      case 'get':
        $response = \Httpful\Request::get($url)
          ->addHeaders( $headers )
          ->send();
        break;
      case 'post':
        $response = \Httpful\Request::post($url)
          ->body( json_encode($body) )
          ->addHeaders( $headers )
          ->send();
        break;
    }
    
    return $response;

  }

  function getResource( $resourceName ) {
    return $this->resources[ $resourceName ];
  }
 }

  /**
   * These classes represent the different resources of the API.
   */
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
      return $this->service->call($method, array("listId" => $listId) )->body;
    }

    
    public function getAllLists( $listId = null, $lists = [], $page = 1  ){
      
      $method = $this->methods['list'];
      
      $z = $this->service->call($method, array("listId" => $listId, 'page' => $page))->body;
      
      $lists[] = $z->items;

      if($z->currentPage < $z->pagesCount && $page<2){
        
        $page = $page+1;
        return $this->getAllLists(null, $lists, $page);

      }else{

        return $lists;
        
      }
      
    }
    
  }

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
      return $this->service->call($method, array("listId" => $listId) )->body;
    }

  }

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

  class Doppler_Exception_Invalid_Account extends Exception {};

  class Doppler_Exception_Invalid_APIKey extends Exception {};

?>