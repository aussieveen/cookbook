# JSON API

## Summary

A versioned JSON REST API at `/api/v1` for reading recipes and generating shopping lists.
Interactive OpenAPI documentation is available at `/api/doc`.

## Why it matters

Allows external applications (e.g. a meal-planner UI) to search and display recipes and generate consolidated shopping lists without coupling to the admin interface.

## How it works

All endpoints return and accept `application/json`.
CORS is configured via Nelmio CORS Bundle.

### Endpoints

#### Recipes

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/v1/recipes` | List and search recipes |
| `GET` | `/api/v1/recipes/:id` | Get a single recipe with full detail |
| `GET` | `/api/v1/recipes/:id/suggested-sides` | Get suggested sides for a recipe |
| `PATCH` | `/api/v1/recipes/:id/favourite` | Toggle favourite flag |

#### Shopping list

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/v1/shopping-list` | Consolidated ingredient list for a set of recipes |

### Recipe list — `GET /api/v1/recipes`

All query parameters are optional and combinable.

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Partial match on recipe name |
| `ingredients[]` | string (repeatable) | Partial match on ingredient name |
| `meal_occasion` | string | One of: `breakfast`, `lunch`, `dinner`, `baked_goods`, `staple` |
| `course` | string | One of: `starter`, `main`, `side`, `dessert` |
| `exclude_ids[]` | integer (repeatable) | Exclude recipes by ID |

Returns an array of recipe summaries with fields: `id`, `name`, `slug`, `course`, `mealOccasions`, `mastered`, `favourite`, `image`.

### Recipe detail — `GET /api/v1/recipes/:id`

Returns full recipe detail including `components` (with `ingredients`), `steps`, `pairings`, and all summary fields.

### Suggested sides — `GET /api/v1/recipes/:id/suggested-sides`

Returns explicit pairings filtered to `course=side`.
Falls back to all side-course recipes if the recipe has no explicit pairings.

### Toggle favourite — `PATCH /api/v1/recipes/:id/favourite`

Toggles the `favourite` boolean and returns a JSON object with key `favourite` set to `true` or `false`.

### Shopping list — `POST /api/v1/shopping-list`

Request body:

```
recipeIds: [1, 2, 3]
```

Returns a consolidated ingredient list sorted alphabetically, for example:

```
id: 42, name: "Flour", category: "store_cupboard", display: "500g"
```

Unknown recipe IDs are silently ignored.

## Where to start

- OpenAPI docs: `/api/doc`
- Recipe API controller: `app/src/Controller/Api/RecipeApiController.php`
- Shopping list API controller: `app/src/Controller/Api/ShoppingListApiController.php`
- CORS config: `app/config/packages/nelmio_cors.yaml`

## Dependencies

- **Nelmio API Doc Bundle** — OpenAPI documentation
- **Nelmio CORS Bundle** — CORS headers
- **Symfony Serializer** with groups `recipe:summary` and `recipe:detail`
- **`ShoppingListService`** — see [Shopping list](shopping-list.md)

---

<details>
<summary>Source Map</summary>

- [app/src/Controller/Api/RecipeApiController.php](../../app/src/Controller/Api/RecipeApiController.php) — recipe endpoints
- [app/src/Controller/Api/ShoppingListApiController.php](../../app/src/Controller/Api/ShoppingListApiController.php) — shopping list endpoint
- [app/src/Serializer/RecipeNormalizer.php](../../app/src/Serializer/RecipeNormalizer.php) — custom recipe serialisation
- [app/config/packages/nelmio_cors.yaml](../../app/config/packages/nelmio_cors.yaml) — CORS configuration

</details>

---

[Back to Features](README.md) | [Documentation Home](../README.md)
