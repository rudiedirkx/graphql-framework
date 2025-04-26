<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;

abstract class GraphQLFactory {

	/** @var array<class-string, Type> */
	static protected array $types = [];

	/** @var array<class-string, ParentField> */
	static protected array $fields = [];

	/**
	 * @template TType of Type
	 * @param class-string<TType> $class
	 * @return TType
	 */
	static public function type(string $class) : Type {
		return self::$types[$class] ??= new $class;
	}

	/**
	 * @param class-string<ParentField>|ParentField $field
	 * @return AssocArray
	 */
	static public function field(string|ParentField $field) : array {
		if (is_string($field)) {
			self::$fields[$field] ??= new $field;
			$field = self::$fields[$field];
		}

		return $field->buildConfig();
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
