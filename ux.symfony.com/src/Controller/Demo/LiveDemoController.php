<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Demo;

use App\Entity\Invoice;
use App\Entity\TodoItem;
use App\Entity\TodoList;
use App\Form\TodoListFormType;
use App\Repository\FoodRepository;
use App\Repository\TodoListRepository;
use App\Service\LiveDemoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demos/live-component')]
class LiveDemoController extends AbstractController
{
    #[Route('/', name: 'app_demo_live_component')]
    public function __invoke(): Response
    {
        return $this->redirectToRoute('app_demos');
    }

    #[Route('/form-collection-type/{id}', name: 'app_demo_live_component_form_collection_type', defaults: ['id' => null])]
    public function demoFormCollectionType(LiveDemoRepository $liveDemoRepository, Request $request, TodoListRepository $todoListRepository, ?TodoList $todoList = null): Response
    {
        if (!$todoList) {
            $todoList = new TodoList();
            $todoList->addTodoItem(new TodoItem());
        }
        $form = $this->createForm(TodoListFormType::class, $todoList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $todoListRepository->add($form->getData(), true);
            $this->addFlash('live_demo_success', 'Excellent! With this to-do list, I won’t miss anything.');

            return $this->redirectToRoute('app_demo_live_component_form_collection_type', [
                'id' => $todoList->getId(),
            ]);
        }

        return $this->render('demos/live_component/form_collection_type.html.twig', [
            'form' => $form,
            'todoList' => $todoList,
            'demo' => $liveDemoRepository->find('form-collection-type'),
        ]);
    }

    #[Route('/voting', name: 'app_demo_live_component_voting')]
    public function demoVoting(LiveDemoRepository $liveDemoRepository, FoodRepository $foodRepository): Response
    {
        return $this->render('demos/live_component/voting.html.twig', [
            'demo' => $liveDemoRepository->find('voting'),
            'foods' => $foodRepository->findAll(),
        ]);
    }

    #[Route('/inline-edit', name: 'app_demo_live_component_inline_edit')]
    public function inlineEdit(LiveDemoRepository $liveDemoRepository, FoodRepository $foodRepository): Response
    {
        $food = $foodRepository->findOneBy([]);
        if (!$food) {
            throw $this->createNotFoundException('No food found - try running "php bin/console app:load-data"');
        }

        return $this->render('demos/live_component/inline_edit.html.twig', parameters: [
            'demo' => $liveDemoRepository->find('inline-edit'),
            'food' => $foodRepository->findOneBy([]),
        ]);
    }

    #[Route('/invoice/{id}', name: 'app_demo_live_component_invoice', defaults: ['id' => null])]
    public function invoice(LiveDemoRepository $liveDemoRepository, ?Invoice $invoice = null): Response
    {
        $invoice = $invoice ?? new Invoice();

        return $this->render('demos/live_component/invoice.html.twig', parameters: [
            'demo' => $liveDemoRepository->find('invoice'),
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{demo}', name: 'app_demo_live_component_demo')]
    #[Route('/auto-validating-form', name: 'app_demo_live_component_auto_validating_form')]
    #[Route('/chartjs', name: 'app_demo_live_component_chartjs')]
    #[Route('/dependent-form-fields', name: 'app_demo_live_component_dependent_form_fields')]
    #[Route('/infinite-scroll', name: 'app_demo_live_component_infinite_scroll')]
    #[Route('/infinite-scroll-2', name: 'app_demo_live_component_infinite_scroll_2')]
    #[Route('/product-form', name: 'app_demo_live_component_product_form')]
    #[Route('/upload', name: 'app_demo_live_component_upload')]
    public function demo(
        LiveDemoRepository $liveDemoRepository,
        string $demo,
    ): Response {
        $demo = $liveDemoRepository->find($demo);

        return $this->render($demo->getTemplate(), ['demo' => $demo]);
    }
}