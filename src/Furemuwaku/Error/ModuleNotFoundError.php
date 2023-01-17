<?php

namespace Yume\Fure\Error;

use Throwable;

/*
 * ModuleNotFoundError
 *
 * @package Yume\Fure\Error
 *
 * @extends Yume\Fure\Error\ModuleError
 */
class ModuleNotFoundError extends ModuleError
{
	/*
	 * @inherit Yume\Fure\Error\ModuleError
	 *
	 */
	public function __construct( Array | Int | String $message, Int $code = parent::NOT_FOUND_ERROR, ? Throwable $previous = Null )
	{
		parent::__construct( ...func_get_args() );
	}
}

?>