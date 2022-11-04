<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('api/book', name: 'app_book', methods:['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        $jsonBookList= $serializerInterface->serialize($bookList,'json',['groups'=>'getBooks']);
        
        //retourne la liste de livre en format json et nous donne un status ok 
        return new JsonResponse($jsonBookList,Response::HTTP_OK,[],true);
    }

    #[Route('api/book/{id}', name: 'app_detail_book', methods:['GET'])]
    public function getDetailBook(Book $book,SerializerInterface $serializerInterface): JsonResponse
    {
            //grâce au parmaConverter on arrive à 
            //récupérer le livre en fonction de son id
            $jsonBook = $serializerInterface->serialize($book,'json',['groups'=>'getBooks']);
            return new JsonResponse($jsonBook,Response::HTTP_OK,[],true); 
    }
    
}
