<?php

namespace rdx\graphql;

use Closure;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;

abstract class GraphQLFactory {

	/** @var array<class-string<Type>, Type> */
	static protected array $types = [];

	/** @var array<class-string<Type>, float> */
	static public array $typeTimings = [];

	/** @var array<class-string, ParentField> */
	static protected array $fields = [];

	/**
	 * @template TType of Type
	 * @param class-string<TType>&class-string<NamedType> $class
	 * @return TType&NamedType
	 */
	static public function type(string $class) : Type {
		if (isset(self::$types[$class])) return self::$types[$class]; // @phpstan-ignore return.type

		$t = hrtime(true);
		self::$types[$class] = new $class;
		$t = hrtime(true) - $t;
		self::$typeTimings[$class] = $t / 1e6;

		return self::$types[$class];
	}

	/**
	 * @param class-string<ParentField>|ParentField $field
	 * @return (Closure(): AssocArray)
	 */
	static public function field(string|ParentField $field) : Closure {
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
