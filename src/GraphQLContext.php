<?php

namespace rdx\graphql;

class GraphQLContext {

	/** @var list<string> */
	protected array $warnings = [];

	public function addWarning(string $message) : void {
		if (!in_array($message, $this->warnings)) {
			$this->warnings[] = $message;
		}
	}

	public function getWarnings() : int {
		return count($this->warnings);
	}

	/**
	 * @return array{extensions?: array{warnings: list<array{message: string}>}}
	 */
	public function getExtensions() : array {
		if (count($this->warnings)) {
			return [
				'extensions' => [
					'warnings' => array_map(fn(string $message) => compact('message'), $this->warnings),
				],
			];
		}

		return [];
	}

}
