<?php

namespace App;

/*
	Template class for compile and render templates
*/

class Template {

	protected

		/**
		 * @var template path, based to templates dir
		 */
		$name = '',

		/**
		 * @var filepath of source template file
		 */
		$path = '',

		/**
		 * @var filepath of compiled template cache
		 */
		$compiledPath = '',

		/**
		 * @var contents of template source file
		 */
		$source = '',

		/**
		 * @var contents of compiled template
		 */
		$compiled = '',

		/**
		 * @var locale template variables, that are available in render
		 */
		$vars = [];


	/**
	 * Create template with file and setup local vars
	 *
	 * @param string $name
	 * @param array $vars
	 * @return void
	 */
	public function __construct($name, array $vars = null)
	{
		$this -> name = $name;
		$this -> path = DOCUMENT_ROOT . Config :: get('dirTemplates') . "/$name.tpl";
		if (is_array($vars)) {
			$this -> vars = $vars;
		}
	}


	/**
	 * Compiles and renders template to buffer, and return buffer content
	 *
	 * @return string
	 */
	public function render()
	{
		if ($this -> compile()) {
			ob_start();
			include $this -> compiledPath;
			return ob_get_clean();
		}
		return null;
	}


	/**
	 * Read template source, parse it and save to templates-cache dir.
	 * Return true, if compile is OK, otherwise return false.
	 *
	 * @return boolean
	 */
	// xxx check, need to compile or not
	private function compile()
	{
		if (file_exists($this -> path)) {
			$name = md5($this -> name);
			$this -> compiledPath = DOCUMENT_ROOT . Config :: get('dirTemplates') . "/__compiled/$name.ctpl";

			$this -> source = file_get_contents($this -> path);
			$this -> parse();

			file_put_contents($this -> compiledPath, $this -> compiled);
			return true;
		}
		return false;
	}


	/**
	 * Parse template source
	 * Replace tpl instructions by php code
	 *
	 * @return void
	 */
	private function parse()
	{
		// if, elseif, else, endif
		$source = preg_replace('/{{ if (\w[\w\.]*) }}/', '<?if($this->getvar(\'$1\')):?>', $this -> source);
		$source = preg_replace('/{{ elseif (\w[\w\.]*) }}/', '<?elseif($this->getvar(\'$1\')):?>', $source);
		$source = preg_replace('/{{ else }}/', '<?else:?>', $source);
		$source = preg_replace('/{{ endif }}/', '<?endif;?>', $source);

		// for, endfor
		$source = preg_replace('/{{ for (\w[\w\.]*) in (\w[\w\.]*) }}/', '<? if($this->getvar(\'$2\')): foreach ($this->getvar(\'$2\') as \$$1): $this->vars[\'$1\'] = \$$1; ?>', $source);
		$source = preg_replace('/{{ for (\w[\w\.]*),(\w[\w\.]*) in (\w[\w\.]*) }}/', '<? if($this->getvar(\'$3\')): foreach ($this->getvar(\'$3\') as \$$1=>\$$2): $this->vars[\'$1\'] = \$$1; $this->vars[\'$2\'] = \$$2; ?>', $source);
		$source = preg_replace('/{{ endfor }}/', '<? endforeach; endif; ?>', $source);

		// actions, uses
		$source = $this -> parseActions($source);
		$source = $this -> parseUse($source);

		// vars
		$source = preg_replace('/{{ (\w[\w\.\|]*) }}/', '<?=$this->getvar(\'$1\')?>', $source);

		$this -> compiled = $source;
	}


	/**
	 * Search $source for action-macroses like {{ macrosName "title" 123 }}
	 * Replace them by '$this->macrosNameAction()' calls with parameters, parsed from macros-string
	 *
	 * @param string $source
	 * @return string
	 */
	private function parseActions($source)
	{
		return preg_replace_callback('/{{ \#(\w+) (.*?)}}/', function ($matches) {
			if ($args = _array($matches, 2)) {
				if (preg_match_all('/((\w[\w\.]*)|(".*?"))/', $args, $m)) {
					$ms = $m[1];
					$vars = $m[2];
					$strings = $m[3];
					$useArgs = [];
					foreach ($ms as $i => $name) {
						$value = '';
						if ($strings[$i]) {
							$value = $strings[$i];
						} elseif ($vars[$i]) {
							$value = '$this->getvar(\'' . $vars[$i] . '\')';
						}
						$useArgs[] = $value;
					}
					$useArgs = implode(',', $useArgs);
				}
			}
			return '<?=$this->' . $matches[1] . 'Action(' . $useArgs . ')?>';
		}, $source);
	}


	/**
	 * Search for use-command like {{ use "inc/button" title="some_title" }}
	 * Replace 'use' macros in template by call of Use function with parameters, parsed from replaced string
	 *
	 * @param string $source
	 * @return string
	 */
	private function parseUse($source)
	{
		return preg_replace_callback('/{{ use "([\w\/\.\-\_]+)" (.*?)}}/', function ($matches) {
			$useArgs = '';
			if ($args = _array($matches, 2)) {
				if (preg_match_all('/(\w+)=((\w[\w\.]*)|(".*?"))/', $args, $m)) {
					$names = $m[1];
					$vars = $m[3];
					$strings = $m[4];
					$useArgs = [];
					foreach ($names as $i => $name) {
						$value = '';
						if ($strings[$i]) {
							$value = $strings[$i];
						} elseif ($vars[$i]) {
							$value = '$this->getvar(\'' . $vars[$i] . '\')';
						}
						$useArgs[] = '"' . $name . '"=>' . $value;
					}
					$useArgs = ",[" . implode(',', $useArgs) . "]";
				}
			}
			return '<?=$this->useTemplate(\'' . $matches[1] . '\'' . $useArgs . ')?>';
		}, $source);
	}


	/**
	 * Get variable from local template vars, or from View
	 * modify it by modifiers and return result value
	 * $name should be like `value` or `object.propertyName`
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getvar($name)
	{
		// extract modififiers
		$modifiers = null;
		if (strpos($name, '|') !== false) {
			$name = explode('|', $name);
			$modifiers = array_slice($name, 1);
			$name = reset($name);
		}

		// find path
		$path = explode('.', $name);
		$start = reset($path);
		$root = isset($this -> vars[$start]) ? $this -> vars[$start] : View :: get($start);

		// get value
		$value = '';
		if (count($path) > 1 and is_object($root)) {
			$value = $root -> {$path[1]};
		} elseif (count($path) > 1 and is_array($root)) {
			$value = isset($root[$path[1]]) ? $root[$path[1]] : null;
		} else {
			$value = $root;
		}

		// apply modifiers
		if (is_array($modifiers)) {
			foreach ($modifiers as $mod) {
				$method = $mod . 'Modifier';
				if (is_callable([$this, $method]))
					$value = $this -> {$method}($value);
			}
		}

		return $value;
	}


	/**
	 * Execute "use" command from compiled template,
	 * Creates and renders template with $name, and $args as local variables
	 * Return render result
	 *
	 * @param string $name
	 * @param array $args
	 * @return string
	 */
	private function useTemplate($name, array $args = null)
	{
		$template = new Template($name, $args);
		return $template -> render();
	}


	/**
	 * Executes {{ #attr name value }} macros
	 * Constructs html-attribute expression, if $value is set
	 *
	 * @param string $name
	 * @param string $value
	 * @return string
	 */
	private function attrAction($name, $value = null)
	{
		return $value ? $name .'="' . $value . '"' : '';
	}


	/**
	 * Executes `|escapeHtml` modifier upon $value
	 * Used to escape html attributes, value in inputs for example
	 *
	 * @param string $value
	 * @return string
	 */
	private function escapeHtmlModifier($value)
	{
		return htmlentities($value);
	}


	/**
	 * Executes `|datetime` modifier upon $value
	 * Used to translate MYSQL date format to human-readable date format
	 *
	 * @param string $value
	 * @return string
	 */
	private function datetimeModifier($value)
	{
		return \App\Text :: datetime($value);
	}


	/**
	 * Executes `|shortdate` modifier upon $value
	 * Used to translate MYSQL date format into '12 sent' format
	 *
	 * @param string $value
	 * @return string
	 */
	private function shortDateModifier($value)
	{
		return \App\Text :: shortdate($value);
	}


	/**
	 * Executes `|cutDate` modifier upon $value
	 * Returns only date from date-time MYSQL string
	 *
	 * @param string $value
	 * @return string
	 */
	private function cutDateModifier($value)
	{
		if ($time = strtotime($value)) {
			return date("Y-m-d", $time);
		}
		return '';
	}


}