<?php

namespace rdx\graphql;

use Closure;

abstract class GraphQLProxy {

	static public function property(string $name) : Closure {
		return function($source) use ($name) {
			return $source->$name;
		};
	}

	static public function nullableProperty(string $name) : Closure {
		return function($source) use ($name) {
			$value = $source->$name;
			return $value === '' ? null : $value;
		};
	}

	/**
	 * @param list<string> $names
	 */
	static public function properties(array $names) : Closure {
		return function($source) use ($names) {
			foreach ($names as $name) {
				if (!is_null($child = $source->$name)) {
					return $child;
				}
			}

			return null;
		};
	}

}
