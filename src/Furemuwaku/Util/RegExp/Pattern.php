<?php

namespace Yume\Fure\Util\RegExp;

use Closure;
use Stringable;

use Yume\Fure\Util\Arr;

/*
 * Pattern
 *
 * @package Yume\Fure\Util\RegExp
 */
final class Pattern implements Stringable
{
	
	/*
	 * Compiled regular expression.
	 *
	 * @access Public Readonly
	 *
	 * @values String
	 */
	public Readonly String $compiled;
	
	/*
	 * Regular expression flags.
	 *
	 * @access Public Readonly
	 *
	 * @values Array
	 */
	public Readonly Array $flags;
	
	/*
	 * Last posotion matched.
	 *
	 * @access Private
	 *
	 * @values Int
	 */
	private Int $index;
	
	/*
	 * Last subject matched.
	 *
	 * @access Private
	 *
	 * @values String
	 */
	private ? String $subject = Null;
	
	/*
	 * Construct method of class Pattern.
	 *
	 * @access Public Initialize
	 *
	 * @params String $pattern
	 *
	 * @return Void
	 */
	public function __construct( public Readonly String $pattern, Array | String $flags = [] )
	{
		// Avoid empty pattern.
		if( valueIsEmpty( $pattern ) ) throw new RegExpError( "Pattern can't be empty" );
		
		// Normalize pattern flags.
		$this->flags = is_array( $flags ) ? $flags : split( $flags );
		
		foreach( $this->flags As $flag )
		{
			// Checked flags.
			$checked = [];
			
			// Check if flags is not supported.
			if( RegExp::isFlag( $flag, False ) ) throw new RegExpError( [ $flag, $pattern ], RegExpError::MODIFIER_ERROR );
			
			// Check if there are duplicate flag.
			if( in_array( $flag, $checked ) ) throw new RegExpError( [ $flag, $pattern ], RegExpError::MODIFIER_DUPLICATE_ERROR );
			
			// Push checked flag.
			$checked[] = $flag;
		}
		$this->compiled = sprintf( "/%1\$s/%2\$s", $this->pattern, join( "", $this->flags ) );
	}
	
	/*
	 * Parse class to String.
	 *
	 * @access Public
	 *
	 * @return String
	 */
	public function __toString(): String
	{
		return( $this )->compiled;
	}
	
	/*
	 * Execute the given subject.
	 *
	 * @access Public
	 *
	 * @params String $subject
	 *
	 * @return Yume\Fure\Util\RegExp\Matches
	 */
	public function exec( String $subject ): ? Matches
	{
		$this->index = $this->subject === $subject ? $this->index : 0;
		$this->subject = $subject;
		
		// Explode string for avoid infinity loop.
		$explode = substr( $subject, $this->index );
		
		// Check if subject is matched.
		if( $matches = RegExp::match( $this->compiled, $explode ) )
		{
			return( $this )->process( $subject, $explode, $matches, $this->index );
		}
		return( Null );
	}
	
	/*
	 * Return last subject from exec.
	 *
	 * @access Public
	 *
	 * @return String
	 */
	public function getSubject(): ? String
	{
		return( $this )->subject;
	}
	
	/*
	 * @inherit Yume\Fure\Util\RegExp\RegExp::match
	 *
	 */
	public function match( String $subject ): ? Matches
	{
		// Check if subject is matched.
		if( $matches = RegExp::match( $this->compiled, $subject ) )
		{
			return( $this )->process( $subject, $subject, $matches );
		}
		return( Null );
	}
	
	/*
	 * Replace the given subject with replacement.
	 *
	 * @access Public
	 *
	 * @params Array|String $subject
	 * @params Callable|String $replace
	 * @params Int $limit
	 * @params Int &$count
	 * @params Int $flags
	 *
	 * @return Array|String
	 */
	public function replace( Array | String $subject, Callable | String $replace, Int $limit = -1, Int &$count = Null, Int $flags = 0 ): Array | String
	{
		if( $replace Instanceof Closure )
		{
			// Captured position.
			$index = 0;
			
			// Exploded sub string of subject.
			$explode = $subject;
			$callback = $replace;
			
			/*
			 * Handle replace.
			 *
			 * @params Array $matches
			 *
			 * @return Mixed
			 */
			$replace = fn( Array $matches ) => call_user_func( $callback, $this->process( $subject, $explode, $matches, $index ) );
		}
		return( RegExp::replace( $this->compiled, $subject, $replace ) );
	}
	
	/*
	 * Return match results.
	 *
	 * @access Private
	 *
	 * @params String $subject
	 * @params String &$explode
	 * @params Array $matches
	 * @params Int &$index
	 *
	 * @return Yume\Fure\Util\RegExp\Matches
	 */
	private function process( String $subject, String &$explode, Array $matches, Int &$index = 0 ): Matches
	{
		// Save previous index.
		$iprev = $index;
		
		// Get next index iteration.
		$search = $index += strpos( $explode, $matches[0] );
		$index += strlen( $matches[0] );
		
		// Get subject string for next iteration.
		$explode = substr( $subject, $index );
		
		// Create group instance.
		$groups = new Arr\Associative;
		$string = $matches[0];
		$stacks = "";
		
		// Mapping captured strings.
		foreach( $matches As $group => $value )
		{
			// If group has name, and if group has value.
			if( is_string( $group ) && valueIsNotEmpty( $value ) )
			{
				// Get position group in captured string.
				$post = strpos( $string, $value );
				$post += strlen( $stacks );
				
				$string = substr( $matches[0], $post );
				$stacks = substr( $matches[0], 0, $post );
				
				// Push groups.
				$groups[$group] = new Capture( $group, $value, $post );
				
				// Unset group name from matches.
				unset( $matches[$group] );
			}
		}
		return( new Matches( $matches, $groups, $search ) );
	}
	
}

?>