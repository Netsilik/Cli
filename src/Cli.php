<?php
namespace Netsilik/Lib;

abstract class Cli
{
	/** 
	 * @var array $_optionFlags
	 */
	protected $_optionFlags;
	
	/**
	 * @param int $argc
	 * @param array $argv
	 *
	 * @return string
	 */
	public function main($argc, array $argv);
	
	/**
	 * @param int $argc
	 * @param array $argv
	 * 
	 * @return array
	 */
	protected function _parseArguments($argc, array $argv)
	{
		if ($argc == 1) {
			return [null, []];
		}
		
		if (PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION >= 1) {
			$lastIndex = 0;
			$parsed = getopt(implode($this->_optionFlags['short']), $this->_optionFlags['long'], $lastIndex);
			$operator = $argv[$lastIndex];
			
			$lastOptionValue = $argv[$lastIndex - 1];
			
			
		} else { // backward compatible (PHP < 7.1.0) method
			$parsed = getopt(implode($this->_optionFlags['short']), $this->_optionFlags['long']);
			$operator = end($argv);
			
			$lastOptionValue = end($parsed);
			while (is_array($lastOptionValue)) {
				$lastOptionValue = end($lastOptionValue);
			}
		}
			
		$options = $this->_getOptions($parsed, $this->_optionFlags);
		
		if ($operator{0} == '-' || $operator == $lastOptionValue) {
			$operator = null;
		}

		return [$operator, $options];
	}
	
	/**
	 * Transform the parsed option strings into an array of options
	 * @param array $parsed
	 *
	 * @return array
	 */
	private function _getOptions(array $parsed)
	{
		
		$options = [];
		foreach ($this->_optionFlags['name'] as $key) {
			$options[$key] = [];
		}
		
		foreach ($parsed as $key => $value) {
			if (!is_array($value)) {
				$parsed[$key] = [$value];
			}
		}
		
		$shortCount = count($this->_optionFlags['short']);
		$longCount  = count($this->_optionFlags['long']);
		foreach ($parsed as $key => $values) {
			$found = false;
			if (strlen($key) > 1) { // must be long
				for ($i = 0; $i < $longCount; $i++) {
					if ($key <> substr($this->_optionFlags['long'][$i], 0, strlen($key))) {
						continue;
					}
					$found = true;
					break;
				}
			}
			
			if (!$found) { // look for a short match
				for ($i = 0; $i < $shortCount; $i++) {
					if ($key <> $this->_optionFlags['short'][$i]{0}) {
						continue;
					}
					$found = true;
					break;
				}
			}
			
			if ($found) {
				foreach ($values as $value) {
					if ($value === false) { // an option argument without value
						$options[ $this->_optionFlags['name'][$i] ][] = true;
						continue;
					}
					
					foreach (explode(',', $value) as $value) {
						$options[ $this->_optionFlags['name'][$i] ][] = $value;
					}
				}
			}
		}
		
		return $options;
	}
}