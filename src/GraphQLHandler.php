<?php

namespace rdx\graphql;

use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Validator\Rules\ValidationRule;
use ReflectionClass;
use RuntimeException;

/**
 * @template TContext of GraphQLContext
 *
 * @phpstan-type Input array{query: string, variables?: ?AssocArray, operationName?: ?string}
 * @phpstan-type Output array{errors?: list<AssocArray>, data?: AssocArray, extensions: AssocArray}
 */
abstract class GraphQLHandler {

	public RememberQueryComplexity $complexity;

	/** @var Output */
	protected array $result;

	/**
	 * @param TContext $context
	 * @param ?Input $input
	 */
	public function __construct(
		protected GraphQLContext $context,
		protected ?array $input = null,
	) {}

	public function execute() : void {
		$this->input ??= $this->makeInput();

		$_mem = memory_get_peak_usage();
		$_totalTime = hrtime(true);

		$schema = $this->makeSchema();

		$this->complexity = $this->makeComplexity();

		$addValidationRules = $this->getAddRules();
		$validationRules = [...GraphQL::getStandardValidationRules(), ...$addValidationRules];

		$debug = $this->isDebug();
		$debugFlags = $debug ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : 0;

		$executionResult = GraphQL::executeQuery(
			schema: $schema,
			source: $this->input['query'],
			contextValue: $this->context,
			variableValues: $this->input['variables'] ?? [],
			operationName: $this->input['operationName'] ?? null,
			validationRules: $validationRules,
		);

		$result = $executionResult->toArray($debugFlags);
		$this->result = [
			'extensions' => $this->context->getExtensions() + [
				'complexity' => $this->complexity->getComplexity(),
			],
		] + $result;

		$_mem = round((memory_get_peak_usage() - $_mem) / 1e6, 1);
		$_totalTime = round((hrtime(true) - $_totalTime) / 1e6);

		$serverErrors = array_values(array_filter($executionResult->errors, function(Error $ex) {
			return !$ex->isClientSafe();
		}));

		if ( $debug ) {
// $reflClass = new ReflectionClass($schema);
// $reflProperty = $reflClass->getProperty('resolvedTypes');
// $loadedTypes = $reflProperty->getValue($schema);
// dump(array_keys($loadedTypes));
// dump(array_keys(GraphQLFactory::$typeTimings));

			$_types = count(GraphQLFactory::$typeTimings);
			$_typesTime = array_sum(GraphQLFactory::$typeTimings);

			$this->result = compact('_totalTime', '_typesTime', '_mem', '_types') + $this->result + [
				'_queries' => $this->getDebugQueries(),
			];
		}

		if (!$debug && count($serverErrors)) {
			$this->handleExceptions($serverErrors);
		}
	}

	/**
	 * @return Output
	 */
	public function getResult() : array {
		return $this->result;
	}

	protected function getNumErrors() : int {
		return count($this->result['errors'] ?? []);
	}

	protected function getNumWarnings() : int {
		return count($this->result['extensions']['warnings'] ?? []);
	}

	protected function makeComplexity() : RememberQueryComplexity {
		return new RememberQueryComplexity($this->context, $this->input['operationName'] ?? null);
	}

	/**
	 * @return Input
	 */
	protected function makeInput() : array {
		$json = $this->getInputJson();

		if (!$json) {
			$this->logInputError('ERR 1', $json);
			throw new RuntimeException("Empty body");
		}

		$input = @json_decode($json, true);
		if (!is_array($input)) {
			$this->logInputError('ERR 2', $json);
			throw new RuntimeException("Invalid JSON body");
		}

		if (empty($input['query'])) {
			$this->logInputError('ERR 3', $json);
			throw new RuntimeException("Missing 'query' in JSON body");
		}

		$this->logInputQuery('OK', $input);

		return $input;
	}

	protected function getInputJson() : string {
		return trim(file_get_contents('php://input'));
	}

	abstract public function makeSchema() : Schema;

	abstract protected function isDebug() : bool;

	abstract protected function logInputError(string $label, string $input) : void;

	/**
	 * @param Input $input
	 */
	abstract protected function logInputQuery(string $label, array $input) : void;

	/**
	 * @return list<string>
	 */
	abstract protected function getDebugQueries() : array;

	/**
	 * @param non-empty-list<Error> $errors
	 */
	protected function handleExceptions(array $errors) : void {
		// Throw for real, or log, or ignore
	}

	/**
	 * @return array<class-string<ValidationRule>, ValidationRule>
	 */
	protected function getAddRules() : array {
		return [
			NotifyAboutDeprecations::class => new NotifyAboutDeprecations($this->context),
			RememberQueryComplexity::class => $this->complexity,
		];
	}

}
