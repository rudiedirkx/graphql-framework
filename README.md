This is a simple way to make `webonyx/graphql-php` more readable.

## Set-up

You need to make a handler class:

```php
class MyHandler extends GraphQLHandler {

	public function makeSchema() : Schema {
		return new \GraphQL\Type\Schema([
			'query' => GraphQLFactory::type(MyQueryType::class),
			'mutation' => GraphQLFactory::type(MyMutationType::class),
		]);
	}

	protected function isDebug() : bool {
		// Whether you want extra debug in your GraphQL response (next to `data` + `extensions`)
		return app()->ip() == '127.0.0.1';
	}

	protected function logInputError(string $label, string $input) : void {
		// Client sent invalid JSON: $input
	}

	protected function logInputQuery(string $label, array $input) : void {
		// Client sent correct JSON: $input
	}

	protected function getDebugQueries() : array {
		// If you want the response to contain runtime db queries
		return app()->queries();
	}

}
```

And if you want eager loading (you do), you need to define your framework/app's strategy:

```php
class MyEager extends DeferredEager {

	protected function doLoadAll() : void {
		// Laravel:
		ModelCollection::make($this->queue)->load($this->field);

		// Other framework:
		$class = get_class($this->queue[0]);
		call_user_func([$class, 'eager'], $this->queue, $this->field);
	}

}
```

## Controller

And then you can call it in the controller:

```php
$context = new GraphQLContext();
$handler = new MyHandler($context);
$handler->execute();

return new JsonResponse($handler->getResult());
```

## Types

GraphQL is about types, and `webonyx/graphql-php` is not very user-friendly by default, so this
is how you make types. It does help if you know how webonyx works.

Everything starts with `MyQueryType` from `MyHandler`:

```php
class MyQueryType extends ParentObjectType {

	public function fields() : array {
		$fields = [];

		// Simple field:
		$fields['user'] = [
			'type' => GraphQLFactory::type(UserType::class),
			'resolve' => function() {
				return app()->user();
			},
		];

		// Complex field:
		$fields['users'] = GraphQLFactory::field(MyQueryUsersField::class);

		return $fields;
	}

}

class MyQueryUsersField extends ParentField {

	static public function type() : Type {
		return GraphQLFactory::type(UsersPagerType::class);
	}

	static public function args() : array {
		// All of these are optional:
		return [
			'ids' => Type::listOf(Type::nonNull(Type::int())),
			'organization' => Type::int(),
			'emails' => Type::listOf(Type::nonNull(Type::string())),
			'changed_after' => Type::int(),
			'status' => Type::int(),
		];
	}

	static public function argsMapper(array $args) : mixed {
		// If you want the above args to be a nice DTO, use argsMapper()
		return new UsersPagerArgs(...$args);
	}

	/**
	 * @return AssocArray
	 */
	static public function resolve(null $source, UsersPagerArgs $args, BrContext $context) : array {
		// $source is null, because this is a root field. Deeper fields have a mixed or YourDbalObject etc $source.

		// Use $args to make cool query
		$query = ...;

		// Return fields according to `UsersPagerType`.
		// Use fn() to lazy load: only execute if queried.
		return [
			'total' => fn() => $query->getCount(),
			'nodes' => fn() => $query->getRecords(),
		];
	}

}

class UsersPagerArgs {

	public function __construct(
		/** @var list<int> */
		public array $ids = [],
		public int $organization = 0,
		/** @var list<string> */
		public array $emails = [],
		public int $changed_after = 0,
		public int $status = 0,
	) {}

}
```

`UserType` and `UsersPagerType` are `ParentObjectType` again, with the same logic. Because `UserType`
is so much simpler, another example:

```php
class UserType extends ParentObjectType {

	public function fields() : array {
		// No resolvers, so webonyx's default resolver is used.
		return [
			'id' => Type::nonNull(Type::int()),
			'name' => Type::nonNull(Type::string()),
			'email' => Type::nonNull(Type::string()),
			'status' => Type::nonNull(Type::int()),
			'fav_color' => Type::string(),
		];
	}

}
```

`MyMutationType` works exactly the same as Query types.
