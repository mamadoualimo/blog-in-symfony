<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NationaliteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiAuteurController extends AbstractController
{
    /**
     * @Route("/api/auteurs", name="api_auteurs", methods={"GET"})
     */
    public function list(AuteurRepository $repo, SerializerInterface $serializer): Response
    {
        $auteurs = $repo->findAll();
        $resultat = $serializer->serialize(
            $auteurs,
            'json',
            [
                'groups' => ['listAuteurFull']
            ]
        );
        return new JsonResponse($resultat, 200, [], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_show", methods={"GET"})
     */
    public function show(Auteur $auteur, SerializerInterface $serializer): Response
    {
        $resultat = $serializer->serialize(
            $auteur,
            'json',
            [
                'groups' => ['listAuteurSimple']
            ]
        );
        return new JsonResponse($resultat, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs", name="api_auteurs_create", methods={"POST"})
     */
    public function create(NationaliteRepository $repoNation, Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $auteur = new Auteur();
        $nationalite = $repoNation->find($dataTab['nationalite']['id']);
        $serializer->deserialize($data, Auteur::class, 'json', ['object_to_populate' => $auteur]);
        $auteur->setNationalite($nationalite);
        //$serializer->deserialize($data, Auteur::class, 'json',['object_to_populate'=$auteur]);
        // $auteur = $serializer->deserialize($data, Auteur::class, 'json');

        // Gestion des erreurs de validator
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($auteur);
        $em->flush();

        return new JsonResponse(
            "Le auteur a bien été créé",
            Response::HTTP_CREATED,
            // [
            //     "location" => "api/auteurs/" . $auteur->getId()
            // ],
            ["location" => $this->generateUrl(
                'api_auteurs_show',
                ["id" => $auteur->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )],
            true
        );
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_delete", methods={"DELETE"})
     */
    public function delete(Auteur $auteur, EntityManagerInterface $em): Response
    {
        $em->remove($auteur);
        $em->flush();

        return new JsonResponse("L'auteur a bien été supprimé", Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_update", methods={"PUT"})
     */
    public function edit(NationaliteRepository $repoNation, Request $request, EntityManagerInterface $em, Auteur $auteur, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $nationalite = $repoNation->find($dataTab['nationalite']['id']);
        // Solution 1
        $serializer->deserialize($data, Auteur::class, 'json', ['object_to_populate' => $auteur]);
        $auteur->setNationalite($nationalite);

        // Solution 1
        // $serializer->denormalize($dataTab['auteur'], Auteur::class, null, ['object_to_populate' => $auteur]);

        // Gestion des erreurs de validator
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($auteur);
        $em->flush();

        return new JsonResponse("L'auteur a bien été modifié", Response::HTTP_OK, [], true);
    }
}
