<?php

namespace rdx\graphql\Schema;

use Carbon\Carbon;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class DateType extends ScalarType {

	public ?string $description = 'String formatted like YYYY-MM-DD';

	public function serialize($value) {
		$format = 'Y-m-d H:i:s';

		if ($value instanceof Carbon) {
			return $value->format($format);
		}

		if (is_numeric($value)) {
			return date($format, $value);
		}

		return $value;
	}

	public function parseValue($value) {
		if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
			throw new Error(sprintf("Invalid date '%s'", $value));
		}

		return $value;
	}

	public function parseLiteral($valueNode, ?array $variables = null) {
		if (!($valueNode instanceof StringValueNode)) {
			throw new Error("Date must be string");
		}

		return $this->parseValue($valueNode->value);
	}

}
