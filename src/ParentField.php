<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use RuntimeException;

abstract class ParentField {

	/**
	 * @return AssocArray
	 */
	static public function buildConfig() : array {
		if (!is_callable([$class = get_called_class(), 'resolve'])) {
			throw new RuntimeException(sprintf('Field class %s is missing a resolve() method.', $class));
		}

		return [
			'type' => static::type(),
			'description' => static::description(),
			'args' => static::args(),
			'argsMapper' => static::argsMapper(...),
			'resolve' => static::resolve(...), // @phpstan-ignore staticMethod.notFound
		];
	}

	abstract static public function type() : Type;

	// static public function resolve(mixed $source, array $args, GraphQLContext $context, ResolveInfo $info) : mixed;

	/**
	 * @param AssocArray $args
	 */
	static public function argsMapper(array $args) : mixed {
		return $args;
	}

	static public function description() : ?string {
		return null;
	}

	static public function deprecationReason() : ?string {
		return null;
	}

	/**
	 * @return array<string, Type|array{type: Type}>
	 */
	static public function args() : array {
		return [];
	}

}
