<?php

namespace rdx\graphql\Schema;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class NonEmptyStringType extends ScalarType {

	public ?string $description = 'A string that is not "" after trimming white-space.';

	public function serialize($value) {
		return $value;
	}

	public function parseValue($value) {
		if (trim($value) === '') {
			throw new Error("Non-empty string can't be empty 😮");
		}

		return $value;
	}

	public function parseLiteral($valueNode, ?array $variables = null) {
		if (!($valueNode instanceof StringValueNode)) {
			throw new Error("NonEmptyString must be string");
		}

		return $this->parseValue($valueNode->value);
	}

}
