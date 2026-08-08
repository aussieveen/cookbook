<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\IngredientName;
use App\Entity\Recipe;
use App\Entity\Step;
use App\Enum\Course;
use App\Enum\MealOccasion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DinnerFixtures extends Fixture
{
    /** @var array<string, IngredientName> */
    private array $ingredientNames = [];

    private const SIDES = [
        'Roast Potatoes' => [
            'desc'  => 'Crispy on the outside, fluffy in the middle — roasted in goose fat until golden.',
            'image' => 'fixture-roast-potatoes.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Maris Piper potatoes', '1kg', null],
                    ['Goose fat', '3 tbsp', 'Vegetable oil works too'],
                    ['Flaky sea salt', '1 tsp', null],
                ]],
            ],
            'steps' => [
                'Peel the potatoes and cut into large chunks. Boil in salted water for 10 minutes until the edges are just beginning to soften.',
                'Drain thoroughly, then return to the pan and shake vigorously to rough up the edges — this is what makes them crispy.',
                'Heat the goose fat in a roasting tin in the oven at 220°C. Add the potatoes, turning to coat, and roast for 40–45 minutes, turning halfway, until deeply golden.',
            ],
        ],
        'Mashed Potato' => [
            'desc'  => 'Buttery, creamy mash made with plenty of butter and warm milk.',
            'image' => 'fixture-mashed-potato.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Maris Piper potatoes', '1kg', 'Floury variety essential'],
                    ['Unsalted butter', '50g', 'Plus extra to serve'],
                    ['Whole milk', '100ml', 'Warmed'],
                    ['Salt', 'pinch', null],
                ]],
            ],
            'steps' => [
                'Peel and chop the potatoes into even chunks. Cover with cold salted water and bring to a boil. Cook for 15–20 minutes until completely tender.',
                'Drain and leave in the colander for 2 minutes to steam dry — this prevents watery mash.',
                'Pass through a potato ricer or mash well. Beat in the warm milk and butter until smooth and creamy. Season generously.',
            ],
        ],
        'Steamed Broccoli' => [
            'desc'  => 'Fresh broccoli florets steamed until tender-crisp with a squeeze of lemon.',
            'image' => 'fixture-steamed-broccoli.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Broccoli', '1 large head', 'About 400g'],
                    ['Lemon', '½', 'Juice only'],
                    ['Salt', 'pinch', null],
                ]],
            ],
            'steps' => [
                'Break the broccoli into florets of similar size. Trim and peel the stalk and cut into batons.',
                'Steam over boiling water for 4–5 minutes until bright green and just tender.',
                'Squeeze over a little lemon juice and season with salt before serving.',
            ],
        ],
        'Minted Peas' => [
            'desc'  => 'Garden peas simmered with fresh mint and a knob of butter.',
            'image' => 'fixture-minted-peas.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Frozen peas', '300g', null],
                    ['Fresh mint', '4 sprigs', 'Leaves only'],
                    ['Unsalted butter', '15g', null],
                    ['Salt', 'pinch', null],
                ]],
            ],
            'steps' => [
                'Bring a small pan of salted water to a boil. Add the peas and cook for 2–3 minutes.',
                'Drain and return to the pan. Add the butter and mint leaves, stirring until the butter melts.',
                'Season and serve immediately.',
            ],
        ],
        'Roasted Vegetables' => [
            'desc'  => 'Seasonal vegetables roasted in olive oil with thyme and garlic.',
            'image' => 'fixture-roasted-vegetables.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Courgette', '1 large', 'Cut into chunks'],
                    ['Red pepper', '1', 'Deseeded and sliced'],
                    ['Red onion', '1', 'Cut into wedges'],
                    ['Olive oil', '3 tbsp', null],
                    ['Thyme', '3 sprigs', null],
                    ['Garlic', '2 cloves', 'Unpeeled'],
                ]],
            ],
            'steps' => [
                'Heat the oven to 200°C. Spread the vegetables across a large roasting tin — don\'t overcrowd or they\'ll steam.',
                'Drizzle with olive oil, tuck in the thyme sprigs and garlic, and season well.',
                'Roast for 30–35 minutes, turning halfway through, until tender and lightly charred at the edges.',
            ],
        ],
        'Cauliflower Cheese' => [
            'desc'  => 'Tender cauliflower baked in a rich, bubbling cheddar bechamel.',
            'image' => 'fixture-cauliflower-cheese.jpg',
            'components' => [
                ['name' => 'Cauliflower', 'ingredients' => [
                    ['Cauliflower', '1 large', 'Broken into florets'],
                ]],
                ['name' => 'Cheese sauce', 'ingredients' => [
                    ['Unsalted butter', '40g', null],
                    ['Plain flour', '40g', null],
                    ['Whole milk', '500ml', null],
                    ['Mature cheddar', '100g', 'Grated, plus extra for the top'],
                    ['Dijon mustard', '1 tsp', null],
                ]],
            ],
            'steps' => [
                'Boil the cauliflower in salted water for 6 minutes until just tender. Drain and tip into a baking dish.',
                'Make a roux: melt the butter, stir in the flour and cook for 1 minute. Gradually whisk in the milk until smooth and thick.',
                'Remove from the heat and stir in the cheese and mustard. Pour over the cauliflower, scatter over extra cheese, and bake at 200°C for 20 minutes until bubbling and golden.',
            ],
        ],
        'Green Beans' => [
            'desc'  => 'Fine green beans blanched and tossed in butter with toasted almonds.',
            'image' => 'fixture-green-beans.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Fine green beans', '300g', 'Topped and tailed'],
                    ['Unsalted butter', '20g', null],
                    ['Flaked almonds', '30g', 'Toasted in a dry pan'],
                    ['Salt', 'pinch', null],
                ]],
            ],
            'steps' => [
                'Cook the beans in boiling salted water for 3–4 minutes until bright green and just tender.',
                'Drain and immediately toss with the butter until melted and glossy.',
                'Scatter over the toasted almonds and serve at once.',
            ],
        ],
    ];

    private const STANDALONE_MAINS = [
        [
            'name'      => 'Spaghetti Bolognese',
            'desc'      => 'A slow-cooked meat ragu with soffritto, red wine, and a splash of milk — proper Sunday sauce.',
            'mastered'  => true,
            'occasions' => [MealOccasion::DINNER],
            'image'     => 'fixture-spaghetti-bolognese.jpg',
            'components' => [
                ['name' => 'The ragu', 'ingredients' => [
                    ['Beef mince', '500g', '15–20% fat for flavour'],
                    ['Onion', '1 large', 'Finely diced'],
                    ['Carrot', '1', 'Finely diced'],
                    ['Celery', '2 sticks', 'Finely diced'],
                    ['Garlic', '3 cloves', 'Crushed'],
                    ['Red wine', '150ml', null],
                    ['Chopped tomatoes', '1 x 400g tin', null],
                    ['Whole milk', 'splash', 'Stirred in at the end'],
                ]],
                ['name' => 'To serve', 'ingredients' => [
                    ['Spaghetti', '400g', null],
                    ['Parmesan', 'to taste', 'Freshly grated'],
                ]],
            ],
            'steps' => [
                'Sweat the onion, carrot, and celery in a little olive oil over a low heat for 10 minutes until very soft — this is your soffritto.',
                'Add the garlic and cook for another minute. Turn up the heat, add the mince, and brown well, breaking up any lumps.',
                'Pour in the red wine and let it bubble away. Add the tomatoes, season well, and simmer very gently for at least 45 minutes.',
                'Stir in a splash of milk to round out the acidity. Cook your pasta, toss with the ragu, and finish with plenty of Parmesan.',
            ],
        ],
        [
            'name'      => 'Chicken Tikka Masala',
            'desc'      => 'Marinated, charred chicken tikka simmered in a creamy tomato and spiced yoghurt sauce.',
            'mastered'  => true,
            'occasions' => [MealOccasion::DINNER],
            'image'     => 'fixture-chicken-tikka-masala.jpg',
            'components' => [
                ['name' => 'For the chicken tikka', 'ingredients' => [
                    ['Chicken breast', '4', 'Cut into chunks'],
                    ['Greek yoghurt', '200ml', null],
                    ['Tikka masala paste', '2 tbsp', null],
                    ['Lemon juice', '1 tbsp', null],
                ]],
                ['name' => 'For the masala sauce', 'ingredients' => [
                    ['Onion', '1 large', 'Finely sliced'],
                    ['Garlic', '4 cloves', 'Minced'],
                    ['Ginger', '2cm piece', 'Grated'],
                    ['Chopped tomatoes', '1 x 400g tin', null],
                    ['Double cream', '100ml', null],
                    ['Garam masala', '1 tsp', null],
                ]],
            ],
            'steps' => [
                'Marinate the chicken in the yoghurt, tikka paste, and lemon juice for at least 1 hour, ideally overnight.',
                'Thread onto skewers and grill or cook in a very hot griddle pan until charred in spots. Set aside.',
                'Fry the onion until golden, add the garlic and ginger, then the tomatoes. Simmer for 15 minutes until thickened.',
                'Stir in the double cream and garam masala. Add the cooked chicken and simmer for 10 minutes. Serve with rice or naan.',
            ],
        ],
        [
            'name'      => 'Beef Lasagne',
            'desc'      => 'Rich bolognese layered with silky bechamel and fresh pasta, baked until bubbling.',
            'mastered'  => false,
            'occasions' => [MealOccasion::DINNER],
            'image'     => 'fixture-beef-lasagne.jpg',
            'components' => [
                ['name' => 'Bolognese layer', 'ingredients' => [
                    ['Beef mince', '500g', null],
                    ['Onion', '1', 'Diced'],
                    ['Garlic', '2 cloves', 'Crushed'],
                    ['Chopped tomatoes', '1 x 400g tin', null],
                    ['Red wine', '150ml', null],
                ]],
                ['name' => 'Bechamel', 'ingredients' => [
                    ['Unsalted butter', '60g', null],
                    ['Plain flour', '60g', null],
                    ['Whole milk', '600ml', null],
                    ['Nutmeg', 'pinch', 'Freshly grated'],
                ]],
                ['name' => 'To assemble', 'ingredients' => [
                    ['Lasagne sheets', '12', 'Fresh if possible'],
                    ['Parmesan', '80g', 'Grated'],
                ]],
            ],
            'steps' => [
                'Brown the mince with the onion and garlic. Add the wine, let it reduce, then add the tomatoes. Simmer for 30 minutes.',
                'Make the bechamel: melt the butter, stir in the flour, then whisk in the milk gradually until smooth and thick. Season with nutmeg.',
                'In a large baking dish, layer: bolognese, pasta, bechamel. Repeat twice, finishing with bechamel and a thick layer of Parmesan.',
                'Bake at 180°C for 40 minutes until golden and bubbling. Rest for 10 minutes before cutting.',
            ],
        ],
        [
            'name'      => 'Chilli Con Carne',
            'desc'      => 'Slow-cooked beef chilli with kidney beans, chipotle, and a square of dark chocolate for depth.',
            'mastered'  => false,
            'occasions' => [MealOccasion::DINNER],
            'image'     => 'fixture-chilli-con-carne.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Beef mince', '500g', null],
                    ['Onion', '1', 'Diced'],
                    ['Garlic', '3 cloves', 'Crushed'],
                    ['Kidney beans', '1 x 400g tin', 'Drained and rinsed'],
                    ['Chopped tomatoes', '1 x 400g tin', null],
                    ['Chipotle paste', '2 tsp', null],
                    ['Dark chocolate', '1 square', '70% cocoa'],
                    ['Ground cumin', '1 tsp', null],
                ]],
            ],
            'steps' => [
                'Brown the mince in batches in a hot pan. Remove and set aside.',
                'Fry the onion until soft, add the garlic, cumin, and chipotle paste and cook for 2 minutes.',
                'Return the mince, add the tomatoes and a splash of water. Season and simmer for 45 minutes.',
                'Stir in the kidney beans and the square of dark chocolate. Simmer for another 15 minutes. Serve with rice, soured cream, and guacamole.',
            ],
        ],
        [
            'name'      => 'Mushroom Risotto',
            'desc'      => 'Arborio rice cooked low and slow with porcini, Parmesan, and a glass of dry white wine.',
            'mastered'  => true,
            'occasions' => [MealOccasion::DINNER],
            'image'     => 'fixture-mushroom-risotto.jpg',
            'components' => [
                ['name' => 'The risotto', 'ingredients' => [
                    ['Arborio rice', '300g', null],
                    ['Dried porcini mushrooms', '30g', 'Soaked in 200ml hot water'],
                    ['Chestnut mushrooms', '300g', 'Sliced'],
                    ['Onion', '1', 'Finely diced'],
                    ['Dry white wine', '150ml', null],
                    ['Parmesan', '80g', 'Grated'],
                    ['Unsalted butter', '40g', null],
                ]],
                ['name' => 'Stock', 'ingredients' => [
                    ['Vegetable stock', '1.2 litres', 'Kept warm in a pan'],
                ]],
            ],
            'steps' => [
                'Soak the porcini in 200ml hot water for 20 minutes. Drain, reserving the soaking liquid. Roughly chop the porcini.',
                'Sauté the onion in butter until soft. Add the chestnut mushrooms and cook until golden. Add the porcini and rice and stir to coat.',
                'Pour in the wine and stir until absorbed. Add the stock a ladleful at a time, stirring constantly and waiting until each addition is absorbed.',
                'After about 20 minutes, when the rice is tender with a slight bite, remove from heat. Beat in the remaining butter and Parmesan. Rest for 2 minutes before serving.',
            ],
        ],
    ];

    private const PAIRED_MAINS = [
        [
            'name'     => 'Roast Chicken',
            'desc'     => 'Whole free-range chicken roasted over root vegetables with tarragon butter under the skin.',
            'mastered' => true,
            'sides'    => ['Roast Potatoes', 'Steamed Broccoli'],
            'image'    => 'fixture-roast-chicken.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Whole chicken', '1.8kg', 'Free-range'],
                    ['Unsalted butter', '50g', 'Softened'],
                    ['Fresh tarragon', '1 bunch', null],
                    ['Garlic', '1 bulb', 'Halved horizontally'],
                    ['Lemon', '1', 'Halved'],
                    ['Root vegetables', '500g', 'Carrots and parsnips, roughly chopped'],
                ]],
            ],
            'steps' => [
                'Remove the chicken from the fridge 30 minutes before cooking. Mix the softened butter with most of the tarragon and stuff under the breast skin.',
                'Stuff the cavity with the lemon halves, garlic, and remaining tarragon. Season all over.',
                'Arrange the root vegetables in a roasting tin to make a trivet. Sit the chicken on top and roast at 200°C for 1 hour 30 minutes.',
                'Rest uncovered for at least 20 minutes before carving. Use the roasting juices to make a gravy.',
            ],
        ],
        [
            'name'     => 'Creamy Chicken',
            'desc'     => 'Chicken breasts in a rich tarragon and creme fraiche sauce — weeknight dinner, weekend flavour.',
            'mastered' => false,
            'sides'    => ['Mashed Potato', 'Steamed Broccoli'],
            'image'    => 'fixture-creamy-chicken.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Chicken breast', '4', 'Skin-on if possible'],
                    ['Shallots', '2', 'Finely sliced'],
                    ['Dry white wine', '100ml', null],
                    ['Chicken stock', '200ml', null],
                    ['Creme fraiche', '200ml', null],
                    ['Fresh tarragon', '1 small bunch', 'Leaves only'],
                    ['Dijon mustard', '1 tsp', null],
                ]],
            ],
            'steps' => [
                'Season the chicken breasts and brown in a little oil over a high heat for 4 minutes each side. Remove and set aside.',
                'Lower the heat and soften the shallots in the same pan for 3 minutes. Deglaze with the wine, scraping up any browned bits.',
                'Add the stock and return the chicken to the pan. Cover and simmer gently for 15 minutes until cooked through.',
                'Stir in the creme fraiche, mustard, and most of the tarragon. Bubble for 2 minutes until the sauce thickens slightly. Scatter over the remaining tarragon.',
            ],
        ],
        [
            'name'     => 'Lamb Chops',
            'desc'     => 'Rosemary and garlic marinated lamb cutlets griddled until pink and rested well.',
            'mastered' => true,
            'sides'    => ['Roast Potatoes', 'Minted Peas'],
            'image'    => 'fixture-lamb-chops.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Lamb chops', '8', 'About 100g each'],
                    ['Fresh rosemary', '3 sprigs', 'Leaves finely chopped'],
                    ['Garlic', '4 cloves', 'Crushed'],
                    ['Olive oil', '3 tbsp', null],
                    ['Lemon juice', '2 tbsp', null],
                ]],
            ],
            'steps' => [
                'Mix the rosemary, garlic, olive oil, and lemon juice to make the marinade. Coat the chops and leave for at least 30 minutes at room temperature.',
                'Get a griddle pan or heavy frying pan very hot. Shake off any excess marinade and cook the chops for 3–4 minutes each side for medium-pink.',
                'Rest the chops on a warm plate for 5 minutes before serving — this is not optional.',
            ],
        ],
        [
            'name'     => 'Pork Tenderloin',
            'desc'     => 'Pork fillet wrapped in prosciutto, pan-seared and finished in the oven until just blushing.',
            'mastered' => false,
            'sides'    => ['Roasted Vegetables', 'Cauliflower Cheese'],
            'image'    => 'fixture-pork-tenderloin.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Pork tenderloin', '2', 'About 400g each, trimmed'],
                    ['Prosciutto', '8 slices', null],
                    ['Fresh sage', '8 leaves', null],
                    ['Dijon mustard', '2 tsp', null],
                    ['Olive oil', '2 tbsp', null],
                ]],
            ],
            'steps' => [
                'Pat the pork dry and season. Brush each fillet with the Dijon mustard.',
                'Lay out the prosciutto slices overlapping on a board. Place a sage leaf on each slice, then sit the pork at one end and roll tightly to wrap.',
                'Sear in hot oil for 3–4 minutes, turning to brown all sides. Transfer to a 200°C oven for 12–15 minutes until just cooked through.',
                'Rest for 5 minutes before slicing into medallions.',
            ],
        ],
        [
            'name'     => 'Sausage Casserole',
            'desc'     => 'Pork sausages braised in white wine, tomatoes, and Puy lentils until deeply flavourful.',
            'mastered' => true,
            'sides'    => ['Mashed Potato', 'Green Beans'],
            'image'    => 'fixture-sausage-casserole.jpg',
            'components' => [
                ['name' => null, 'ingredients' => [
                    ['Pork sausages', '8', 'Good quality'],
                    ['Onion', '1', 'Sliced'],
                    ['Garlic', '2 cloves', 'Sliced'],
                    ['Dry white wine', '150ml', null],
                    ['Chopped tomatoes', '1 x 400g tin', null],
                    ['Puy lentils', '200g', null],
                    ['Chicken stock', '400ml', null],
                    ['Thyme', '3 sprigs', null],
                ]],
            ],
            'steps' => [
                'Brown the sausages all over in a wide casserole pan. Remove and set aside.',
                'Fry the onion in the same pan until soft, then add the garlic and thyme and cook for 1 minute.',
                'Pour in the wine and let it bubble for 2 minutes. Add the tomatoes, lentils, and stock. Return the sausages to the pan.',
                'Cover and simmer gently for 30 minutes until the lentils are tender and the sauce has thickened. Serve in deep bowls.',
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $this->ingredientNames = [];

        $sidesByName = [];

        foreach (self::SIDES as $name => $data) {
            $recipe = $this->makeRecipe($name, $data['desc'], Course::SIDE, [MealOccasion::DINNER], null, $data['image']);
            $this->addComponents($recipe, $data['components'], $manager);
            $this->addSteps($recipe, $data['steps']);
            $sidesByName[$name] = $recipe;
            $manager->persist($recipe);
        }

        foreach (self::STANDALONE_MAINS as $data) {
            $recipe = $this->makeRecipe($data['name'], $data['desc'], Course::MAIN, $data['occasions'], $data['mastered'], $data['image']);
            $this->addComponents($recipe, $data['components'], $manager);
            $this->addSteps($recipe, $data['steps']);
            $manager->persist($recipe);
        }

        foreach (self::PAIRED_MAINS as $data) {
            $recipe = $this->makeRecipe($data['name'], $data['desc'], Course::MAIN, [MealOccasion::DINNER], $data['mastered'], $data['image']);
            $this->addComponents($recipe, $data['components'], $manager);
            $this->addSteps($recipe, $data['steps']);
            foreach ($data['sides'] as $sideName) {
                $recipe->addPairsWith($sidesByName[$sideName]);
            }
            $manager->persist($recipe);
        }

        $manager->flush();
    }

    /** @param MealOccasion[] $occasions */
    private function makeRecipe(
        string $name,
        string $description,
        Course $course,
        array $occasions,
        ?bool $mastered,
        string $image,
    ): Recipe {
        $recipe = new Recipe();
        $recipe->setName($name);
        $recipe->setDescription($description);
        $recipe->setCourse($course);
        $recipe->setMealOccasions($occasions);
        $recipe->setMastered($mastered);
        $recipe->setImage($image);

        return $recipe;
    }

    /**
     * @param array<array{name: string|null, ingredients: array<array{0: string, 1: string, 2: string|null}>}> $componentDefs
     */
    private function addComponents(Recipe $recipe, array $componentDefs, ObjectManager $manager): void
    {
        foreach ($componentDefs as $def) {
            $component = new Component();
            $component->setName($def['name']);
            $component->setRecipe($recipe);

            foreach ($def['ingredients'] as [$iName, $measurement, $note]) {
                $ingredient = new Ingredient();
                $ingredient->setMeasurement($measurement);
                $ingredient->setNote($note);
                $ingredient->setIngredientName($this->ingredientName($iName, $manager));
                $component->addIngredient($ingredient);
            }

            $recipe->addComponent($component);
        }
    }

    /** @param string[] $steps */
    private function addSteps(Recipe $recipe, array $steps): void
    {
        foreach ($steps as $detail) {
            $step = new Step();
            $step->setDetail($detail);
            $recipe->addStep($step);
        }
    }

    private function ingredientName(string $name, ObjectManager $manager): IngredientName
    {
        if (!isset($this->ingredientNames[$name])) {
            $iname = new IngredientName();
            $iname->setName($name);
            $manager->persist($iname);
            $this->ingredientNames[$name] = $iname;
        }

        return $this->ingredientNames[$name];
    }
}
