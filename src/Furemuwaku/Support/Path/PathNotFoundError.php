<?php

namespace Yume\Fure\Support\Path;

use Throwable;

/*
 * PathNotFoundError
 *
 * @package Yume\Fure\Support\Path
 *
 * @extends Yume\Fure\Support\Path\PathError
 */
class PathNotFoundError extends PathError
{
	/*
	 * @inherit Yume\Fure\Support\Path\PathError
	 *
	 */
	public function __construct( Array | Int | String $message, Int $code = parent::NOT_FOUND_ERROR, ? Throwable $previous = Null )
	{
		parent::__construct( ...func_get_args() );
	}
}

?>