<?php

namespace rdx\graphql;

use GraphQL\Deferred;

/**
 * @template TModel
 */
abstract class DeferredEager {

	/** @var list<TModel> */
	public array $queue = [];

	public function __construct(
		protected string $field,
	) {}

	protected function loadAll(GraphQLContext $context) : void {
		if (count($this->queue) == 0) return;

		$this->doLoadAll();

		$this->queue = [];
	}

	abstract protected function doLoadAll() : void;

	/**
	 * @param TModel $source
	 * @param list<mixed> $args
	 */
	public function __invoke($source, array $args, GraphQLContext $context) : Deferred {
		$this->queue[] = $source;

		return new Deferred(function() use ($source, $context) {
			$this->loadAll($context);

			return $source->{$this->field};
		});
	}
}
