<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Menu;
use App\Entity\Commande;
use App\Entity\Plat;
use App\Form\RestaurantType;
use App\Form\PlatType;
use App\Repository\RestaurantRepository;
use App\Repository\MenuRepository;
use App\Repository\PlatRepository;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestaurantController extends AbstractController
{
    private RestaurantRepository $restaurantRepository;
    private MenuRepository $menuRepository;
    private PlatRepository $platRepository;
    private CommandeRepository $commandeRepository;

    public function __construct(RestaurantRepository $restaurantRepository, MenuRepository $menuRepository, PlatRepository $platRepository, CommandeRepository $commandeRepository) {
        $this->restaurantRepository = $restaurantRepository;
        $this->menuRepository = $menuRepository;
        $this->platRepository = $platRepository;
        $this->commandeRepository = $commandeRepository;
    }
    #[Route('/restaurants', name: 'restaurant_list')]
    public function list(): Response
    {
        $restaurants = $this->restaurantRepository->findAll();

        return $this->render('restaurant/list.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    #[Route('/restaurant/{id}/menu', name: 'restaurant_menu')]
    public function menu(Restaurant $restaurant): Response
    {
        $menus = $restaurant->getMenus();

        return $this->render('restaurant/menu.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $menus,
        ]);
    }

    #[Route('/restaurant/create', name: 'restaurant_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $restaurant->setRestaurateur($user);
            $this->restaurantRepository->save($restaurant, true);
            return $this->redirectToRoute('restaurant_list');
        }

        return $this->render('restaurant/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/restaurant/manage/{id}", name:"restaurant_manage")]
    public function manageRestaurant(int $id): Response
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

    #[Route('/restaurant/{id}/manage/menus', name: 'restaurant_manage_menus')]
    public function manageMenus(int $id, Request $request): Response
    {
        $restaurant = $this->restaurantRepository->find($id);
        if (!$restaurant || $restaurant->getUser() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            return $this->redirectToRoute('restaurant_list');
        }
        $menus = $this->menuRepository->findBy(['restaurant' => $restaurant]);
        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $form->get('menu')->getData();
            $plat->addMenu($menu);
            $this->platRepository->save($plat, true);
            $this->addFlash('success', 'Plat ajouter');
            return $this->redirectToRoute('restaurant_manage_menus', ['id' => $restaurant->getId()]);
        }

        return $this->render('restaurant/manage_menus.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $menus,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/restaurant/{id}/manage/commandes", name:"restaurant_manage_commandes")]
    public function showCommandes(int $id): Response
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