<?php

namespace Yume\Fure\Util\Array;

use Traversable;

use Yume\Fure\Error;

/*
 * Lists
 *
 * @extends Yume\Fure\Util\Array\Arrayable
 *
 * @package Yume\Fure\Util\Array
 */
class Lists extends Arrayable
{
	
	/*
	 * Construct method of class Lists.
	 *
	 * @access Public Instance
	 *
	 * @params Array|Yume\Fure\Util\Array\Arrayable|Traversable $data
	 * @params Bool $keep
	 *
	 * @return Void
	 */
	public function __construct( Array | Arrayable $data = [], Bool $keep = False )
	{
		// Copy data from passed Arrayable instance.
		if( $data Instanceof Arrayable ) $data = $data->data;
		
		// Copy data from passed Traversable.
		if( $data Instanceof Traversable ) $data = toArray( $data, $keep );
		
		// Check if keep position is enabled.
		if( $keep )
		{
			// Check if array is not lists.
			if( array_is_list( $data ) === False )
			{
				throw new Error\TypeError( "Can't keep array position because the array passed is not Lists" );
			}
		}
		else {
			
			// Allowed any array type to pass.
			// Because the array list just need value only.
			$data = array_values( $data );
		}
		foreach( $data As $idx => $val )
		{
			$this->assert( $idx );
			$this->data[$idx] = $val;
		}
		$this->keys = array_keys(
			$this->data
		);
	}
	
	/*
	 * Assertion array key/ index.
	 *
	 * @access Private
	 *
	 * @params Mixed $offset
	 *
	 * @return Void
	 *
	 * @throws Yume\Fure\Error\AssertionError
	 */
	private function assert( Mixed &$offset ): Void
	{
		// Throw if offset is invalid numeric value.
		if( is_numeric( $offset ) === False )
		{
			throw new Error\AssertionError([ \Numeric::class, type( $offset ) ]);
		}
		$offset = ( Int ) $offset;
	}
	
	/*
	 * Whether an offset exists.
	 *
	 * @access Public
	 *
	 * @params Mixed $offset
	 *
	 * @return Bool
	 */
	public function offsetExists( Mixed $offset ): Bool
	{
		$this->assert( $offset );
		return( in_array( $offset, $this->keys ) && isset( $this->data[$offset] ) );
	}
	
	/*
	 * Offset to retrieve.
	 *
	 * @access Public
	 *
	 * @params Mixed $offset
	 *
	 * @return Mixed
	 */
	public function offsetGet( Mixed $offset ): Mixed
	{
		$this->assert( $offset );
		return( $this->keys[$offset] ?? False ? $this->data[$this->keys[$offset]] ?? Null : Null );
	}
	
	/*
	 * Assign a value to the specified offset.
	 *
	 * @access Public
	 *
	 * @params Mixed $offset
	 * @params Mixed $value
	 *
	 * @return Void
	 */
	public function offsetSet( Mixed $offset, Mixed $value ): Void
	{
		// Check if value is array.
		if( is_array( $value ) )
		{
			// If value is Array list.
			if( array_is_list( $value ) )
			{
				$value = new Lists( $value );
			}
			else {
				$value = new Associative( $value );
			}
		}
		
		// Check if position is passed by iteration/ push e.g $x[]
		if( $offset === Null )
		{
			$this->data[] = $value;
			$this->keys = array_keys( $this->data );
		}
		else {
			$this->assert( $offset );
			$this->data[$this->keys[$offset]] = $value;
		}
	}
	
	/*
	 * Unset an offset.
	 *
	 * @access Public
	 *
	 * @params Mixed $offset
	 *
	 * @return Void
	 */
	public function offsetUnset( Mixed $offset ): Void
	{
		$this->assert( $offset );
		
		// Check if array key is exists.
		if( is_numeric( $index = array_search( $offset, $this->keys ) ) )
		{
			unset( $this->keys[$index] );
		}
		unset( $this->data[$offset] );
	}
	
}

?>