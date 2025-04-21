<?php

namespace rdx\graphql;

use GraphQL\Error\Error;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\QueryValidationContext;
use GraphQL\Validator\Rules\ValidationRule;

class ArgumentMustBeVariable extends ValidationRule {

	public function getVisitor(QueryValidationContext $context): array {
		return [ // @phpstan-ignore return.type
			NodeKind::ARGUMENT => function(ArgumentNode $node) use ($context) {
				if ($node->value->kind == NodeKind::VARIABLE) return;

				$fieldDef = $context->getFieldDef();
				if (!$fieldDef) return;

				$argDef = $fieldDef->getArg($node->name->value);
				if (empty($argDef->config[__CLASS__])) return;

				$parentType = $context->getParentType();
				if (!($parentType instanceof ObjectType)) return;

				$name = sprintf('%s.%s.%s', $parentType->name, $fieldDef->name, $node->name->value);
				$context->reportError(new Error(sprintf("Argument '%s' must be a Variable, not an inline value.", $name)));
			},
		];
	}

}
