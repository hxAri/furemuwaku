<?php

namespace Yume\Fure\Support\Data;

use ArrayAccess;
use Countable;
use Iterator;
use Stringable;

/*
 * DataInterface
 *
 * @package Yume\Fure\Support\Data
 */
interface DataInterface extends ArrayAccess, Countable, Iterator, Stringable {}

?>