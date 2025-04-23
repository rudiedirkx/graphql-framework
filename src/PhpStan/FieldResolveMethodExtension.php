<?php

namespace rdx\graphql\PhpStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use rdx\graphql\ParentField;

class FieldResolveMethodExtension implements MethodsClassReflectionExtension {

	public function hasMethod(ClassReflection $classReflection, string $methodName) : bool {
		return dump($classReflection->is(ParentField::class) && $methodName === 'resolve');
	}

	public function getMethod(ClassReflection $classReflection, string $methodName) : MethodReflection {
dd($classReflection->getName(), $methodName);
	}

}
