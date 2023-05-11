<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use App\Repository\FileRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class RegistrationController extends AbstractController
{

    private FileRepository $fileRepository;


    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    #[Route('/register', name: 'app_registration')]
    public function index(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
      $em = $doctrine->getManager();
      $email = $request->request->get('email');
      $plaintextPassword = $request->request->get('password');
      $role = $request->request->get('role');
      $picture = json_decode($request->request->get('picture'));
      $picture_entity = null;
      if ($picture) {
          $base64 = $picture->base64;
          $picture_path_info = pathinfo($picture->name);
          $target_directory = tempnam($this->getParameter('uploads_directory'), $picture_path_info['filename']);
          $full_file = $target_directory . '.' . $picture_path_info['extension'];
          rename($target_directory, $full_file);
          $picture_file = base64_decode($base64);
          file_put_contents($full_file, $picture_file);
          $picture_entity = new File();
          $file_path_parts =  explode('/', $full_file);
          $relative_file_path = $this->getParameter('relative_directory') . '/' . end($file_path_parts);
          $picture_entity->setPath($relative_file_path);
          $this->fileRepository->save($picture_entity, true);
      }

      $user = new User();
      $hashedPassword = $passwordHasher->hashPassword(
        $user,
        $plaintextPassword
      );
      $user->setPassword($hashedPassword);
      $user->setEmail($email);
      $user->setRoles([$role]);
      if ($picture_entity) {
          $user->setPicture($picture_entity);
      }
      $em->persist($user);
      $em->flush();

      return $this->json(['message' => 'Registered Successfully'], Response::HTTP_CREATED);
    }
}
