<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Commande;
use App\Entity\Restaurant;
use App\Form\UserProfileType;
use App\Repository\PlatRepository;
use App\Repository\MenuRepository;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route("/user")]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'user_profile')]
    public function profile(Request $request): Response
    {
        $user = $this->getUser();

        // Create and handle the form
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your profile has been updated.');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/command', name: 'user_commands')]
    public function commands(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        $commands = $commandeRepository->findBy(['client' => $user]);

        return $this->render('user/commands.html.twig', [
            'commands' => $commands,
        ]);
    }

    #[Route('/command/delete/{id}', name: 'user_command_delete', methods: ['POST'])]
    public function deleteCommand(Commande $commande): Response
    {
        if ($commande->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not authorized to delete this command.');
        }

        $this->entityManager->remove($commande);
        $this->entityManager->flush();

        return $this->redirectToRoute('user_commands');
    }

    #[Route('/start-command/{restaurantId}', name: 'user_start_command')]
    public function startCommand(int $restaurantId, MenuRepository $menuRepository, PlatRepository $platRepository, Request $request): Response
    {
        $restaurant = $this->entityManager->getRepository(Restaurant::class)->find($restaurantId);
        if (!$restaurant) {
            $this->addFlash('error', 'Restaurant not found.');
            return $this->redirectToRoute('restaurant_list');
        }

        $menus = $menuRepository->findBy(['restaurant' => $restaurantId]);
        if (empty($menus)) {
            $this->addFlash('error', 'No menus found for this restaurant.');
            return $this->redirectToRoute('restaurant_list');
        }

        $plats = $platRepository->findBy(['menu' => $menus]);

        $session = $request->getSession();
        if (!$session->has('command')) {
            $session->set('command', []);
        }

        return $this->render('user/start_command.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $menus,
            'plats' => $plats,
            'commandeActuelle' => $session->get('command'),
        ]);
    }

    #[Route('/add-to-command/{platId}', name: 'user_add_to_command')]
    public function addToCommand(int $platId, PlatRepository $platRepository, Request $request): Response
    {
        $plat = $platRepository->find($platId);
        if (!$plat) {
            throw $this->createNotFoundException('Plat not found');
        }

        $session = $request->getSession();
        $command = $session->get('command', []);
        $command[] = $plat;
        $session->set('command', $command);

        return $this->redirectToRoute('user_start_command', ['restaurantId' => $plat->getMenu()->getRestaurant()->getId()]);
    }

    #[Route('/remove-from-command/{platId}', name: 'user_remove_from_command')]
    public function removeFromCommand(int $platId, Request $request): Response
    {
        $session = $request->getSession();
        $command = $session->get('command', []);
        $command = array_filter($command, function ($plat) use ($platId) {
            return $plat->getId() !== $platId;
        });
        $session->set('command', array_values($command));

        return $this->redirectToRoute('user_start_command', ['restaurantId' => $request->get('restaurantId')]);
    }

    #[Route('/reset-command/{restaurantId}', name: 'user_reset_command')]
    public function resetCommand(int $restaurantId, Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('command');
        return $this->redirectToRoute('user_start_command', ['restaurantId' => $restaurantId]);
    }

    #[Route('/submit-command/{restaurantId}', name: 'user_submit_command', methods: ['POST'])]
    public function submitCommand(int $restaurantId, Request $request): Response
    {
        $user = $this->getUser();
        $session = $request->getSession();
        $commandData = $session->get('command', []);

        if (empty($commandData)) {
            $this->addFlash('error', 'Your command is empty.');
            return $this->redirectToRoute('user_start_command', ['restaurantId' => $restaurantId]);
        }

        $restaurant = $this->entityManager->getRepository(Restaurant::class)->find($restaurantId);
        if (!$restaurant) {
            $this->addFlash('error', 'Restaurant not found.');
            return $this->redirectToRoute('restaurant_list');
        }

        $commande = new Commande();
        $commande->setClient($user);
        $commande->setRestaurant($restaurant);

        foreach ($commandData as $plat) {
            $commande->addPlat($plat);
        }
        $this->entityManager->persist($commande);
        $this->entityManager->flush();
        $session->remove('command');

        $this->addFlash('success', 'Your command has been successfully placed.');
        return $this->redirectToRoute('user_commands');
    }
}
