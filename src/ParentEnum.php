<?php

namespace rdx\graphql;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\Type;

/**
 * @phpstan-import-type PartialEnumValueConfig from \GraphQL\Type\Definition\EnumType
 */
abstract class ParentEnum extends EnumType {

	public function __construct() {
		$config = [];
		$config['values'] = function() {
			return $this->makeValues();
		};
		parent::__construct($config);
	}

	/**
	 * @return array<string, PartialEnumValueConfig>
	 */
	protected function makeValues() : array {
		$values = [];
		foreach ($this->values() as $value => $info) {
			if (is_string($info)) {
				$info = ['description' => $info];
			}
			$name = strtoupper($value);
			$info['value'] ??= $value;
			$values[$name] = $info;
		}

		return $values;
	}

	/**
	 * @return array<string, string|PartialEnumValueConfig>
	 */
	abstract public function values() : array;

}
