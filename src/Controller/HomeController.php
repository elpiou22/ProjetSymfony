<?php


namespace App\Controller;

use App\Form\MovieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\Movie;



class HomeController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $default_image = "images/default.webp";



        return $this->render('base.html.twig', [
            "image_path" => $default_image,
        ]);
    }




    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        // Si l'utilisateur est déjà connecté, redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }


        return $this->render('login.html.twig', [

        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(): Response
    {
        return $this->redirectToRoute('app_home');
    }


    #[Route('/create', name: 'app_movie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier téléchargé
            $file = $form->get('file')->getData();

            if ($file) {
                // Déplacement du fichier vers le dossier upload
                $newFilename = uniqid() . '.' . $file->guessExtension();
                $file->move('upload', $newFilename);

                // Enregistrement du chemin du fichier dans l'entité Movie
                $movie->setPosterPath($newFilename);
            }

            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('movies_list');
        }

        return $this->render('movies/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/watch', 'app_movie_read')]
    public function read(
        EntityManagerInterface $entityManager
    ): Response
    {
        $movies = $entityManager->getRepository(Movie::class)->findAll();

        return $this->render('test.html.twig', [
            'movies' => $movies
        ]);
    }

    #[Route('/movies', name: 'movies_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les films de la base de données
        $movies = $entityManager->getRepository(Movie::class)->findAll();

        // Rendu de la vue avec les films
        return $this->render('movies/movies.html.twig', [
            'movies' => $movies,
        ]);
    }

}