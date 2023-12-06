<?php
/**
 * Multidimensional ArrayAccess
 *
 * Allows ArrayAccess-like functionality with multidimensional arrays.  Fully supports
 * both sets and unsets.
 *
 * @package WordPress
 * @subpackage Session
 * @since 3.7.0
 */

/**
 * Recursive array class to allow multidimensional array access.
 *
 * @package WordPress
 * @since 3.7.0
 */
class Recursive_ArrayAccess implements ArrayAccess, Iterator, Countable {
	/**
	 * Internal data collection.
	 *
	 * @var array
	 */
	protected $container = array();

	/**
	 * Flag whether or not the internal collection has been changed.
	 *
	 * @var bool
	 */
	protected $dirty = false;

	/**
	 * Default object constructor.
	 *
	 * @param array $data
	 */
	protected function __construct( $data = array() ) {
		foreach ( $data as $key => $value ) {
			$this[ $key ] = $value;
		}
	}

	/**
	 * Allow deep copies of objects
	 */
	public function __clone() {
		foreach ( $this->container as $key => $value ) {
			if ( $value instanceof self ) {
				$this[ $key ] = clone $value;
			}
		}
	}

	/**
	 * Output the data container as a multidimensional array.
	 *
	 * @return array
	 */
	public function toArray() {
		$data = $this->container;
		foreach ( $data as $key => $value ) {
			if ( $value instanceof self ) {
				$data[ $key ] = $value->toArray();
			}
		}
		return $data;
	}

	/*****************************************************************/
	/*                   ArrayAccess Implementation                  */
	/*****************************************************************/

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->container[ $offset ]);
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->container[ $offset ] ) ? $this->container[ $offset ] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $data ) {
		if ( is_array( $data ) ) {
			$data = new self( $data );
		}
		if ( $offset === null ) { // don't forget this!
			$this->container[] = $data;
		} else {
			$this->container[ $offset ] = $data;
		}

		$this->dirty = true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		unset( $this->container[ $offset ] );

		$this->dirty = true;
	}
	
	
	/*****************************************************************/
	/*                     Iterator Implementation                   */
	/*****************************************************************/

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		return current( $this->container );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function key() {
		return key( $this->container );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function next() {
		next( $this->container );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function rewind() {
		reset( $this->container );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function valid() {
		return $this->offsetExists( $this->key() );
	}

	/*****************************************************************/
	/*                    Countable Implementation                   */
	/*****************************************************************/

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function count() {
		return count( $this->container );
	}
}
