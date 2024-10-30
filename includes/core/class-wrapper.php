<?php
/*!
 * Wrapper for core class functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Wrapper
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Abstract class used to implement the core class functionality.
 *
 * @since 1.0.0
 */
abstract class InvoiceEM_Wrapper
{
	/**
	 * Current page title.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected $_page_title = '';

	/**
	 * Base plugin object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM
	 */
	public $base = null;

	/**
	 * Stored object properties.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    array
	 */
	protected $_properties = array();

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->base = InvoiceEM();
	}

	/**
	 * Get a property based on the provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $name Name of the property to return.
	 * @return mixed        Property if it is found, otherwise an empty string.
	 */
	public function __get($name)
	{
		if
		(
			!isset($this->_properties[$name])
			||
			is_null($this->_properties[$name])
		)
		{
			return $this->_properties[$name] = $this->_default($name);
		}

		return $this->_properties[$name];
	}

	/**
	 * Check to see if a property exists with the provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string  $name Name of the property to check.
	 * @return boolean       True if the property is set.
	 */
	public function __isset($name)
	{
		if
		(
			!isset($this->_properties[$name])
			||
			is_null($this->_properties[$name])
		)
		{
			$default = $this->_default($name);

			if (!is_null($default))
			{
				$this->_properties[$name] = $default;
			}
		}

		return isset($this->_properties[$name]);
	}

	/**
	 * Set the property with the provided name to the provided value.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $name  Name of the property to set.
	 * @param  mixed  $value Value of the property to set.
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->_properties[$name] = $value;
	}

	/**
	 * Unset the property with the provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $name Name of the property to unset.
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->_properties[$name]);
	}

	/**
	 * Set the properties for the object.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array $properties Properties for the object.
	 * @return void
	 */
	protected function _set_properties($properties)
	{
		$properties = InvoiceEM_Utilities::check_array($properties);

		if (!empty($properties))
		{
			$this->_properties = array_merge($this->_properties, $properties);
		}
	}

	/**
	 * Get a default property based on the provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $name Name of the property to return.
	 * @return string       Null if the function is not overridden.
	 */
	protected function _default($name)
	{
		return null;
	}

	/**
	 * Push a value into a property array.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $name  Name of the property array to push the value into.
	 * @param  string $value Value to push into the property array.
	 * @param  mixed  $index Optional array index for the value to push.
	 * @return void
	 */
	public function _push($name, $value, $index = null)
	{
		$property = $this->$name;

		if (is_array($property))
		{
			if (is_null($index))
			{
				$property[] = $value;
			}
			else
			{
				$property[$index] = $value;
			}
		}

		$this->$name = $property;
	}
}
