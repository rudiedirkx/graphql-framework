<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\WrappingType;

abstract class ParentObjectType extends ObjectType {

	public function __construct() {
		$config = [];
		$config['fields'] = function() {
			return $this->fields();
		};
		$config['argsMapper'] = function(array $args, FieldDefinition $field) {
			if (count($args)) {
				$objectType = $field->config['type'];
				if ($objectType instanceof WrappingType) {
					$objectType = $objectType->getInnermostType();
				}
				assert($objectType instanceof self or $objectType instanceof ParentInterfaceType);
				return $objectType->args($args);
			}
			return $args;
		};
		$config['interfaces'] = $this->interfaces();
		parent::__construct($config);
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	abstract public function fields() : array;

	/**
	 * @return list<InterfaceType>
	 */
	public function interfaces() : array {
		return [];
	}

	/**
	 * @param list<mixed> $args
	 * @return mixed
	 */
	public function args(array $args) {
		return $args;
	}

}
