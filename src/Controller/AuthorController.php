<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class AuthorController extends AbstractController
{
    #[Route('api/author', name: 'app_author', methods:['GET'])]
    public function getAllAuthor(AuthorRepository $authorRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonAuthorList= $serializerInterface->serialize($authorList,'json',['groups'=>'getAuthors']);
        
        //retourne la liste de livre en format json et nous donne un status ok 
        return new JsonResponse($jsonAuthorList,Response::HTTP_OK,[],true);
    }

    #[Route('api/author/{id}', name: 'app_detail_author', methods:['GET'])]
    public function getDetailAuthor(Author $author,SerializerInterface $serializerInterface): JsonResponse
    {
            //grâce au parmaConverter on arrive à 
            //récupérer le livre en fonction de son id
            $jsonAuthor = $serializerInterface->serialize($author,'json',['groups'=>'getAuthors']);
            return new JsonResponse($jsonAuthor,Response::HTTP_OK,[],true); 
    }

    #[Route('api/author', name:'app_create_author', methods:['POST'])]
    public function createAuthor(Request $request,SerializerInterface $serializerInterface,
    EntityManagerInterface $em,UrlGeneratorInterface $urlGeneratorInterface,ValidatorInterface $validator): JsonResponse
    {
        //création variable author et on vient deserialiser le contenu de author
            $author = $serializerInterface->deserialize($request->getContent(),Author::class,'json');

            // On vérifie les erreurs
        $errors = $validator->validate($author);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        //on garde les données dans notre seau
            $em->persist($author);
        //on expédie
            $em->flush();

        //on reprend les "groups" créés auparavant
        $jsonAuthor = $serializerInterface->serialize($author,'json',['groups'=>'getAuthors']);

        //on génère une URL avec l'id de l'auteur créé
            $location = $urlGeneratorInterface->generate('app_detail_author',['id'=>$author->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

        //on retourne un JSON avec le status et l'url 
            return new JsonResponse($jsonAuthor,Response::HTTP_CREATED,["location"=>$location],true); 
    }
    
}
