<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\InputObjectType;

abstract class ParentInputType extends InputObjectType {

	public function __construct() {
		$config = [];
		$config['fields'] = function() {
			return $this->fields();
		};
		parent::__construct($config);
	}

	/**
	 * @return array<string, AssocArray>
	 */
	abstract public function fields() : array;

	/**
	 * @param AssocArray $args
	 */
	public function argsMapper(array $args) : mixed {
		return $args;
	}

	#[\Override]
	public function parseValue(array $value) {
		return $this->argsMapper($value);
	}

}
