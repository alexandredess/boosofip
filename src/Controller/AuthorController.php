<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
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
}
