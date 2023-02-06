<?php

namespace Yume\Fure\Support\File;

use Yume\Fure\Error;
use Yume\Fure\Locale\DateTime;
use Yume\Fure\Support\Path;
use Yume\Fure\Util;
use Yume\Fure\Util\Json;
use Yume\Fure\Util\RegExp;

/*
 * File
 *
 * @package Yume\Fure\Support\File
 */
abstract class File
{
	
	/*
	 * PHP Valid file open read modes.
	 *
	 * @access Public Static
	 *
	 * @values String
	 */
	public const READABLE_MODES = "/^(r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+)$/";
	
	/*
	 * PHP Valid file open write modes.
	 *
	 * @access Public Static
	 *
	 * @values String
	 */
	public const WRITABLE_MODES = "/^(a|w|r\+|rb\+|rw|x|c)$/";
	
	/*
	 * PHP File open modes.
	 *
	 * @access Protected
	 *
	 * @values Array
	 */
	protected static Array $modes = [
		"r",
		"r+",
		"rb",
		"rw",
		"a+",
		"ab+",
		"w",
		"wb+",
		"x",
		"x+",
		"xb+",
		"c+",
		"cb+"
	];
	
	/*
	 * File open assertion mode.
	 *
	 * @access Private Static
	 *
	 * @params String $file
	 * @params String $mode
	 *
	 * @return Void
	 *
	 * @throws Yume\Fure\Error\AssertionError
	 * @throws Yume\Fure\Support\File\FileError
	 */
	private static function assert( String $file, String $mode ): Void
	{
		try
		{
			// If file open mode is invalid mode.
			if( in_array( $mode, self::$modes ) === False )
			{
				throw new Error\AssertionError( [ "mode", self::$modes, $mode ], Error\AssertionError::VALUE_ERROR );
			}
		}
		catch( Error\AssertionError $e )
		{
			throw new FileError( [ $file, $mode ], FileError::MODE_ERROR, $e );
		}
	}
	
	/*
	 * Changes file mode.
	 *
	 * @access Public Static
	 *
	 * @params String $file.
	 * @params Int $permissions
	 *
	 * @return Bool
	 */
	public static function chmod( String $file, Int $permissions ): Bool
	{
		return( chmod( Path\Path::path( $file ), $permissions ) );
	}
	
	/*
	 * Check if file is executable.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function executeable( String $file ): Bool
	{
		return( is_executable( Path\Path::path( $file ) ) );
	}
	
	/*
	 * Check if file is exists.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function exists( String $file ): Bool
	{
		return( file_exists( Path\Path::path( $file ) ) && is_file( Path\Path::path( $file ) ) );
	}

	/*
	 * Check if file open mode is readable.
	 * 
	 * @access Public Static
	 * 
	 * @params String $mode
	 * 
	 * @return Bool
	 */
	public static function isReadableMode( String $mode ): Bool
	{
		return( RegExp\RegExp::test( self::READABLE_MODES, $mode ) );
	}

	/*
	 * Return if file open is writable mode.
	 * 
	 * @access Public Static
	 * 
	 * @params String $mode
	 * 
	 * @return Bool
	 */
	public static function isWritableMode( String $mode ): Bool
	{
		return( RegExp\RegExp::test( self::WRITABLE_MODES, $mode ) );
	}
	
	/*
	 * Decode String from file content.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 * @params Bool $associative
	 * @params Int $depth
	 * @params Int $flags
	 *
	 * @return Array
	 */
	public static function json( String $file, ? Bool $associative = Null, Int $depth = 512, Int $flags = 0 ): Array
	{
		return( Json\Json::decode( self::read( $file ), $associative, $depth, $flags ) );
	}
	
	/*
	 * Return if file is modified.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function modified( String $file ): Bool
	{
		// ...
		$time = self::time( $file );
		
		// ...
		clearstatcache();
		
		// ...
		return( $time->getTimestamp() != self::time( $file )->getTimestamp() );
	}
	
	/*
	 * Check if file is not found.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function none( String $file ): Bool
	{
		return( self::exists( $file ) === False );
	}
	
	/*
	 * Opens file or URL.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 * @params String $mode
	 * @params Bool $include
	 * @params Resource $context
	 *
	 * @return Resource
	 *
	 * @throws Yume\Fure\Support\File\FileError
	 * @throws Yume\Fure\Support\Path\PathNotFoundError
	 */
	public static function open( String $file, String $mode = "r", Bool $include = False, $context = Null )
	{
		// File mode assertion.
		self::assert( $file, $mode );
		
		// Check if the filename is a directory.
		if( Path\Path::is( $file ) )
		{
			throw new FileError( $file, FileError::FILE_ERROR );
		}
		
		// Check if such a directory exists.
		if( Path\Path::exists( $fpath = Util\Str::pop( $file, "/", True ) ) )
		{
			return( fopen( Path\Path::path( $file ), $mode, $include, $context ) );
		}
		throw new FileError( [ $file, $fpath ], FileError::PATH_ERROR, new Path\PathNotFoundError( $fpath ) );
	}
	
	/*
	 * Read the contents of the file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 * @params Resource $context
	 * @params Bool $close
	 *
	 * @return String
	 *
	 * @throws Yume\Fure\Support\File\FileError
	 * @throws Yume\Fure\Support\File\FileNotFoundError
	 * @throws Yume\Fure\Support\Path\PathError
	 * @throws Yume\Fure\Support\Path\PathNotFoundError
	 */
	public static function read( String $file, $context = Null, Bool $close = True ): String
	{
		// Check if context is Resource type.
		if( is_resource( $context ) )
		{
			$fopen = $context;
		}
		else {
			
			// Get file pathname.
			$fpath = Util\Str::pop( $file, "/", True );
			
			// Check if the filename is a directory.
			if( Path\Path::exists( $file ) )
			{
				throw new FileError( $file, FileError::FILE_ERROR );
			}
			
			// Check if such directory is exists.
			if( Path\Path::exists( $fpath ) )
			{
				// Check if such directory is unreadable.
				if( Path\Path::readable( $fpath ) )
				{
					// Check if such a file exists.
					if( self::none( $file ) )
					{
						throw new FileNotFoundError( $file );
					}
					
					// Check if such files are readable.
					if( self::readable( $file ) === False )
					{
						throw new FileError( $file, FileError::READ_ERROR );
					}
					
					// Binary-safe file open.
					$fopen = fopen( Path\Path::path( $file ), "r" );
				}
				else {
					throw new FileError( $file, FileError::READ_ERROR, new Path\PathError( $fpath, Path\PathError::READ_ERROR ) );
				}
			}
			else {
				throw new FileNotFoundError( $file, previous: new Path\PathNotFoundError( $fpath ) );
			}
		}
		
		// Check if file success open.
		if( $fopen )
		{
			// Get file size.
			$fsize = fsize( $fopen, 13421779 );
			
			// Reader stack.
			$fread = "";
			
			// Binary-safe file read.
			while( feof( $fopen ) === False )
			{
				$fread .= fread( $fopen, $fsize );
			}
			
			// Closes an open file pointer.
			fclose( $fopen );
			
			// Return readed contents.
			return( $fread );
		}
		else {
			throw new FileError( $file, FileError::OPEN_ERROR );
		}
	}
	
	/*
	 * Read file contents and split file contents with endline.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 * @params Bool $skip
	 * @params Resource $context
	 *
	 * @return Array
	 */
	public static function readline( String $file, Bool $skip = false, $context = Null ): Array
	{
		// Reading file contents.
		$fread = self::read( $file, $context );
		
		// Split file contents with end line.
		$fline = explode( "\n", $fread );
		
		// If empty line skip.
		if( $skip )
		{
			// Mapping Lines.
			foreach( $fline As $i => $line )
			{
				// Check if the line is empty.
				if( valueIsEmpty( $line ) )
				{
					// Destroy the line.
					unset( $fline[$i] );
				}
			}
		}
		return( $fline );
	}
	
	/*
	 * Check if file is readable.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function readable( String $file ): Bool
	{
		return( is_readable( Path\Path::path( $file ) ) );
	}
	
	/*
	 * Get file size.
	 *
	 * @access Public Static
	 *
	 * @params Resource|String $file
	 * @params Int|String $optional
	 *
	 * @return String|Int
	 *
	 * @throws Yume\Fure\Support\File\FileNotFoundError
	 */
	public static function size( $file, Int | String $optional = 0 ): Int | String
	{
		// Default file size.
		$fsize = 0;
		
		// Check if file is Resource type.
		if( is_resource( $file ) )
		{
			// Get file information.
			$fstat = fstat( $file );
			
			// Check if info is available.
			if( $fstat !== False )
			{
				$fsize = $fstat['size'] ?? 0;
			}
		}
		else {
			
			// Check if such a file exists.
			if( self::exists( $file ) )
			{
				$fsize = filesize( Path\Path::path( $file ) );
			}
			else {
				throw new FileNotFoundError( $file );
			}
		}
		return( $fsize ?: ( is_int( $optional ) ? $optional : strlen( $optional ) ) );
	}
	
	/*
	 * Get last file modified timestamp.
	 * This method DateTime class instance from file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Yume\Fure\Locale\DateTime\DateTime
	 *
	 * @throws Yume\Fure\Support\File\FileNotFoundError
	 */
	public static function time( String $file ): DateTime
	{
		// Check if such a file exists.
		if( self::exists( $file, True ) )
		{
			// Get timestamp value from file.
			$time = filemtime( Path\Path::path( $file ) );
			
			// Create new instance of DateTime class.
			$date = new DateTime;
			$date->setTimestamp( $time );
			
			// Return DateTime instance.
			return( $date );
		}
		throw new FileNotFoundError( $file );
	}
	
	/*
	 * Remove file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function unlink(): Bool
	{
		return( unlink( Path\Path::path( $file ) ) );
	}
	
	/*
	 * Write or create a new file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 * @params Resource $context
	 *
	 * @return Bool
	 *
	 * @throws Yume\Fure\Error\PermissionError
	 * @throws Yume\Fure\Support\File\FileError
	 */
	public static function write( String $file, ? String $fdata = Null, String $fmode = "w", $context = Null ): Bool | Int
	{
		// Default function return.
		$fwrite = False;
		
		// Check if context is Resource type.
		if( is_resource( $context ) )
		{
			$fopen = $context;
		}
		else {
			
			// Get file pathname.
			$fpath = Util\Str::pop( $file, DIRECTORY_SEPARATOR );
			
			// Check if the filename is a directory.
			if( Path\Path::exists( $file ) )
			{
				throw new FileError( $file, FileError::FILE_ERROR );
			}
			
			// Check if such a directory exists.
			if( Path\Path::exists( $fpath ) === False )
			{
				Path\Path::mkdir( $fpath );
			}
			
			// Check if such directory is unwritable.
			if( Path\Path::writable( $fpath ) === False )
			{
				throw new Error\PermissionError( $fpath, Error\PermissionError::WRITE_ERROR );
			}
			
			// Check if such a file exists.
			if( self::exists( $file ) )
			{
				// Check if such files are unwritable.
				if( self::writable( $file ) === False )
				{
					throw new Error\PermissionError( $file, Error\PermissionError::WRITE_ERROR );
				}
			}
			
			// Add prefix base path.
			$fname = path( $file );
			
			// Binary-safe file open.
			$fopen = fopen( $fname, $fmode );
		}
		
		// File contents.
		$fdata = $fdata ?? "";
		
		// Check if file success open.
		if( $fopen )
		{
			// Binary-safe file write.
			$fwrite = is_int( fwrite( $fopen, $fdata ) );
			
			// Closes an open file pointer.
			fclose( $fopen );
		}
		else {
			throw new FileError( $file, FileError::OPEN_ERROR );
		}
		return( $fwrite );
	}
	
	/*
	 * Check if file is writable.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function writable( String $file )
	{
		return( is_writable( Path\Path::path( $file ) ) );
	}
	
}

?>