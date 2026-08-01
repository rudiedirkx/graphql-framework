<?php

namespace rdx\graphql;

use GraphQL\Error\Error;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Validator\Rules\ValidationRule;
use GraphQL\Validator\ValidationContext;

class FieldAccessRule extends ValidationRule {

	public function getVisitor(ValidationContext $context): array {
		return [
			NodeKind::FIELD => function (FieldNode $node) use ($context) {
				$fieldDef = $context->getFieldDef();
				if (!$fieldDef) {
					return;
				}

				$access = $fieldDef->config['access'] ?? true;
				if ($access === true) {
					return;
				}

				$parentType = $context->getParentType();
				$typeName = $parentType ? $parentType->name : 'Unknown';

				$fieldName = $node->name->value;

				$schema = $context->getSchema();
				$isMutation = $parentType === $schema->getMutationType();
				$fieldLabel = $isMutation ? "mutation $fieldName" : "field $typeName.$fieldName";

				$context->reportError(new Error("You don't have access to $fieldLabel!", [$node]));
			}
		];
	}
}
