# Recipe Management

## Summary

The core of the application.
Recipes are stored with rich structured data and managed through an EasyAdmin admin interface.
Recipes created by AI parsing enter a **Pending Approval** workflow and are only visible to end users once approved.

## Why it matters

Provides the canonical data store for all recipes, driving both the admin interface and the JSON API consumed by external tools such as a meal-planner UI.

## How it works

Each recipe can have:

- **Name** and **slug** (unique)
- **Description**
- **Course** — `starter`, `main`, `side`, or `dessert`
- **Meal occasions** — one or more of `breakfast`, `lunch`, `dinner`, `baked_goods`, `staple`
- **Components** — named or unnamed groups of ingredients (e.g. "For the sauce")
- **Ingredients** — each with a measurement, optional revised measurement, and optional note
- **Steps** — ordered cooking instructions
- **Image** — stored on AWS S3
- **Pairings** — many-to-many links to other recipes (used for suggested sides)
- **Flags** — `mastered`, `favourite`, `needsApproval`

Recipes with `needsApproval = true` are in **Pending Approval** status.
They are saved by the AI parsing pipeline and must be reviewed and approved in the admin interface before they appear in API responses.

The admin interface is powered by EasyAdmin and provides CRUD for recipes, ingredients, ingredient names, components, steps, and mistakes.
Ingredient names are normalised as a separate entity so that the same ingredient across recipes can be tracked, merged, and categorised.

## Where to start

- Admin interface: `/admin`
- EasyAdmin dashboard: `app/src/Controller/Admin/DashboardController.php`
- Recipe entity: `app/src/Entity/Recipe.php`
- Recipe repository with search: `app/src/Repository/RecipeRepository.php`

## Dependencies

- **Doctrine ORM** — persistence
- **EasyAdmin** — admin interface
- **AWS S3 / Flysystem** — image storage (`app/src/Service/ImageUploader.php`, `app/src/Service/StorageUrlResolver.php`)
- **Symfony Serializer** — API serialisation groups (`recipe:summary`, `recipe:detail`)

---

<details>
<summary>Source Map</summary>

- [app/src/Entity/Recipe.php](../../app/src/Entity/Recipe.php) — recipe entity and serialisation groups
- [app/src/Entity/Component.php](../../app/src/Entity/Component.php) — named ingredient groups
- [app/src/Entity/Ingredient.php](../../app/src/Entity/Ingredient.php) — ingredient with measurement and note
- [app/src/Entity/IngredientName.php](../../app/src/Entity/IngredientName.php) — normalised ingredient name entity
- [app/src/Controller/Admin/DashboardController.php](../../app/src/Controller/Admin/DashboardController.php) — EasyAdmin entrypoint
- [app/src/Controller/Admin/RecipeCrudController.php](../../app/src/Controller/Admin/RecipeCrudController.php) — recipe CRUD admin controller
- [app/src/Repository/RecipeRepository.php](../../app/src/Repository/RecipeRepository.php) — recipe search and retrieval

</details>

---

[Back to Features](README.md) | [Documentation Home](../README.md)
