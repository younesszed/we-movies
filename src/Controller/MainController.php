<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\GenreRepositoryInterface;
use App\Repository\MovieRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function __construct(
        private GenreRepositoryInterface $genreRepository,
        private MovieRepositoryInterface $movieRepository
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $genres = $this->genreRepository->findAll();
        $popularMovies = $this->movieRepository->findPopular();

        return $this->render('main/index.html.twig', [
            'title' => 'Most popular movies',
            'genres' => $genres,
            'movies' => $popularMovies,
        ]);
    }

    #[Route('/api/movies/search', name: 'movies_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $search = $request->get('query');
        $movies = $this->movieRepository->findByTitle($search);

        return $this->json([
            'html' => $this->renderView('commun/_movies_list.html.twig', [
                'movies' => $movies,
            ]),
        ]);
    }

    #[Route('/api/movies/autocomplete', name: 'movies_autocomplete', methods: ['GET'])]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('query');
        $movies = $this->movieRepository->findByTitle($query);

        $suggestions = array_map(function ($movie) {
            return [

                    'id' => $movie->getId(),
                    'title' => $movie->getTitle(),
                    'release_date' => $movie->getReleaseDate() ? $movie->getReleaseDate()->format('Y') : null,
                    'poster_path' => $movie->getPosterPath(),

            ];
        }, $movies);

        return $this->json(['results' => $suggestions]);
    }

    #[Route('/api/movies/by-genres', name: 'movies_by_genres', methods: ['POST'])]
    public function moviesByGenres(Request $request): JsonResponse
    {
        $genreIds = $request->request->all('genres');

        try {
            $movies = $this->movieRepository->findByGenres($genreIds);

            $html = $this->renderView('commun/_movies_list.html.twig', [
                'movies' => $movies,
            ]);

            return $this->json([
                'success' => true,
                'html' => $html,
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    #[Route('/api/movie/{id}/modal', name: 'movie_modal', methods: ['GET'])]
    public function movieModal(int $id): JsonResponse
    {
        $movie = $this->movieRepository->findById($id);

        if (!$movie) {
            return $this->json(['error' => 'Movie not found'], 404);
        }

        return $this->json([
            'html' => $this->renderView('/commun/_movie_modal_content.html.twig', [
                'movie' => $movie,
            ]),
        ]);
    }
}