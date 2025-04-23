<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;

abstract class GraphQLFactory {

	/** @var array<class-string, Type> */
	static protected array $types = [];

	/**
	 * @template TType of Type
	 * @param class-string<TType> $class
	 * @return TType
	 */
	static public function type(string $class) : Type {
		return self::$types[$class] ??= new $class;
	}

	/**
	 * @param class-string<ParentField> $class
	 * @return AssocArray
	 */
	static public function field(string $class) : array {
		return $class::buildConfig();
	}

	/**
	 * @template TType of Type
	 * @param TType|class-string<TType> $type
	 * @return ListOfType<TType>
	 */
	static public function listOf(string|Type $type) {
		if (is_string($type)) {
			$type = static::type($type);
		}

		return Type::nonNull(Type::listOf(Type::nonNull($type))); // @phpstan-ignore return.type
	}

}
