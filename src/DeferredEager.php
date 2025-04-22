<?php

namespace rdx\graphql;

use Closure;
use GraphQL\Deferred;

/**
 * @template TModel
 */
abstract class DeferredEager {

	/** @var list<TModel> */
	public array $queue = [];

	public function __construct(
		protected string $field,
		protected ?Closure $loadEager = null,
	) {}

	protected function loadAll(GraphQLContext $context) : void {
		if (count($this->queue) == 0) return;

		if ($this->loadEager) {
			call_user_func($this->loadEager, $this, $context);
		}
		else {
			$this->doLoadAll();
		}

		$this->queue = [];
	}

	abstract protected function doLoadAll() : void;

	/**
	 * @param list<TModel> $args
	 */
	public function __invoke(mixed $source, array $args, GraphQLContext $context) : Deferred {
		$this->queue[] = $source;

		return new Deferred(function() use ($source, $context) {
			$this->loadAll($context);

			return $source->{$this->field};
		});
	}
}
