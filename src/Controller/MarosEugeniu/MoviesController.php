<?php

namespace App\Controller\MarosEugeniu;

use App\Entity\MarosEugeniu\Movie;
use App\Form\MarosEugeniu\MovieFormType;
use App\Repository\MarosEugeniu\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MoviesController extends AbstractController {
    private $em;
    private $movieRepository;
    public function __construct(EntityManagerInterface $em, MovieRepository $movieRepository) {
        $this->em = $em;
        $this->movieRepository = $movieRepository;
    }

    #[Route('/MarosEugeniu/movies', name: 'movies')]
    public function index(): Response{
        $movies = $this->movieRepository->findAll();
        return $this->render('movies/index.html.twig', [
            'movies' => $movies,
            'controller_name' => 'Vezi seteaza variabila asta din controller ca vadca in twig o chemi )))'
        ]);
    }

    #[Route('/MarosEugeniu/movies/create', name: 'create_movie')]
    public function create(Request $request): Response{

        phpinfo(); die;

        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newMovie = $form->getData();
            $imagePath = $form->get('imagePath')->getData();
            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();
                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
                $newMovie->setUserId($this->getUser()->getId());
                $newMovie->setImagePath('/uploads/' . $newFileName);
            }
            $this->em->persist($newMovie);
            $this->em->flush();
            return $this->redirectToRoute('movies');
        }
        return $this->render('/MarosEugeniu/movies/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/MarosEugeniu/movies/edit/{id}', name: 'edit_movie')]
    public function edit($id, Request $request): Response{
        $this->checkLoggedInUser($id);
        $movie = $this->movieRepository->find($id);
        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);
        $imagePath = $form->get('imagePath')->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                if ($movie->getImagePath() !== null) {
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . $movie->getImagePath()
                        )) {
                            $this->GetParameter('kernel.project_dir') . $movie->getImagePath();
                    }
                    $newFileName = uniqid() . '.' . $imagePath->guessExtension();
                    try {
                        $imagePath->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads',
                            $newFileName
                        );
                    } catch (FileException $e) {
                        return new Response($e->getMessage());
                    }
                    $movie->setImagePath('/uploads/' . $newFileName);
                    $this->em->flush();
                    return $this->redirectToRoute('movies');
                }
            } else {
                $movie->setTitle($form->get('title')->getData());
                $movie->setReleaseYear($form->get('releaseYear')->getData());
                $movie->setDescription($form->get('description')->getData());
                $this->em->flush();
                return $this->redirectToRoute('movies');
            }
        }
        return $this->render('/MarosEugeniu/movies/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView()
        ]);
    }

    #[Route('/MarosEugeniu/movies/delete/{id}', methods: ['GET', 'DELETE'], name: 'delete_movie')]
    public function delete($id): Response{
        $this->checkLoggedInUser($id);
        $movie = $this->movieRepository->find($id);
        $this->em->remove($movie);
        $this->em->flush();
        return $this->redirectToRoute('movies');
    }

    #[Route('/MarosEugeniu/movies/{id}', methods: ['GET'], name: 'show_movie')]
    public function show($id): Response
    {
        $movie = $this->movieRepository->find($id);
        
        return $this->render('movies/show.html.twig', [
            'movie' => $movie
        ]);
    }
    
    private function checkLoggedInUser($movieId) {
        if($this->getUser() == null || $this->getUser()->getId() !== $movieId) {
            return $this->redirectToRoute('movies');
        }
    }
}