<?php

namespace Yume\Fure\Support\Services;

use Yume\Fure\Support\Design;
use Yume\Fure\Util;

/*
 * ServiceProviderInterface
 *
 * @package Yume\Fure\Support\Services
 */
interface ServiceProviderInterface
{
	
	/*
	 * Service booting.
	 *
	 * @access Public
	 *
	 * @return Void
	 */
	public function booting(): Void;
	
	/*
	 * Register new services.
	 *
	 * @access Public
	 *
	 * @return Void
	 */
	public function register(): Void;
	
}

?>