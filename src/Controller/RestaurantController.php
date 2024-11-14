<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Restaurant;
use App\Form\MenuType;
use App\Form\PlatType;
use App\Form\RestaurantType;
use App\Repository\MenuRepository;
use App\Repository\PlatRepository;
use App\Repository\CommandeRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/restaurant")]
class RestaurantController extends AbstractController
{
    private RestaurantRepository $restaurantRepository;
    private MenuRepository $menuRepository;
    private PlatRepository $platRepository;
    private CommandeRepository $commandeRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(RestaurantRepository $restaurantRepository, MenuRepository $menuRepository, PlatRepository $platRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->menuRepository = $menuRepository;
        $this->platRepository = $platRepository;
        $this->commandeRepository = $commandeRepository;
        $this->entityManager = $entityManager;
    }
    #[Route('/', name: 'restaurant_list')]
    public function list(): Response
    {
        $restaurants = $this->restaurantRepository->findAll();

        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    #[Route('/{id}/menu', name: 'restaurant_menu')]
    public function menu(Restaurant $restaurant): Response
    {
        $menus = $restaurant->getMenus();

        return $this->render('restaurant/menu.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $menus,
        ]);
    }

    #[Route('/create', name: 'restaurant_create')]
    public function make(Request $request): Response|RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $restaurant->setUser($user);
            $this->entityManager->persist($restaurant);
            $this->entityManager->flush();
            return $this->redirectToRoute('restaurant_list');
        }

        return $this->render('restaurant/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/edit', name: 'restaurant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($restaurant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('restaurant_list');
        }

        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('restaurant_list');
        }

        return $this->render('restaurant/edit.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/{id}/delete', name: 'restaurant_delete', methods: ['POST'])]
    public function delete(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($restaurant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('restaurant_list');
        }

        if ($this->isCsrfTokenValid('delete' . $restaurant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($restaurant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('restaurant_list');
    }

    #[Route("/manage/{id}", name: "restaurant_manage")]
    public function manageRestaurant(int $id): Response|RedirectResponse
    {
        $restaurant = $this->restaurantRepository->find($id);
        if (!$restaurant || $restaurant->getUser() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            return $this->redirectToRoute('restaurant_list');
        }
        return $this->render('restaurant/manage.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/{id}/manage/menus', name: 'restaurant_manage_menus')]
    public function manageMenus(int $id, Request $request): Response|RedirectResponse
    {
        $restaurant = $this->restaurantRepository->find($id);

        if ($restaurant === null) {
            throw $this->createNotFoundException('Restaurant not found');
        }
        if ($restaurant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('restaurant_list');
        }

        // Handle Menu creation
        $menu = new Menu();
        $menuForm = $this->createForm(MenuType::class, $menu);
        $menuForm->handleRequest($request);

        if ($menuForm->isSubmitted() && $menuForm->isValid()) {
            $menu->setRestaurant($restaurant);
            $this->entityManager->persist($menu);
            $this->entityManager->flush();

            return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurant->getId()]);
        }

        // Handle Plat forms for each menu
        $menus = $this->menuRepository->findBy(['restaurant' => $restaurant]);
        $platForms = [];

        foreach ($menus as $menu) {
            $plat = new Plat();
            $platForm = $this->createForm(PlatType::class, $plat);
            $platForm->handleRequest($request);
            $platForms[$menu->getId()] = $platForm->createView();

            if ($platForm->isSubmitted() && $platForm->isValid() && $request->request->get('menu_id') == $menu->getId()) {
                $plat->setMenu($menu);
                $this->entityManager->persist($plat);
                $this->entityManager->flush();

                return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurant->getId()]);
            }
        }

        return $this->render('restaurant/manage_menus.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $menus,
            'menuForm' => $menuForm->createView(),
            'platForms' => $platForms,
        ]);
    }
    #[Route('/{restaurantId}/menu/{menuId}/plat/{platId}/delete', name: 'restaurant_delete_plat', methods: ['POST'])]
    public function deletePlat(int $restaurantId, int $menuId, int $platId): RedirectResponse
    {
        $restaurant = $this->restaurantRepository->find($restaurantId);
        $menu = $this->menuRepository->find($menuId);
        $plat = $this->platRepository->find($platId);

        if (!$restaurant || !$menu || !$plat || $restaurant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurantId]);
        }

        $menu->removePlat($plat);
        $this->entityManager->remove($plat);
        $this->entityManager->flush();

        return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurantId]);
    }

    #[Route('/{restaurantId}/menu/{menuId}/delete', name: 'restaurant_delete_menu', methods: ['POST'])]
    public function deleteMenu(int $restaurantId, int $menuId): RedirectResponse
    {
        $restaurant = $this->restaurantRepository->find($restaurantId);
        $menu = $this->menuRepository->find($menuId);

        if (!$restaurant || !$menu || $restaurant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurantId]);
        }

        $this->entityManager->remove($menu);
        $this->entityManager->flush();

        return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurantId]);
    }

    #[Route('/{restaurantId}/menu/{menuId}/plat/{platId}/modify', name: 'restaurant_modify_plat')]
    public function modifyPlat(int $restaurantId, int $menuId, int $platId, Request $request): Response
    {
        $restaurant = $this->restaurantRepository->find($restaurantId);
        $menu = $this->menuRepository->find($menuId);
        $plat = $this->platRepository->find($platId);
        if (!$restaurant || !$menu || !$plat || $restaurant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('restaurant_list');
        }
        $platForm = $this->createForm(PlatType::class, $plat);
        $platForm->handleRequest($request);

        if ($platForm->isSubmitted() && $platForm->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurantId]);
        }
        return $this->render('plat/modify.html.twig', [
            'restaurant' => $restaurant,
            'menu' => $menu,
            'plat' => $plat,
            'platForm' => $platForm->createView(),
        ]);
    }

    #[Route("/restaurant/{id}/manage/commandes", name: "restaurant_manage_commandes")]
    public function showCommandes(int $id): Response|RedirectResponse
    {
        $restaurant = $this->restaurantRepository->find($id);
        if (!$restaurant || $restaurant->getUser() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            return $this->redirectToRoute('restaurant_list');
        }
        $commandes = $this->commandeRepository->findBy(['Restaurant' => $restaurant]);

        return $this->render('restaurant/manage_commandes.html.twig', [
            'restaurant' => $restaurant,
            'commandes' => $commandes,
        ]);
    }
}