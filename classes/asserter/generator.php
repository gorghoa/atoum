<?php

namespace mageekguy\atoum\asserter;

use
	mageekguy\atoum,
	mageekguy\atoum\exceptions
;

class generator
{
	protected $test = null;
	protected $locale = null;
	protected $aliases = array();

	public function __construct(atoum\test $test = null, atoum\locale $locale = null)
	{
		if ($test !== null)
		{
			$this->setTest($test);

			if ($locale === null)
			{
				$locale = $test->getLocale();
			}
		}

		$this->setLocale($locale ?: new atoum\locale());
	}

	public function __get($property)
	{
		switch ($property)
		{
			case 'if':
			case 'and':
			case 'then':
				return $this;

			case 'assert':
				return $this->assert();

			default:
				return $this->getAsserterInstance($property);
		}
	}

	public function __set($asserter, $class)
	{
		$this->setAlias($asserter, $class);
	}

	public function __call($method, $arguments)
	{
		switch ($method)
		{
			case 'if':
			case 'and':
				return $this;

			default:
				return $this->getAsserterInstance($method, $arguments);
		}
	}

	public function getTest()
	{
		return $this->test;
	}

	public function getScore()
	{
		return $this->test === null ? null : $this->test->getScore();
	}

	public function setLocale(atoum\locale $locale)
	{
		$this->locale = $locale;

		return $this;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function getAsserterClass($asserter)
	{
		if (isset($this->aliases[$asserter]) === true)
		{
			$asserter = $this->aliases[$asserter];
		}

		if (substr($asserter, 0, 1) != '\\')
		{
			$asserter = __NAMESPACE__ . 's\\' . $asserter;
		}

		return $asserter;
	}

	public function setTest(atoum\test $test)
	{
		$this->test = $test;

		return $this->setLocale($test->getLocale());
	}

	public function setAlias($alias, $asserterClass)
	{
		$this->aliases[$alias] = $asserterClass;

		return $this;
	}

	public function getAliases()
	{
		return $this->aliases;
	}

	public function resetAliases()
	{
		$this->aliases = array();

		return $this;
	}

	public function assert($case = null)
	{
		if ($this->test !== null)
		{
			$this->test->assert($case);
		}

		return $this;
	}

	public function when(\closure $closure)
	{
		$closure();

		return $this;
	}

	protected function getAsserterInstance($asserterName, array $arguments = null)
	{
		$class = $this->getAsserterClass($asserterName);

		if (class_exists($class, true) === false)
		{
			throw new exceptions\logic\invalidArgument('Asserter \'' . $class . '\' does not exist');
		}

		$asserter = new $class($this);

		if ($arguments !== null && sizeof($arguments) > 0)
		{
			call_user_func_array(array($asserter, 'setWith'), $arguments);
		}

		return $asserter;
	}
}

?>
