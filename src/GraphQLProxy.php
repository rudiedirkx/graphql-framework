<?php

namespace rdx\graphql;

use Closure;

abstract class GraphQLProxy {

	static public function property(string $name) : Closure {
		return function($source) use ($name) {
			return $source->$name;
		};
	}

}
