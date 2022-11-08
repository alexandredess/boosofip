<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'books', methods: ['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllBooks-" . $page . "-" . $limit;
        
        $jsonBookList = $cache->get($idCache, function (ItemInterface $item) use ($bookRepository, $page, $limit, $serializer) {
            $item->tag("booksCache");
            $bookList = $bookRepository->findAllWithPagination($page, $limit);
            return $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        });
      
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
   }

    #[Route('api/book/{id}', name: 'app_detail_book', methods:['GET'])]
    public function getDetailBook(Book $book,SerializerInterface $serializerInterface): JsonResponse
    {
            //grâce au parmaConverter on arrive à 
            //récupérer le livre en fonction de son id
            $jsonBook = $serializerInterface->serialize($book,'json',['groups'=>'getBooks']);
            return new JsonResponse($jsonBook,Response::HTTP_OK,[],true); 
    }

    #[Route('api/book/{id}', name:'app_delete_book', methods:['DELETE'])]
    public function deleteBook(Book $book,BookRepository $bookRepository): JsonResponse
    {
            //on utilise le remove de BookRepository
            $bookRepository -> remove($book,true);
            return new JsonResponse(null,Response::HTTP_NO_CONTENT); 
    }

    #[Route('api/book', name:'app_create_book', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN',message:"Vous n'avez pas les droits suffisant pour créer un livre")]
    public function createBook(Request $request,SerializerInterface $serializerInterface,
    EntityManagerInterface $em,UrlGeneratorInterface $urlGeneratorInterface,AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        //création variable book et on vient deserialiser le contenu de Book
            $book = $serializerInterface->deserialize($request->getContent(),Book::class,'json');

            // On vérifie les erreurs
        $errors = $validator->validate($book);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        //Récupération de l'ensemble des données envoyés sous forme de tableau
            $content = $request->toArray();

        //Récupération de l'idAuthor.
        //S'il n'est pas défini , alors on met -1 par défaut
            $idAuthor=$content['author']??-1;
        //on cherche l'auteur qui correspond et on l'assigne au livre
            $book->setAuthor($authorRepository->find($idAuthor));
        //on garde les données dans notre seau
            $em->persist($book);
        //on expédie
            $em->flush();

        //on reprend les "groups" créés auparavant
            $jsonBook = $serializerInterface->serialize($book,'json',['groups'=>'getBooks']);

        //on génère une URL avec l'id du livre créé
            $location = $urlGeneratorInterface->generate('app_detail_book',['id'=>$book->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

        //on retourne un JSON avec le status et l'url 
            return new JsonResponse($jsonBook,Response::HTTP_CREATED,["location"=>$location],true); 
    }

    #[Route('api/book/{id}', name:'app_update_book', methods:['PUT'])]
    public function updateBook(Request $request,SerializerInterface $serializerInterface,Book $currentBook,
    EntityManagerInterface $em,AuthorRepository $authorRepository): JsonResponse
    {
        $updateBook = $serializerInterface->deserialize($request->getContent(),
                    Book::class,
                    'json',[AbstractNormalizer::OBJECT_TO_POPULATE=>$currentBook]);
        $content = $request->toArray();
        $idAuthor=$content['author']??-1;
        $updateBook->setAuthor($authorRepository->find($idAuthor));

        $em->persist($updateBook);
        $em->flush();

        return new JsonResponse(null,JsonResponse::HTTP_ACCEPTED); 
    }

}
