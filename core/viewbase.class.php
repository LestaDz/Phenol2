<?php

namespace Core;

/**
 * View
 * 
 * @package Phenol2
 * @author LestaD
 * @copyright 2013
 * @version 1.4
 * @access public
 */
class ViewBase {
	protected $registry;
	
	// Наличие ошибок в работе
	protected $error = false;
	
	// Массив с переменными и значениями
	protected $values;
	
	// Содержимое шаблона
	protected $code = "";
	
	// Массив шаблонов для загрузки в переменные при рендеринге
	protected $childsv = array();
	
	// Имя шаблона, полное или относительное
	public $template;
	
	// Базовая папка со всеми шаблонами
	public $folder;
	
    // Текущий язык страницы 
	public $locale;
	
	public $v;
	
	/**
	 * Конструктор
	 * 
	 * @param string $template
	 * @return
	 */
	public function __construct( &$registry ) {
		$this->registry = $registry;
		$this->folder = '';
		$this->template = '';
		$this->locale = &$this->registry->locale;
		$this->values = new \stdClass();
		$this->v = &$this->values;
	}
	
	
	
	/**
	 * Назначение переменной значения
	 * 
	 * @param mixed $var - переменная в шаблоне
	 * @param mixed $value - значение переменной
	 * @return
	 */
	public function __set( $var, $value ) {
		if ( $this->error ) return;
		
		if ( $var ) {
			$this->values->{$var} = $value;
		}
	}
	public function set($var,$value){$this->__set($var,$value);}
	
		
	/**
	 * Добавление шаблона для вставки в основной шаблон
	 * 
	 * @param mixed $var
	 * @param mixed $tpl
	 * @return
	 */
	public function child( $var, $tpl )
	{
		if ( $this->error ) return;
		
		$tpl = $this->folder . DS .$tpl . '.tpl';
		$code = "";
		
		if ( file_exists( $tpl ) )
		{
			$code = file_get_contents($tpl, false) ?: "[ Error: Template can't be readed! ]";
		}
		else
		{
			//trigger_error("Template \"".$tpl."\" can not be loaded!");
			$code = "[ Error: Template can't be loaded! ]";
		}
		
		$this->childsv[$var] = $code;
	}
	
	
	
	public function tr( $var, $translate )
	{
		$this->set( $var, $this->locale->detect( $translate ) );
	}
	
		
	/**
	 * var=>template
	 * 
	 * @param mixed $childs
	 * @return
	 */
	public function childs( array $childs )
	{
		if ( $this->error ) return;
		foreach( $childs as $key=>$tpl )
		{
			$this->child($key,$tpl);
		}
	}
		
	/**
	 * Удаление кода шаблона предназначенного для вставки в основной шаблон
	 * 
	 * @param mixed $var
	 * @return
	 */
	public function removeChild( $var )
	{
		if ( $this->error ) return;
		
		unset( $this->childsv[$var] );
	}
	
	
	
	/**
	 * Возвращает значение переменной из шаблона
	 * 
	 * @param mixed $var - переменная в шаблоне
	 * @return mixed
	 */
	public function __get( $var ) {
		if ( $this->error ) return NULL;
		
		if ( isset( $var ) && $var != NULL && $var != "" ) {
			if ( isset( $this->values->{$var} ) ) {
				return $this->values->{$var};
			}
		}
		
		return NULL;
	}
	public function get($var){return $this->__get($var);}
	
	
	
		
	/**
	 * Добавляет значение переменной к уже существующему в конец
	 * @param mixed $var
	 * @param mixed $value
	 * @return void
	 */
	public function append( $var, $value )
	{
		$v = $this->get( $var );
		$this->set($var, $v.$value);
	}
	
	
		
	/**
	 * Добавляет значение переменной к уже существующему в начало
	 * 
	 * @param mixed $var
	 * @param mixed $value
	 * @return void
	 */
	public function prepend( $var, $value )
	{
		$v = $this->get( $var );
		$this->set($var, $value.$v);
	}
	
	
	
	/**
	 * Устанавливает содержимое шаблона
	 * Не удаляя при этом переменных
	 * 
	 * @param mixed $code - HTML код шаблона
	 * @return
	 */
	public function setCode( $code ) {
		if ( $this->error ) return false;
		
		if ( $code != NULL ) {
			$this->template = false;
			$this->code = $code;
		}
	}
	
	
	
	/**
	 * Читает все записи в ассоциативном массиве и устанавливает переменные
	 * 
	 * @param mixed $array
	 * @return
	 */
	public function assign( $array ) {
		if ( isset( $array ) && is_array( $array ) ) {
			foreach ( $array as $var => $value ) {
				$this->values->{$var} = $value;
			}
		}
	}
	
	
	public function vars( array $array )
	{
		foreach( $array as $key=>$value )
		{
			$this->set($key,$value);
		}
	}
    
	
	/**
	 * Удаление переменной из обработки
	 * 
	 * @param mixed $var - имя переменной для удаления
	 * @return
	 */
	public function remove( $var ) {
		if ( $this->error ) return;
		
		if ( $var ) {
			if ( isset( $this->values->{$var} ) ) {
				unset( $this->values->{$var} );
			}
		}
	}
	public function __unset($var){$this->remove($var);}
    
	
	
	/**
	 * Чтение шаблона в переменную
	 * 
	 * @return string - исходный код шаблона
	 */
	protected function ReadTemplate( $tpl )
	{
		if ( $this->template == false && 0 )
		{
			return $this->code;
		}
		
		if ( file_exists( $tpl ) && is_file( $tpl ) )
		{
			$c = file_get_contents($tpl, false);
			$code = ($c !== false) ? $c : "[ Error: Template can't be readed! ]";
			return $code;
		}
		else
		{
			$this->registry->error->errorTemplateRead($tpl);
		}
	}
	
	public function constants()
	{
		// Стандартные переменные
        $default = array();
        $default['YEAR'] = date("Y");
        $default['MONTH'] = date("m");
        $default['DAY'] = date("d");
        $default['ENGINE'] = ENGINE;
        $default['VERSION'] = VERSION;
        $default['AUTHOR'] = AUTHOR;
        $default['DOMAIN'] = $this->registry->fconfig->Server['Domain'];
        $default['SUBDOMAIN'] = $this->registry->detector->getCurrentSubdomain();
        $default['PURI'] = $this->registry->detector->baseuri;
        
        return $default;
	}
	
	
	
	/**
	 * Обработка шаблона
	 * 
	 * @return string - заполненный код страницы
	 */
	public function dispatch( $effectsclass ) {
		if ( $this->error ) return;
		
		$code = $this->template != false ? $this->ReadTemplate( $this->folder . str_replace('.tpl', '', $this->template) . '.tpl' ) : $this->code;
		
		// Врезка дочерних шаблонов
		foreach ( $this->childsv as $var => $value )
			$code = str_replace( "{\$" . $var . "}", $value, $code );
		
		
		// Перевод
		$vals = (array)$this->registry->locale->getAllArray();
		foreach ( $vals as $var => $value )
		{
			if ( !is_int( $value ) && !is_float($value) && !is_string($value) ) continue;
			$code = str_replace( "{_" . $var . "}", $value, $code );
		}
		
		/*
		$code = str_replace('{%', '<?', $code);
		$code = str_replace('%}', '?>', $code);
		*/
		$code = str_replace('{if ', '<?if(', $code);
		$code = str_replace(' then}', '){?>', $code);
		$code = str_replace('{else}', '<?}else{?>', $code);
		$code = str_replace('{endif}', '<?}?>', $code);
		$code = str_replace('{elseif ', '<?else if(', $code);
		$code = str_replace('{foreach ', '<?foreach(', $code);
		$code = str_replace('{endforeach}', '<?}?>', $code);
		$code = str_replace('{for ', '<?for(', $code);
		$code = str_replace('{endfor}', '<?}?>', $code);
		$code = str_replace('{break}', '<?break;?>', $code);
		$code = str_replace('{continue}', '<?continue;?>', $code);
		$code = str_replace('{while ', '<?while(', $code);
		$code = str_replace('{endwhile}', '<?}?>', $code);
		$code = str_replace('{die}', '<?die();?>', $code);
		
		$code = preg_replace('/{\$(.*?)}/','<?=\$$1?>', $code);
		$code = preg_replace('/{=(.*?)}/','<?=$1?>', $code);
		
		$all = array_merge((array)$this->values, $vals);
		$all['registry'] = $this->registry;
		
		//qrd($code);
		
		$sourcecode = "";
		
		$this->run( $sourcecode, $code, $all, $this );
		
		// Перевод
		$vals = (array)$this->registry->locale->getAllArray();
		foreach ( $vals as $var => $value )
		{
			if ( !is_int( $value ) && !is_float($value) && !is_string($value) ) continue;
			$sourcecode = str_replace( "{_" . $var . "}", $value, $sourcecode );
		}				
		
		$const = $this->constants();
		foreach ( $const as $c => $val )
		{
			$sourcecode = str_replace( "{" . $c . "}", $val, $sourcecode );
		}
		
		// Удаление комментариев в шаблоне
		$sourcecode = preg_replace("/({\;.*?})/", "", $sourcecode);
		$sourcecode = preg_replace("/^(;;.*?\r\n)/m", "", $sourcecode);
		$sourcecode = preg_replace("/{[\$].*?}/", '', $sourcecode);
		$sourcecode = preg_replace("/{_.*?}/", '', $sourcecode);
		
		return $sourcecode;
	}
	
	private function run ( &$_sc, $_c, $_d, &$view ) {
		extract( $_d );
		ob_start();
		
		eval("?>$_c<?php\r\n");
		
		$_sc = ob_get_contents();
		ob_end_clean();
	}
	
	
	public function _title($tag)
	{
		$this->tr('title', $tag);
	}
}
