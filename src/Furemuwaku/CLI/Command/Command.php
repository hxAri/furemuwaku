<?php

namespace Yume\Fure\CLI\Command;

use Yume\Fure\CLI\Argument;
use Yume\Fure\Logger;
use Yume\Fure\Support\Reflect;

/*
 * Command
 *
 * @package Yume\Fure\CLI\Command
 */
abstract class Command implements CommandInterface
{
	
	/*
	 * Command abouts/ descriptions.
	 *
	 * @access Protected
	 *
	 * @values Array
	 */
	protected ? String $about = Null;
	
	/*
	 * Command group name.
	 *
	 * @access Protected
	 *
	 * @values String
	 */
	protected String $group = "Yume";
	
	/*
	 * Command name.
	 *
	 * @access Protected
	 *
	 * @values String
	 */
	protected String $name;
	
	/*
	 * Command options.
	 *
	 * @access Protected
	 *
	 * @values Array
	 */
	protected Array $options = [];
	
	/*
	 * Command usage.
	 *
	 * @access Protected
	 *
	 * @values Array|String
	 */
	protected Array | Null | String $usage = Null;
	
	/*
	 * Construct method of class Command.
	 *
	 * @access Public Instance
	 *
	 * @params Protected Readonly Yume\Fure\CLI\Command\Commands $commands
	 * @params Protected Readonly Yume\Fure\Logger\LoggerInterface $logger
	 *
	 * @return Void
	 *
	 * @throws Yume\Fure\CLI\Command\CommandUnitializedNameError
	 */
	public function __construct( protected Readonly Commands $commands, protected Readonly Logger\LoggerInterface $logger )
	{
		if( Reflect\ReflectProperty::isInitialized( $this, "name" ) === False )
		{
			throw new CommandUnitializedNameError( $this::class );
		}
	}
	
	/*
	 * Get command abouts/ descriptions.
	 *
	 * @access Public
	 *
	 * @values Array
	 */
	public function getAbout(): ? String
	{
		return( $this )->about;
	}
	
	/*
	 * Get command group name.
	 *
	 * @access Public
	 *
	 * @values String
	 */
	public function getGroup(): String
	{
		return( $this )->group;
	}
	
	/*
	 * Get command name.
	 *
	 * @access Public
	 *
	 * @return String
	 */
	public function getName(): String
	{
		return( $this )->name;
	}
	
	/*
	 * Get command options.
	 *
	 * @access Public
	 *
	 * @return Array
	 */
	public function getOptions(): Array
	{
		return( $this )->options;
	}
	
	/*
	 * Get command usage.
	 *
	 * @access Public
	 *
	 * @return Array|String
	 */
	public function getUsage(): Array | Null | String
	{
		return( $this )->usage;
	}
	
	/*
	 * @inherit Yume\Fure\CLI\Command\CommandInterface::run
	 *
	 */
	abstract public function run( Argument\Argument $args ): Void;
	
}

?>