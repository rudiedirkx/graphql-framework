<?php

namespace rdx\graphql;

use Closure;
use GraphQL\Deferred;

class DeferredAnything {

	/** @var list<mixed> */
	public array $queue = [];

	/**
	 * @param (Closure(list<mixed>): void) $load
	 * @param (Closure(mixed, GraphQLContext): mixed) $return
	 */
	public function __construct(
		protected Closure $load,
		protected Closure $return,
	) {}

	protected function loadAll() : void {
		if (count($this->queue) == 0) return;

		call_user_func($this->load, $this->queue);

		$this->queue = [];
	}

	/**
	 * @param AssocArray $args
	 */
	public function __invoke(mixed $source, array $args, GraphQLContext $context) : Deferred {
		$this->queue[] = $source;

		return new Deferred(function() use ($source, $context) {
			$this->loadAll();

			return call_user_func($this->return, $source, $context);
		});
	}
}
