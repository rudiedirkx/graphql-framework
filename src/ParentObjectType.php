<?php

namespace rdx\graphql;

use Closure;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

abstract class ParentObjectType extends ObjectType {

	public function __construct() {
		$config = [];
		$config['fields'] = function() {
			return $this->fields();
		};
		$config['interfaces'] = $this->interfaces();
		parent::__construct($config);
	}

	/**
	 * @return array<string, Type|AssocArray|(Closure(): AssocArray)>
	 */
	abstract public function fields() : array;

	/**
	 * @return list<InterfaceType>
	 */
	public function interfaces() : array {
		return [];
	}

}
