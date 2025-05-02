<?php

namespace rdx\graphql;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Validator\QueryValidationContext;
use GraphQL\Validator\Rules\QueryComplexity;

/**
 * @template TContext of GraphQLContext = GraphQLContext
 */
class RememberQueryComplexity extends QueryComplexity {

	/** @var array<string, int> */
	protected array $operationRoots = [];
	/** @var array<string, list<string>> */
	protected array $operationRootNames = [];
	/** @var array<string, int> */
	protected array $operationComplexities = [];

	/**
	 * @param TContext $runtimeContext
	 */
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

			/** @var list<FieldNode> $roots */
			$roots = array_values(iterator_to_array($operationDefinition->selectionSet->selections));
			$this->operationRoots[$operationName] = count($roots);
			$this->operationRootNames[$operationName] = array_map(fn(FieldNode $root) => $root->name->value, $roots);

			$complexity = $this->fieldComplexity($operationDefinition->selectionSet);
			$this->operationComplexities[$operationName] = $complexity;
		};
		return $visitor;
	}

	public function getOperationName() : string {
		if (!$this->operationName && count($this->operationComplexities) == 1) {
			return key($this->operationComplexities);
		}

		return $this->operationName ?? '';
	}

	/**
	 * @return null|list<string>
	 */
	public function getOperationRootNames() : ?array {
		$operationName = $this->getOperationName();
		return $this->operationRootNames[$operationName] ?? null;
	}

	public function getOperationRoots() : ?int {
		$operationName = $this->getOperationName();
		return $this->operationRoots[$operationName] ?? null;
	}

	public function getComplexity() : int {
		$operationName = $this->getOperationName();
		return $this->operationComplexities[$operationName] ?? 0;
	}

}
