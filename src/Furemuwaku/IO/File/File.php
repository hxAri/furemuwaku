<?php

namespace Yume\Fure\IO\File;

use DateTime;

use Yume\Fure\Error;
use Yume\Fure\IO;
use Yume\Fure\Util;

/*
 * File
 *
 * @package Yume\Fure\IO\File
 */
abstract class File
{
	
	public const SKIP_EMPTY_LINE = 7829;
	
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
		"w",
		"w+",
		"a",
		"a+",
		"x",
		"x+",
		"c",
		"c+",
		"e"
	];
	
	private static function assertMode( String $file, String $mode ): Void
	{
		try
		{
			// If file open mode is invalid mode.
			if( in_array( $mode, self::$modes ) === False )
			{
				throw new Error\AssertError( [ "mode", self::$modes, $mode ], Error\AssertError::VALUE_ERROR );
			}
		}
		catch( Error\AssertionError $e )
		{
			throw new Error\FileError( [ $file, $mode ], Error\FileError::MODE_ERROR, $e );
		}
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
		return( is_file( path( $file ) ) );
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
	 */
	public static function open( String $file, String $mode = "r", Bool $include = False, $context = Null )
	{
		// File mode assertion.
		self::assertMode( $file, $mode );
		
		// Check if the filename is not a directory.
		if( IO\Path\Path::exists( $file ) === False )
		{
			// Check if such a directory exists.
			if( IO\Path\Path::exists( $fpath = Util\Str::pop( $file, "/" ) ) )
			{
				return( fopen( $file, $mode, $include, $context ) );
			}
			throw new Error\FileError( $fpath, Error\FileError::PATH_ERROR );
		}
		throw new Error\FileError( $file, Error\FileError::TYPE_ERROR );
	}
	
	/*
	 * Read the contents of the file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return String
	 */
	public static function read( String $file ): String
	{
		// Check if such directory is unreadable.
		if( IO\IO::readable( Util\Str::pop( $file, "/" ) ) === False )
		{
			throw new Error\PathError( $fpath, Error\PathError::READ_ERROR );
		}
		// Check if such a file exists.
		if( self::exists( $file ) )
		{
			// Check if such files are readable.
			if( IO\IO::readable( $file ) )
			{
				// Add prefix base path.
				$fname = path( $file );
				
				// Get file size.
				$fsize = ( $fsize = self::size( $file ) ) === 0 ? 13421779 : $fsize;
				
				// Open file.
				$fopen = self::open( $fname, "r" );
				
				// File readed.
				$fread = "";
				
				// Binary-safe file read.
				while( feof( $fopen ) === False )
				{
					$fread .= fread( $fopen, $fsize );
				}
				
				// Closes an open file pointer.
				fclose( $fopen );
				
				return( $fread );
			}
			throw new Error\FileError( $file, Error\FileError::READ_ERROR );
		}
		else {
			throw new Error\FileError( $file, Error\FileError::FILE_ERROR );
		}
	}
	
	/*
	 * Read file contents and split file contents with endline.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 * @params Int $flags
	 *
	 * @return Array
	 */
	public static function readline( String $file, Int $flags = 0 ): Array
	{
		// Reading file contents.
		$fread = self::read( $file );
		
		// Split file contents with end line.
		$fline = explode( "\n", $fread );
		
		switch( $flags )
		{
			// Remove or skip blank lines.
			case self::SKIP_EMPTY_LINE:
			
				// Mapping Lines.
				foreach( $fline As $i => $line )
				{
					// Check if the line is empty.
					if( $line === "" )
					{
						// Destroy the line.
						unset( $fline[$i] );
					}
				}
				break;
		}
		
		return( $fline );
	}
	
	/*
	 * Get file size.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return String|Int
	 */
	public static function size( String $file ): Int | String
	{
		return( filesize( path( $file ) ) );
	}
	
	/*
	 * Get DateTime class instance from file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return DateTime
	 */
	public static function time( String $file ): DateTime
	{
		// Get timestamp value from file.
		$time = filemtime( path( $file ) );
		
		// Create new instance of DateTime class.
		$date = new DateTime;
		$date->setTimestamp( $time );
		
		// Return DateTime instance.
		return( $date );
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
		return( unlink( path( $file ) ) );
	}
	
	/*
	 * Write or create a new file.
	 *
	 * @access Public Static
	 *
	 * @params String $file
	 *
	 * @return Bool
	 */
	public static function write( String $file, ? String $fdata = Null, String $fmode = "w" ): Void
	{
		// Check if the filename is a directory.
		if( IO\Path\Path::exists( $file ) )
		{
			throw new FileError( $file, FileError::NOT_FILE );
		}
		
		// Check if such a directory exists.
		if( IO\Path\Path::exists( $fpath = Util\Str::pop( $file, "/" ) ) === False )
		{
			IO\Path\Path::mkdir( $fpath );
		}
		
		// Check if such directory is unwriteable.
		if( IO\IO::writeable( $fpath ) === False )
		{
			throw new Error\PermissionError( $fpath, Error\PermissionError::WRITE_ERROR );
		}
		
		// Check if such a file exists.
		if( self::exists( $file ) )
		{
			// Check if such files are unwriteable.
			if( IO\IO::writeable( $file ) === False )
			{
				throw new Error\PermissionError( $file, Error\PermissionError::WRITE_ERROR );
			}
		}
		
		// Add prefix base path.
		$fname = path( $file );
		
		// File contents.
		$fdata = $fdata ? $fdata : "";
		
		// Open file.
		if( $fopen = fopen( $fname, $fmode ) )
		{
			// Binary-safe file write.
			fwrite( $fopen, $fdata );
			
			// Closes an open file pointer.
			fclose( $fopen );
		}
		else {
			throw new IOError( $fname, Error\IOError );
		}
	}

}

?>