<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;

abstract class GraphQLFactory {

	/** @var array<class-string<Type>, Type> */
	static protected array $types = [];

	/** @var array<class-string, ParentField> */
	static protected array $fields = [];

	/**
	 * @template TType of Type
	 * @param class-string<TType>&class-string<NamedType> $class
	 * @return TType&NamedType
	 */
	static public function type(string $class) : Type {
		return self::$types[$class] ??= new $class; // @phpstan-ignore return.type
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
	 * @param (TType&NamedType)|(class-string<TType>&class-string<NamedType>) $type
	 * @return ListOfType<TType>
	 */
	static public function listOf(string|Type $type) {
		if (is_string($type)) {
			$type = static::type($type);
		}

		return Type::nonNull(Type::listOf(Type::nonNull($type))); // @phpstan-ignore return.type
	}

}
