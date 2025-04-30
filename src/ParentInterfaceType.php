<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use ReflectionClass;

abstract class ParentInterfaceType extends InterfaceType {

	public function __construct() {
		$config = [];
		$config['fields'] = $this->fields();
		$config['resolveType'] = function($object, GraphQLContext $context, ResolveInfo $info) {
			return $this->resolveType($object, $context, $info);
		};
		parent::__construct($config);
	}

	public function resolveType($objectValue, /*GraphQLContext*/ $context, ResolveInfo $info) {
		throw new InvalidArgumentException('Must implement ' . __METHOD__);
	}

	/**
	 * @return array<string, Type|AssocArray>
	 */
	abstract public function fields() : array;

}
