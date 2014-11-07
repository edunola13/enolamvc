<?php

class Blog{

	public function __construct(){
	
	}
	
	public function index(){
		echo "index blog";
	}
	
	public function page(){
		echo $this->params[0];
		echo "page";
	}
	
	public function post(){
		echo $this->params[0];
		echo "post";
	}
}