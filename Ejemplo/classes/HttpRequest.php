<?php
/**
 * @author Enola
 */
class HttpRequest {
    private static $instancia;    
    public $get_params;
    public $post_params;
    public $session;
    public $request_method;
    
    protected function __construct(){
        $this->get_params= $_GET;
        $this->post_params= $_POST;
        $this->session= new Session();
        $this->request_method= $_SERVER['REQUEST_METHOD'];
    }
    /**
     * Crea una unica instancia y/o devuelve la actual
     */
    public static function getInstance(){
        if(!self::$instancia instanceof self){
            self::$instancia = new self();
        }
        return self::$instancia;
    }    
    /**
     * Devuelve un parametro GET si existe y si no devuelve NULL
     * @param string $nombre
     * @return null o string
     */
    public function param_get($nombre){
        if(isset($this->get_params[$nombre])){
            return $this->get_params[$nombre];
        }
        else{
            return NULL;
        }
    }    
    /**
     * Devuelve un parametro POST si existe y si no devuelve NULL
     * @param string $nombre
     * @return null o string
     */
    public function param_post($nombre){
        if(isset($this->post_params[$nombre])){
            return $this->post_params[$nombre];
        }
        else{
            return NULL;
        }
    }
}
?>
