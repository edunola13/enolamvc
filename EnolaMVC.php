<?php
include 'classes/HttpRequest.php';
include 'classes/Session.php';
/**
 * 
 * @author Enola
 */
class EnolaMVC {
	public $file_config;
	public $folder_controllers;
	
	public function __construct($file_config = 'enolamvc.json', $folder_controllers = ''){
		$this->file_config= $file_config;
		$this->folder_controllers= $folder_controllers;
	}
    
    /**
     * Devuelve la uriapp relativa desde la base url en la que trabaja el MVC 
     */
    private function uriapp_mvc($base_mvc){
        //Cargo la URI segun el servidor - Esta siempre es todo lo que esta despues de www.edunola.com.ar o localhost/
        $uri_actual= $_SERVER['REQUEST_URI'];
        //Analizo la cantidad de partes de la baseurl para poder crear la URI correspondiente para la aplicacion
        $url_base= explode("/", $base_mvc);
        $uri_app= "";
        //0= http, 1= //, 2= dominio, >3= una carpeta cualquiera
        if(count($url_base) > 3){
           //Trabajar desde el 4to elemento del arreglo
           $count_url_base= count($url_base);
           for($i = 3; $i < $count_url_base; $i++){
              $uri_app .= "/" . $url_base[$i];
           }
        }
        //Elimino la parte de la URI que es fija y no es utilizada - Esto seria en caso de que haya carpeta
        $uri_actual= substr($uri_actual, strlen($uri_app));
        //Elimino "/" en caso de que haya una al final de la cadena
        $uri_actual= trim($uri_actual, "/");
        return $uri_actual;
    }
    
    /**
     * Devuelve la posicion actual en la queda el indice en el arreglo partes_uri_actual si mapea
     * FALSE en caso contrario 
     */
    private function maps_controller($partes_url, $partes_uri_actual){
        $mapea= TRUE;
        //Mantiene la pos actual para luego empezar a recorrer el mensaje y los parametros
        $pos_actual= 0;
        if(count($partes_uri_actual) >= count($partes_url)){
			$count_partes_uri_actual= count($partes_uri_actual);
			//Recorro todas las partes de la uri actual
			for($i= 0; $i < $count_partes_uri_actual; $i++){            
				$pos_actual= $i;
				//Si partes url tiene partes se controla
				if(count($partes_url) >= ($i + 1)){                
					if($partes_url[$i] != $partes_uri_actual[$i]){
					$mapea= FALSE;
					break;
					}
				}
				else{
					//Si no tiene mas partes coinciden
					$mapea= TRUE;
					break;
				}
			}
        }
		else{
            //Si es mas grande la url del archivo de configuracion no coinciden
            $mapea= FALSE;
		}
        if($mapea){
            //Devuelve la posicion actual de donde termina la coincidencia
            return $pos_actual;
        }
        else{
            return FALSE;
        }
    }
    
    /**
	 * Ejecucion del MVC
	 */
    public function control(){        
        $json_configuration= file_get_contents($this->file_config);
        $config= json_decode($json_configuration, true);
        //Base desde donde trabaja el MVC
        $base_mvc= $config['base_url'];
        //URIAPP relativa al MVC        
        $uriapp= $this->uriapp_mvc($base_mvc);
        /*
         * Analizo los controladores para ver cual mapea
         */
        $ejecutado= FALSE;
        foreach ($config['controllers'] as $controller) {
            $url= $controller['url'];
            $url= trim($url, "/"); 
            $partes_url= explode("/", $url);
			if($partes_url[0] == ''){
				$partes_url= array();
			}

            //Saco de la uri actual los parametros
            $uri_explode= explode("?", $uriapp);
            $uri_front= $uri_explode[0];
            //Separo la uri actual
            $partes_uri_actual= explode("/", $uri_front);
			if($partes_uri_actual[0] == ''){
				$partes_uri_actual= array();
			}
            //Llama al metodo para ver si mapea
            $mapea= $this->maps_controller($partes_url, $partes_uri_actual);
                        
            if($mapea !== FALSE){
                //Mapea contiene la posicion
                $pos_actual= $mapea;
                //Sacar el nombre del mensaje
                $mensaje= "";
                if(count($partes_uri_actual) == count($partes_url) || count($partes_uri_actual) == 0){
                    $mensaje= 'index';
                }
                else{
                    $mensaje= $partes_uri_actual[$pos_actual];
                }
                //Consigue la clase del controlador, analiza que contenga el metodo y lo ejecuta pasandole los
                //parametros correspondiente
                $dir= $this->folder_controllers . '/' . $controller['class'] . '.php';
                require $dir;
                $dir= explode("/", $controller['class']);
                $class= $dir[count($dir) - 1];
                $controlador= new $class();
                if(method_exists($controlador, $mensaje)){
                    //Sacar los parametros
                    $pos_actual+= 1;
                    $params= array();
                    for($i= $pos_actual; $i < count($partes_uri_actual); $i++){
                        $params[]= $partes_uri_actual[$i];
                    }
                    $controlador->params= $params;
					$controlador->httpRequest= HttpRequest::getInstance();
                    $controlador->$mensaje();
                    $ejecutado= TRUE;
                    break;
                }
				else{
					unset($controlador);
				}
                //Si el mensaje no existe pasa al proximo controlador
            }
        }        
        if(! $ejecutado){
			throw new Exception('Error Enola MVC' . 'Any controller map with the actual requirement');
        }
    }
}

?>
