<?php

namespace rdx\graphql;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Validator\QueryValidationContext;
use GraphQL\Validator\Rules\QueryComplexity;

class RememberQueryComplexity extends QueryComplexity {

	/** @var array<string, int> */
	protected array $operationRoots = [];
	/** @var array<string, string> */
	protected array $operationFirstRootNames = [];
	/** @var array<string, int> */
	protected array $operationComplexities = [];

	public function __construct(
		protected GraphQLContext $runtimeContext,
		protected ?string $operationName,
	) {
		parent::__construct(9999);
	}

	public function getVisitor(QueryValidationContext $context): array {
		$visitor = parent::getVisitor($context);
		$visitor[NodeKind::OPERATION_DEFINITION]['leave'] = function(OperationDefinitionNode $operationDefinition) use ($context) {
			$errors = $context->getErrors();
			if (\count($errors) > 0) {
				return;
			}

			$operationName = $operationDefinition->name->value ?? '';

			$this->operationRoots[$operationName] = count($operationDefinition->selectionSet->selections);
			assert($operationDefinition->selectionSet->selections[0] instanceof FieldNode);
			$this->operationFirstRootNames[$operationName] = $operationDefinition->selectionSet->selections[0]->name->value;

			$complexity = $this->fieldComplexity($operationDefinition->selectionSet);
			$this->operationComplexities[$operationName] = $complexity;
		};
		return $visitor;
	}

	protected function getOperationName() : string {
		if (!$this->operationName && count($this->operationComplexities) == 1) {
			return key($this->operationComplexities);
		}

		return $this->operationName ?? '';
	}

	public function getComplexity() : int {
		$operationName = $this->getOperationName();
		return $this->operationComplexities[$operationName] ?? 0;
	}

	// public function getLog() : string {
	// 	$operationName = $this->getOperationName();
	// 	$firstRootName = $this->operationFirstRootNames[$operationName] ?? '?';
	// 	$roots = $this->operationRoots[$operationName] ?? 0;
	// 	$warnings = $this->runtimeContext->getWarnings();
	// 	return sprintf('%s,%d,%d,%d', $firstRootName, $roots, $this->getComplexity(), $warnings);
	// }

}
