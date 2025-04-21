<?php

namespace rdx\graphql;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\QueryValidationContext;
use GraphQL\Validator\Rules\ValidationRule;

class NotifyAboutDeprecations extends ValidationRule {

	public function __construct(
		protected GraphQLContext $context,
	) {}

	public function getVisitor(QueryValidationContext $context): array {
		return [ // @phpstan-ignore return.type
			NodeKind::FIELD => function(FieldNode $node) use ($context) {
				$field = $context->getFieldDef();
				if ($field && $field->deprecationReason) {
					$parent = $context->getParentType();
					assert($parent instanceof ObjectType);
					$this->context->addWarning(sprintf("%s.%s is deprecated: %s", $parent->name, $field->name, $field->deprecationReason));
				}
			},
		];
	}

}
