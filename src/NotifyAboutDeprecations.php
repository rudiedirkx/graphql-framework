<?php

namespace rdx\graphql;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\VariableDefinitionNode;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Validator\QueryValidationContext;
use GraphQL\Validator\Rules\ValidationRule;

class NotifyAboutDeprecations extends ValidationRule {

	public function __construct(
		protected GraphQLContext $context,
	) {}

	public function getVisitor(QueryValidationContext $context): array {
		$visitors = [];
		$visitors[NodeKind::FIELD] = function(FieldNode $node) use ($context) {
			$field = $context->getFieldDef();
			if ($field && $field->deprecationReason) {
				$parent = $context->getParentType();
				assert($parent instanceof ObjectType);
				$this->context->addWarning(sprintf("Field %s.%s is deprecated: %s", $parent->name, $field->name, $field->deprecationReason));
			}
		};
		$visitors[NodeKind::ARGUMENT] = function(ArgumentNode $node) use ($context) {
			$argument = $context->getArgument();
			$field = $context->getFieldDef();
			if ($argument && $field && $argument->deprecationReason) {
				$parent = $context->getParentType();
				assert($parent instanceof ObjectType);
				$this->context->addWarning(sprintf("Argument %s.%s.%s is deprecated: %s", $parent->name, $field->name, $argument->name, $argument->deprecationReason));
			}
		};
		// $visitors[NodeKind::VARIABLE_DEFINITION] = function(VariableDefinitionNode $node) use ($context) {
		// 	$type = $context->getInputType();
		// 	if ($type instanceof WrappingType) $type = $type->getInnermostType();
		// 	if ($type instanceof EnumType) {
		// 		$varName = $node->variable->name;
		// 		// @todo Get variable value, and its enum value
		// 		// $varValue = ??
		// 		// $enumValue = $type->getValue($varValue);
		// 		// if ($enumValue->deprecationReason)
		// 	}
		// };
		$visitors[NodeKind::ENUM] = function(EnumValueNode $node) use ($context) {
			$argument = $context->getArgument();
			if (!$argument) return;
			$argumentType = $argument->getType();
			if ($argumentType instanceof WrappingType) {
				$argumentType = $argumentType->getInnermostType();
			}
			if (!($argumentType instanceof EnumType)) return;
			$enumValue = $argumentType->getValue($node->value);
			if ($enumValue && $enumValue->deprecationReason) {
				$this->context->addWarning(sprintf("Enum value %s.%s is deprecated: %s", $argumentType->name, $enumValue->name, $enumValue->deprecationReason));
			}
		};
		return $visitors; // @phpstan-ignore return.type
	}

}
