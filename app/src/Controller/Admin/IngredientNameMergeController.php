<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\IngredientName;
use App\Repository\IngredientNameRepository;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// phpcs:disable Generic.Files.LineLength.TooLong
#[Route('/admin/ingredient-names/merge', name: 'admin_ingredient_name_merge', defaults: ['dashboardControllerFqcn' => DashboardController::class])]
// phpcs:enable
class IngredientNameMergeController extends AbstractController
{
    public function __construct(
        private readonly IngredientNameRepository $ingredientNameRepository,
        private readonly IngredientRepository $ingredientRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: '', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $allNames = $this->ingredientNameRepository->findBy([], ['name' => 'ASC']);

        $preview = null;
        $fromName = null;
        $toName = null;

        if ($request->isMethod('POST')) {
            $fromId = (int) $request->request->get('from_id');
            $toId = (int) $request->request->get('to_id');

            $fromName = $this->ingredientNameRepository->find($fromId);
            $toName = $this->ingredientNameRepository->find($toId);

            if ($fromName === null || $toName === null || $fromName === $toName) {
                $this->addFlash('danger', 'Please select two different ingredient names.');
                return $this->redirectToRoute('admin_ingredient_name_merge');
            }

            if ($request->request->get('action') === 'confirm') {
                if (!$this->isCsrfTokenValid('ingredient_merge', $request->request->get('_token'))) {
                    $this->addFlash('danger', 'Invalid CSRF token.');
                    return $this->redirectToRoute('admin_ingredient_name_merge');
                }

                $this->merge(
                    $fromName,
                    $toName,
                    $request->request->all('notes'),
                    $request->request->all('measurements'),
                );

                $this->addFlash('success', sprintf(
                    'Merged "%s" into "%s". %s removed.',
                    $fromName->getName(),
                    $toName->getName(),
                    $fromName->getName(),
                ));

                return $this->redirectToRoute('admin_ingredient_name_merge');
            }

            // action = preview
            $preview = $this->ingredientRepository->findByIngredientName($fromName);
        }

        return $this->render('admin/ingredient_name_merge.html.twig', [
            'allNames' => $allNames,
            'fromName' => $fromName,
            'toName' => $toName,
            'preview' => $preview,
            'csrfToken' => $this->container
                ->get('security.csrf.token_manager')
                ->getToken('ingredient_merge')
                ->getValue(),
        ]);
    }

    /** @param array<string,string> $notes @param array<string,string> $measurements */
    private function merge(IngredientName $from, IngredientName $to, array $notes = [], array $measurements = []): void
    {
        foreach ($this->ingredientRepository->findByIngredientName($from) as $ingredient) {
            $ingredient->setIngredientName($to);

            $id = (string) $ingredient->getId();

            if (array_key_exists($id, $notes)) {
                $ingredient->setNote($notes[$id] !== '' ? $notes[$id] : null);
            }

            if (array_key_exists($id, $measurements) && $measurements[$id] !== '') {
                $ingredient->setMeasurement($measurements[$id]);
            }
        }

        $this->entityManager->remove($from);
        $this->entityManager->flush();
    }
}
