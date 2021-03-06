<?php


abstract class Controller extends \System\EngineBlock
{
	public function getClass()
	{
		return get_class($this);
	}
	
	
	/**
	 * Активация указанного действия для текущего контроллера
	 * 
	 * @param mixed $action
	 * @return void
	 */
	public function fireAction( $action )
	{
		$target = createClassname($action, 'Action');
		if ( is_callable(array(&$this, $target)) )
		{
			$this->registry->detector->dispatchPreActionEvents();
			call_user_func_array(array(&$this, $target),array());
		}
		else
		{
			$this->registry->error->errorControllerFireAction(get_class($this), $target);
		}
	}
	
	public function getName() {
		return $this->load->controller;
	}
	
	
	/**
	 * Перенаправление
	 * ( по моему не сложно догадаться )
	 * 
	 * @param mixed $url
	 * @param integer $status
	 * @return void
	 */
	protected function redirect( $url, $status = 302 )
	{
		header('Status: ' . $status);
		header('Location: ' . $this->detector->baseuri . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
		exit();
	}
	
	
	/**
	 * Вызов любого постороннего класса по его пути
	 * 
	 * @param mixed $controller - путь класса (common/default)
	 * @return
	 */
	protected function sub($controller)
	{
		if ( isset($this->registry->subctr->{$controller}) ) {
			return $this->registry->subctr->{$controller};
		}
		
		Ph::import('controller.'.str_replace('/', '.', $controller));
		$class = createClassname($controller, 'C');
		
		if ( class_exists($class) ) {
			return $this->subctr->{$controller} = new $class($this->registry);
		} else {
			return FALSE;
		}
		
		return FALSE;
	}
	
}


