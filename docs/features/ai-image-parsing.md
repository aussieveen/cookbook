# AI Image Parsing

## Summary

Upload one or more photos of a recipe (e.g. a photograph of a cookbook page) and Claude (`claude-sonnet-4-6`) will extract the structured recipe data automatically.
The parsed recipe is saved with **Pending Approval** status so you can review it before it appears in the API.

## Why it matters

Lets you digitise physical recipe books quickly without manually entering every ingredient and step.

## How it works

1. Navigate to `/admin/parse-images` and upload one or more JPEG/PNG images.
2. The images are dispatched as a `ParseRecipeImagesMessage` to the Symfony Messenger async queue.
3. The worker container picks up the message and calls `AiRecipeParser::parse()`.
4. Each image is resized to fit Claude's API limits (max 2048px per side, max 3 MB raw) and re-encoded as JPEG.
5. All images are sent together in a single Claude vision request with a structured system prompt.
6. Claude returns a JSON object with: `name`, `description`, `components` (with ingredients), `steps`, `photo_index`, and `photo_crop`.
7. If a photo is identified (`photo_index`), it is cropped using the returned bounding box percentages and uploaded to S3 as the recipe image.
8. The recipe is saved with `needsApproval = true` (Pending Approval).
9. Review and approve the recipe in the admin interface to make it available via the API.

Queue status is visible in the admin sidebar via `/admin/queue-status`.

**Ingredient parsing rules applied by Claude:**
- Use maximum serving-size quantities when multiple serving sizes are listed.
- Group ingredients by component/section when the recipe has named sections.
- Put only the ingredient name in `name`; only quantity/unit in `measurement`; qualifiers (e.g. "optional", "to garnish") in `note`.
- Split steps by subject — all actions on the same ingredient or component belong in one step.

## Where to start

- Upload page: `/admin/parse-images`
- Queue status: `/admin/queue-status`
- Message: `app/src/Message/ParseRecipeImagesMessage.php`
- Handler: `app/src/MessageHandler/ParseRecipeImagesHandler.php`
- AI parser service: `app/src/Service/AiRecipeParser.php`

## Dependencies

- **`ANTHROPIC_API_KEY`** environment variable — the rest of the app works without it, but parsing fails at runtime if omitted
- **Symfony Messenger** with Doctrine transport (`MESSENGER_TRANSPORT_DSN`)
- **Worker container** must be running to process the queue
- **AWS S3** — parsed recipe images are uploaded to S3
- **`ext-gd`** — used for image resizing and cropping

---

<details>
<summary>Source Map</summary>

- [app/src/Service/AiRecipeParser.php](../../app/src/Service/AiRecipeParser.php) — Claude API client, image resize, JSON extraction
- [app/src/Message/ParseRecipeImagesMessage.php](../../app/src/Message/ParseRecipeImagesMessage.php) — async message payload
- [app/src/MessageHandler/ParseRecipeImagesHandler.php](../../app/src/MessageHandler/ParseRecipeImagesHandler.php) — message handler: calls parser, saves recipe
- [app/templates/admin/parse_recipe_images.html.twig](../../app/templates/admin/parse_recipe_images.html.twig) — upload UI
- [app/templates/admin/queue_status.html.twig](../../app/templates/admin/queue_status.html.twig) — queue status display

</details>

---

[Back to Features](README.md) | [Documentation Home](../README.md)
