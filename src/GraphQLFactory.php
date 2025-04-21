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
	static public function create(string $class) {
		return self::$types[$class] ??= new $class;
	}

	/**
	 * @template TType of Type
	 * @param class-string<TType> $class
	 * @return ListOfType<TType>
	 */
	static public function listOf(string $class) {
		return Type::nonNull(Type::listOf(Type::nonNull(self::create($class)))); // @phpstan-ignore return.type
	}

}
