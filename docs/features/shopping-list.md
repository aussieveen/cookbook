# Shopping List

## Summary

Generate a consolidated ingredient list from a set of recipes.
Quantities for the same ingredient across recipes are summed and units are normalised.

## Why it matters

Makes it easy for a meal-planner UI or other consumer to produce a single shopping list for a week's worth of recipes without manually combining ingredient lists.

## How it works

1. A consumer POSTs a list of recipe IDs to `POST /api/v1/shopping-list`.
2. `ShoppingListService::consolidate()` loads the recipes and iterates their ingredients.
3. Ingredients are grouped by normalised `IngredientName`.
4. Quantities are converted to a common base unit (e.g. grams, millilitres) using `UnitConverterService`.
5. The consolidated list is returned sorted alphabetically by ingredient name, with `id`, `name`, `category`, and a human-readable `display` string (e.g. `500g`).

Unknown recipe IDs are silently ignored.

## Where to start

- API endpoint: `POST /api/v1/shopping-list` (see [JSON API](api.md))
- Service: `app/src/Service/ShoppingListService.php`
- Unit conversion: `app/src/Service/UnitConverterService.php`

## Dependencies

- **`ShoppingListService`** — consolidation logic
- **`UnitConverterService`** — unit normalisation
- **`IngredientName`** entity — normalised ingredient grouping key

---

<details>
<summary>Source Map</summary>

- [app/src/Service/ShoppingListService.php](../../app/src/Service/ShoppingListService.php) — consolidation and sorting
- [app/src/Service/UnitConverterService.php](../../app/src/Service/UnitConverterService.php) — unit conversion and base quantity calculation
- [app/src/Controller/Api/ShoppingListApiController.php](../../app/src/Controller/Api/ShoppingListApiController.php) — API controller
- [app/src/Entity/IngredientName.php](../../app/src/Entity/IngredientName.php) — normalised ingredient name with category

</details>

---

[Back to Features](README.md) | [Documentation Home](../README.md)
