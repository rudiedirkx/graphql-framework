<?php

namespace rdx\graphql;

use Closure;
use GraphQL\Deferred;

class DeferredAnythingWithArgs {

	/** @var array<string, DeferringContext> */
	protected array $queues = [];
	/** @var array<string, DeferringContext> */
	protected array $results = [];

	/**
	 * @param (Closure(DeferringContext): void) $load
	 * @param (Closure(mixed, DeferringContext, GraphQLContext): mixed) $return
	 */
	public function __construct(
		protected Closure $load,
		protected Closure $return,
	) {}

	protected function loadAll() : void {
		if (count($this->queues) == 0) return;

		$this->results = $this->queues;

		foreach ($this->queues as $deferring) {
			call_user_func($this->load, $deferring);
		}

		$this->queues = [];
	}

	/**
	 * @param AssocArray $args
	 */
	public function __invoke(mixed $source, array $args, GraphQLContext $context) : Deferred {
		$key = json_encode($args);
		$this->queues[$key] ??= new DeferringContext($args);
		$this->queues[$key]->queue[] = $source;

		return new Deferred(function() use ($key, $source, $context) {
			$this->loadAll();

			return call_user_func($this->return, $source, $this->results[$key], $context);
		});
	}
}
