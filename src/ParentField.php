<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use RuntimeException;

abstract class ParentField {

	/**
	 * @return AssocArray
	 */
	public function buildConfig() : array {
		if (!is_callable([$this, 'resolve'])) {
			throw new RuntimeException(sprintf('Field class %s is missing a resolve() method.', get_class($this)));
		}

		return [
			'type' => $this->type(),
			'description' => $this->description(),
			'args' => $this->args(),
			'argsMapper' => $this->argsMapper(...),
			'resolve' => $this->resolve(...), // @phpstan-ignore callable.nonNativeMethod
		];
	}

	abstract public function type() : Type;

	// public function resolve(mixed $source, array $args, GraphQLContext $context, ResolveInfo $info) : mixed;

	/**
	 * @param AssocArray $args
	 */
	public function argsMapper(array $args) : mixed {
		return $args;
	}

	public function description() : ?string {
		return null;
	}

	public function deprecationReason() : ?string {
		return null;
	}

	/**
	 * @return array<string, Type|array{type: Type}>
	 */
	public function args() : array {
		return [];
	}

}
