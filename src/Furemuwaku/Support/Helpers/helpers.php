<?php

/*
 * Yume PHP Framework.
 *
 * @author Ari Setiawan
 * @create 05.02-2022
 * @update -
 * @github https://github.com/yfure/Yume
 *
 * By making this, it is hoped that developers can easily build
 * programs without writing room names or classes at length.
 *
 * Copyright (c) 2022 Ari Setiawan
 * Copyright (c) 2022 Yume Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use Yume\Fure\Error;
use Yume\Fure\IO\Buffer;
use Yume\Fure\IO\Path;
use Yume\Fure\Locale;
use Yume\Fure\Locale\Clock;
use Yume\Fure\Locale\DateTime;
use Yume\Fure\Logger;
use Yume\Fure\Main;
use Yume\Fure\Service;
use Yume\Fure\Support;
use Yume\Fure\Util;
use Yume\Fure\Util\Env;
use Yume\Fure\Util\RegExp;

/*
 * Command Line Interface colorize string.
 *
 * @access Public Static
 *
 * @params String $string
 * @params String $base
 *
 * @return String
 */
function colorize( String $string, ? String $base = Null ): String
{
	$result = "";
	$base ??= "\x1b[0m";
	$regexps = [
		"comment" => [
			"pattern" => "(?<comment>\#(?:\[[^\\]]*\])|(?:\#|\/\/)[^\n]*|\/\*.*?\*\/)",
			"ansicol" => "\x1b[1;38;5;250m",
			"rematch" => [
				"define",
				"version"
			]
		],
		"number" => [
			"pattern" => "(?<number>\b(?:\d+)\b)",
			"ansicol" => "\x1b[1;38;5;61m"
		],
		"define" => [
			"pattern" => "(?<define>(?:@|\\$)[a-zA-Z_][a-zA-Z0-9_\-\.]*)",
			"ansicol" => "\x1b[1;38;5;111m",
			"rematch" => [
				"symbol"
			]
		],
		"symbol" => [
			"pattern" => "(?<symbol>\\\|\:|\*|\-|\+|\/|\&|\%|\=|\;|\,|\.|\?|\!|\||\<|\>|\~)",
			"ansicol" => "\x1b[1;38;5;69m"
		],
		"bracket" => [
			"pattern" => "(?<bracket>\{|\}|\[|\]|\(|\))",
			"ansicol" => "\x1b[1;38;5;214m"
		],
		"boolean" => [
			"pattern" => "(?<boolean>\b(?:False|True|Null)\b)",
			"ansicol" => "\x1b[1;38;5;199m"
		],
		"type" => [
			"pattern" => "(?<type>\b(?:Array|Bool|Callable|Closure|Double|Float|Int|Integer|Mixed|Object|Resource|String|Void)\b)",
			"ansicol" => "\x1b[1;38;5;213m"
		],
		"version" => [
			"pattern" => "(?<version>\b[vV][\d]+(?:[\d\.]+[\d+])*\b)",
			"ansicol" => "\x1b[1;38;5;112m",
			"handler" => [
				"floating" => [
					"pattern" => "(?<floating>[\d\.]+)",
					"ansicol" => "\x1b[1;38;5;190m"
				]
			]
		],
		"yume" => [
			"pattern" => "(?<yume>\b(?:[yY]ume(?:\\\(?:App|Fure)(?:\\\[a-zA-Z_](?:[a-zA-Z0-9_\\\]+[a-zA-Z0-9_])*)*)*)\b)",
			"ansicol" => "\x1b[1;38;5;111m",
			"rematch" => [
				"symbol"
			]
		],
		"string" => [
			"pattern" => "(?P<string>(?<!\\\)(\".*?(?<!\\\)\"|\'.*?(?<!\\\)\'|`.*?(?<!\\\)`))",
			"ansicol" => "\x1b[1;38;5;220m",
			"handler" => [
				"curly" => [
					"pattern" => "(?<curly>(?<!\\\)\{(?:(?:[^\}\\\]|\\.)*)\})",
					"ansicol" => "\x1b[1;38;5;214m",
					"handler" => [
						"chars" => [
							"pattern" => "(?<chars>[a-zA-Z][a-zA-Z0-9\_]*)",
							"ansicol" => "\x1b[1;38;5;11m",
						],
						"define" => [
							"pattern" => "(?<define>\\$[a-zA-Z_][a-zA-Z0-9_]*)",
							"ansicol" => "\x1b[1;38;5;111m",
						],
						"number" => [
							"pattern" => "(?<number>\b(?:\d+)\b)",
							"ansicol" => "\x1b[1;38;5;61m"
						],
						"symbol" => [
							"pattern" => "(?<symbol>\{|\}|\[|\]|\(|\)|\<|\>|\-)",
							"ansicol" => "\x1b[1;38;5;214m"
						],
						"bracket" => [
							"pattern" => "(?<bracket>\{|\}|\[|\]|\(|\))",
							"ansicol" => "\x1b[1;38;5;214m"
						],
						"mismatch" => [
							"pattern" => "(?<mismatch>.)",
							"ansicol" => "\x1b[1;38;5;220m"
						]
					]
				],
				"bracket" => [
					"pattern" => "(?<bracket>(?<!\\\)\[(?:(?:[^\]\\\]|\\.)*)\])",
					"ansicol" => "\x1b[1;38;5;214m",
					"handler" => [
						"chars" => [
							"pattern" => "(?<chars>[a-zA-Z][a-zA-Z0-9\_]*)",
							"ansicol" => "\x1b[1;38;5;11m",
						],
						"define" => [
							"pattern" => "(?<define>\\$[a-zA-Z_][a-zA-Z0-9_]*)",
							"ansicol" => "\x1b[1;38;5;111m",
						],
						"number" => [
							"pattern" => "(?<number>\b(?:\d+)\b)",
							"ansicol" => "\x1b[1;38;5;61m"
						],
						"symbol" => [
							"pattern" => "(?<symbol>\{|\}|\[|\]|\(|\)|\<|\>|\-)",
							"ansicol" => "\x1b[1;38;5;214m"
						],
						"mismatch" => [
							"pattern" => "(?<mismatch>.)",
							"ansicol" => "\x1b[1;38;5;220m"
						]
					]
				],
				"hexadec" => [
					"pattern" => "(?<hexadec>\\\x[a-fA-F0-9]{2})",
					"ansicol" => "\x1b[1;38;5;85m"
				],
				"escape" => [
					"pattern" => "(?<escape>\\\(?:040|40|7|11|011|0113|113|377|81|[aA]|[bB]|cx|[dD]|ddd|e|f|g|[hH]|k|n|[pP]|[rR]|[sS]|t|[vV]|[wW]|xhh|Z))",
					"ansicol" => "\x1b[1;38;5;208m"
				],
				"define" => [
					"pattern" => "(?<define>\\$[a-zA-Z_][a-zA-Z0-9_]*)",
					"ansicol" => "\x1b[1;38;5;111m",
				]
			]
		]
	];
	
	// Building regular expression.
	$pattern = new RegExp\Pattern( join( "|", array_map( fn( Array $regexp ) => $regexp['pattern'], $regexps ) ), "ms" );
	$regansi = new RegExp\Pattern( "^(?:\e|\x1b|\033)\[([^m]+)m$" );
	
	// Split string with ansi color.
	$strings = preg_split( "/((?:\e|\x1b|\033)\[[0-9\;]+m)/", $string, flags: PREG_SPLIT_DELIM_CAPTURE );
	$strings = array_values( array_filter( $strings, fn( String $string ) => $string !== "" ) );
	
	$handler = static function( RegExp\Matches $match, String $escape, Closure $handler, Array $regexps )
	{
		// If captured has group name.
		if( count( $match->groups ) )
		{
			// Find group name.
			foreach( $match->groups->keys() As $group )
			{
				if( isset( $match->groups[$group] ) &&
					isset( $regexps[$group] ) &&
					isset( $regexps[$group]['ansicol'] ) )
				{
					break;
				}
			}
			
			// Get captured caharacters.
			$chars = $match->groups[$group]->value;
			
			// If regex has callback handler.
			if( $regexps[$group]['handler'] ?? Null )
			{
				// If callback is multiple handler.
				if( is_array( $regexps[$group]['handler'] ) )
				{
					$pattern = [];
					
					foreach( $regexps[$group]['handler'] As $callback )
					{
						$match[0] = $chars;
						
						// If callback is callable.
						if( is_callable( $callback ) )
						{
							$chars = call_user_func( $callback, $match );
						}
						else {
							$pattern[] = $callback['pattern'];
						}
					}
					
					// If pattern is available.
					if( count( $pattern ) >= 1 )
					{
						$pattern = new RegExp\Pattern( join( "|", $pattern ), "ms" );
						$chars = $pattern->replace( $chars, fn( RegExp\Matches $match ) => call_user_func( $handler,
							match: $match,
							escape: $regexps[$group]['ansicol'],
							handler: $handler,
							regexps: $regexps[$group]['handler']
						));
					}
				}
				else {
					$chars = call_user_func( $regexps[$group]['handler'], $match );
				}
			}
			
			// If regex is re-matchable.
			if( is_array( $regexps[$group]['rematch'] ?? Null ) )
			{
				// Building regular expression.
				$pattern = new RegExp\Pattern( join( "|", array_map( fn( String $regexp ) => $regexps[$regexp]['pattern'], $regexps[$group]['rematch'] ) ), "ms" );
				
				// Re-match characters.
				$chars = $pattern->replace( $chars, fn( RegExp\Matches $match ) => call_user_func( $handler,
					match: $match,
					escape: $regexps[$group]['ansicol'],
					handler: $handler,
					regexps: $regexps
				));
			}
			return( f( "{}{}{}{0}", $escape, $regexps[$group]['ansicol'], $chars ) );
		}
	};
	
	try
	{
		$last = $base;
		$escape = Null;
		$skipable = [];
		
		foreach( $strings As $idx => $string )
		{
			// Skip if string is skipable.
			if( in_array( $idx, $skipable ) ) continue;
			
			// Check if string is ansi color.
			if( $color = $regansi->match( $string ) )
			{
				$index = $idx +1;
				$escape = $last = $color[0];
				
				// If index is not out of range.
				if( isset( $strings[$index] ) )
				{
					while( $rescape = $regansi->match( $strings[$index] ) )
					{
						// Append index iteration as skipable.
						$skipable[] = $index;
						
						// Append ansi color.
						$escape .= $rescape[0];
						
						$last = $rescape[0];
						$index++;
						
						// Break if index is out of range.
						if( $strings[$index] ?? Null ) break;
					}
				}
				
				// Check if index is in skipable.
				if( in_array( $index +1, $skipable ) ) $index++;
				
				// Append index iteration as skipable.
				$skipable[] = $index;
			}
			else {
				$escape = $last;
				$index = $idx;
			}
			$string = $strings[$index];
			$result = $pattern->replace( $string, fn( RegExp\Matches $match ) => call_user_func( $handler,
				match: $match,
				escape: $escape,
				handler: $handler,
				regexps: $regexps
			));
		}
	}
	catch( Throwable $e )
	{
		echo $e;
		exit;
	}
	return( $result );
}

/*
 * @inherit Yume\Fure\Main\Main::config
 *
 */
function config( String $name, Mixed $optional = Null, Bool $shared = True, Bool $import = False ): Mixed
{
	return( Main\Main::config( ...func_get_args() ) );
}

/*
 * Dumping value.
 *
 * @params Mixed $value
 * @params Bool $colorize
 *  Automatically colorize contents.
 *  This usage only on command line.
 *
 * @return String
 */
function dump( Mixed $value, Bool $colorize = False ): String
{
	// Starting output buffering.
	$buffer = Buffer\Buffer::self();
	$buffer->start( fn( String $buffer ) => $colorize ? colorize( $buffer ) : $buffer );

	// Dumping variable value.
	var_dump( $value );

	// Getting output buffering.
	$string = $buffer->clean()->get();

	// Terminate output buffering.
	$buffer->end( $buffer::FLUSH );

	// Return output buffering.
	return( $string );
}

/*
 * Parse exception class into string.
 *
 * @params Throwable $e
 *
 * @return Void
 */
function e( Throwable $e ): Void
{
	$output = "";
	
	/*
	 * @inherit Yume\Fure\Error\YumeError::format
	 *
	 */
	$format = static function( Throwable $thrown )
	{
		$values = [
			"class" => $thrown::class,
			"message" => $thrown->getMessage(),
			"file" => $thrown->getFile(),
			"line" => $thrown->getLine(),
			"code" => $thrown->getCode(),
			"trace" => $thrown->getTrace(),
			"type" => $thrown->type ?? "None"
		];
		if( $thrown Instanceof Error\YumeError )
		{
			$values = [ "\n{class}: {message}\n{class}: File: {file}\n{class}: Line: {line}\n{class}: Type: {type}\n{class}: Code: {code}\n{class}: {trace}\n", ...$values ];
		}
		else {
			$values = [ "\n{class}: {message}\n{class}: File: {file}\n{class}: Line: {line}\n{class}: Code: {code}\n{class}: {trace}\n", ...$values ];
		}
		return( Util\Strings::format( ...$values ) );
	};
	if( $e Instanceof Error\YumeError )
	{
		$output = $e->__toString();
	}
	else {
		
		/*
		 * Current exception thrown.
		 *
		 */
		$stack = [
			$format( $error = $e )
		];
		
		// Getting previous exception throwns.
		while( $error = $error->getPrevious() ) $stack[] = $format( $error );
		
		// Push exception trace strings.
		$output .= join( "\n", array_reverse( $stack ) );
	}
	puts( "{}\n", YUME_CONTEXT_CLI ? colorize( $output ) : $output );
}

/*
 * @inherit Yume\Fure\Util\Env\Env::get
 *
 */
function env( String $name, Mixed $optional = Null ): Mixed
{
	return( Env\Env::get( ...func_get_args() ) );
}

/*
 * @inherit Yume\Fure\Util\Format::format
 *
 */
function f( String $format, Mixed ...$values ): String
{
	return( Util\Strings::format( $format, ...$values ) );
}

/*
 * @inherit Yume\Fure\Support\Package::import
 *
 */
function import( String $package, Mixed $optional = Null ): Mixed
{
	return( Support\Package::import( $package, $optional ) );
}

/*
 * @inherit Yume\Fure\Locale\Locale::translate
 *
 */
function lang( String $key, ? String $optional = Null, Bool $format = False, Mixed ...$values ): ? String
{
	return( Locale\Locale::translate( $key, $optional, $format, ...$values ) );
}

/*
 * Write new log or get Logger instance class.
 *
 * @params Int|String|Yume\Fure\Logger\LoggerLevel
 * @params String $message
 * @params Array $context
 *
 * @return Yume\Fure\Logger\LoggerInterface
 */
function logger( Int | Null | String | Logger\LoggerLevel $level = Null, ? String $message = Null, ? Array $context = Null ): ? Logger\LoggerInterface
{
	// Check if logger is not available.
	if( Service\Service::available( Logger\Logger::class, False ) )
	{
		Service\Service::register( Logger\Logger::class, new Logger\Logger(), False );
	}
	if( valueIsNotEmpty( $level ) && 
		valueIsNotEmpty( $message ) && 
		valueIsNotEmpty( $context) )
	{
		return( Service\Service::get( Logger\Logger::class ) )->log( $level, $message, $context );
	}
	return( Service\Service::get( Logger\Logger::class ) );
}

/*
 * @inherit Yume\Fure\Util\Path\Path::path
 *
 */
function path( String $path, Bool | Path\Paths $prefix_or_remove = False ): String
{
	return( Path\Path::path( $path, $prefix_or_remove ) );
}

/*
 * Print outputs.
 *
 * @params String $format
 *  Please see Yume\Fure\Util\Format::format
 * @params Mixed ...$values
 *  Please see Yume\Fure\Util\Format::format
 *
 * @return Void
 */
function puts( String $format, Mixed ...$values ): Void
{
	echo( Util\Strings::format( $format, ...$values ) );
}

/*
 * Alias of explode and str_split.
 *
 * @params String $string
 * @params Int|String $separator
 *  When the Int passed str_split will called.
 * @params Int $imit
 *  When the Int passed for separator this useless.
 *
 * @return Array
 */
function split( String $string, Int | String $separator = 1, Int $limit = PHP_INT_MAX ): Array
{
	return( is_int( $separator ) ? str_split( $string, $separator ) : explode( $separator, $string, $limit ) );
}

/*
 * Get value type.
 *
 * @params Mixed $value
 * @params String $optional
 * @params Bool $disable
 * @params Mixed $ref
 *
 * @return String
 */
function type( Mixed $value, ? String $optional = Null, Bool $disable = False, Mixed &$ref = Null ): Bool | String
{
	// If optional argument available.
	if( $optional !== Null ) return( ucfirst( $optional ) === type( $value, Null, $disable, $ref ) );
	
	// If value is Object type.
	if( is_object( $value ) )
	{
		return( $ref = $disable ? "Object" : $value::class );
	}
	return( $ref = ucfirst( gettype( $value ) ) );
}

/*
 * Check if value is empty.
 *
 * @params Mixed $value
 * @params Bool $optional
 *
 * @return Bool
 */
function valueIsEmpty( Mixed $value, ? Bool $optional = Null ): Bool
{
	try
	{
		// If value is Resource type.
		if( is_resource( $value ) ) $value = new Stream\Stream( $value );
		
		// If value is instance of Countable.
		if( is_countable( $value ) ) $value = $value->count();
		
		// If value is instance of Stream.
		if( $value Instanceof Stream\StreamInterface ) $value = $value->getSize();
		
		$empty = match( True )
		{
			is_int( $value ) => $value === 0,
			is_null( $value ) => True,
			is_bool( $value ) => $value === False,
			is_array( $value ) => $value === [],
			is_string( $value ) => preg_match( "/^([\r\t\n\s]*)$/", $value ) || $value === ""
		};
	}
	catch( UnhandledMatchError )
	{
		$empty = False;
	}
	return( $optional === Null ? $empty : $empty === $optional );
}

/*
 * Check if value is not empty.
 *
 * @params Mixed $value
 * @params Bool $optional
 *
 * @return Bool
 */
function valueIsNotEmpty( Mixed $value, ? Bool $optional = Null ): Bool
{
	return( $optional === Null ? valueIsEmpty( $value, False ) : valueIsEmpty( $value, False ) === $optional );
}

?>