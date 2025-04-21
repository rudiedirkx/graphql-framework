<?php

namespace rdx\graphql\Schema;

use Carbon\Carbon;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class DatetimeType extends ScalarType {

	public ?string $description = 'String formatted like YYYY-MM-DD HH:MM:SS';

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
		return $value;
	}

	public function parseLiteral($valueNode, ?array $variables = null) {
		if ( !($valueNode instanceof StringValueNode) ) {
			throw new Error("Datetime must be string", [$valueNode]);
		}

		return $this->parseValue($valueNode->value);
	}

}
