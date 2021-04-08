<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiGenreController extends AbstractController
{
    /**
     * @Route("/api/genres", name="api_genres", methods={"GET"})
     */
    public function list(GenreRepository $repo, SerializerInterface $serializer): Response
    {
        $genres = $repo->findAll();
        $resultat = $serializer->serialize(
            $genres,
            'json',
            [
                'groups' => ['listGenreFull']
            ]
        );
        return new JsonResponse($resultat, 200, [], true);
    }

    /**
     * @Route("/api/genres/{id}", name="api_genres_show", methods={"GET"})
     */
    public function show(Genre $genre, SerializerInterface $serializer): Response
    {
        $resultat = $serializer->serialize(
            $genre,
            'json',
            [
                'groups' => ['listGenreSimple']
            ]
        );
        return new JsonResponse($resultat, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/genres", name="api_genres_create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        // $genre=new Genre();
        //$serializer->deserialize($data, Genre::class, 'json',['object_to_populate'=$genre]);
        $genre = $serializer->deserialize($data, Genre::class, 'json');

        // Gestion des erreurs de validator
        $errors = $validator->validate($genre);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($genre);
        $em->flush();

        return new JsonResponse(
            "Le genre a bien été créé",
            Response::HTTP_CREATED,
            // [
            //     "location" => "api/genres/" . $genre->getId()
            // ],
            ["location" => $this->generateUrl(
                'api_genres_show',
                ["id" => $genre->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )],
            true
        );
    }

    /**
     * @Route("/api/genres/{id}", name="api_genres_update", methods={"PUT"})
     */
    public function edit(Request $request, EntityManagerInterface $em, Genre $genre, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        $serializer->deserialize($data, Genre::class, 'json', ['object_to_populate' => $genre]);

        // Gestion des erreurs de validator
        $errors = $validator->validate($genre);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($genre);
        $em->flush();

        return new JsonResponse("Le genre a bien été modifié", Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/genres/{id}", name="api_genres_delete", methods={"DELETE"})
     */
    public function delete(Genre $genre, EntityManagerInterface $em): Response
    {
        $em->remove($genre);
        $em->flush();

        return new JsonResponse("Le genre a bien été supprimé", Response::HTTP_OK, [], true);
    }
}
