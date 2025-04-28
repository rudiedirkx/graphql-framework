<?php

namespace rdx\graphql;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Validator\Rules\ValidationRule;
use RuntimeException;

/**
 * @template TContext of GraphQLContext
 * @template TComplexity of RememberQueryComplexity = RememberQueryComplexity
 *
 * @phpstan-type Input array{query: string, variables?: ?AssocArray, operationName?: ?string}
 * @phpstan-type Output array{data: AssocArray, extensions: AssocArray}
 */
abstract class GraphQLHandler {

	/** @var TComplexity */
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

		$_mem1 = memory_get_peak_usage();
		$_time1 = hrtime(true);

		$schema = $this->makeSchema();

		$_mem1 = round((memory_get_peak_usage() - $_mem1) / 1e6, 1);
		$_time1 = round((hrtime(true) - $_time1) / 1e6);

		$_mem2 = memory_get_peak_usage();
		$_time2 = hrtime(true);

		$this->complexity = $this->makeComplexity();

		$addValidationRules = $this->getAddRules();
		$validationRules = [...GraphQL::getStandardValidationRules(), ...$addValidationRules];

		$debug = $this->isDebug();
		$debugFlags = $debug ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : 0;

		$this->result = GraphQL::executeQuery(
			$schema,
			$this->input['query'],
			null,
			$this->context,
			$this->input['variables'] ?? [],
			$this->input['operationName'] ?? null,
			null,
			$validationRules,
		)->toArray($debugFlags);

		$this->result += $this->context->getExtensions();
		$this->result['extensions']['complexity'] = $this->complexity->getComplexity();

		$_mem2 = round((memory_get_peak_usage() - $_mem2) / 1e6, 1);
		$_time2 = round((hrtime(true) - $_time2) / 1e6);

		if ( $debug ) {
			$this->result = compact('_time1', '_mem1', '_time2', '_mem2') + $this->result + [
				'_queries' => $this->getDebugQueries(),
			];
		}
	}

	/**
	 * @return Output
	 */
	public function getResult() : array {
		return $this->result;
	}

	/**
	 * @return TComplexity
	 */
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
	 * @return array<class-string<ValidationRule>, ValidationRule>
	 */
	protected function getAddRules() : array {
		return [
			NotifyAboutDeprecations::class => new NotifyAboutDeprecations($this->context),
			RememberQueryComplexity::class => $this->complexity,
		];
	}

}
