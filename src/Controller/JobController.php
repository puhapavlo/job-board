<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\User;
use App\Repository\JobRepository;
use App\Repository\ResumeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/job')]
class JobController extends AbstractController {

    private User $currentUser;

    private JobRepository $jobRepository;


    public function __construct(TokenStorageInterface $tokenStorage, JobRepository $jobRepository) {
        $this->currentUser = $tokenStorage->getToken()->getUser();
        $this->jobRepository = $jobRepository;
    }

    #[Route('/recruiter/add')]
    public function add(Request $request) {
        $hasAccess = $this->isGranted('ROLE_RECRUITER');
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');
        if ($hasAccess) {
            $decoded = $this->getJobDecodedData($request);
            $job = new Job();
            $job->setAuthor($this->currentUser);
            $job->setPublishedAt(new \DateTimeImmutable());
            $job->setTitle($decoded['title']);
            $job->setType($decoded['type']);
            $job->setDescription($decoded['description']);
            $job->setCategory($decoded['category']);
            $job->setRequirements($decoded['requirements']);
            $this->jobRepository->save($job);
            return $this->json(["job" => $job->toArray()], Response::HTTP_CREATED);
        }
        return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/recruiter/delete/{id}')]
    public function delete(int $id) {
        $hasAccess = $this->isGranted('ROLE_RECRUITER');
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');
        $job = $this->jobRepository->find($id);
        if ($job->getAuthor()->getId() != $this->currentUser->getId()) {
            return $this->json(["message" => "You not author this job."], Response::HTTP_FORBIDDEN);
        }
        if ($hasAccess) {
            $this->jobRepository->remove($job);
            return $this->json(["message" => "Delete successfully."], Response::HTTP_OK);
        }
        return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/view/{id}')]
    public function view(int $id) {
        $job = $this->jobRepository->find($id);
        if (is_null($job)) {
            return $this->json(["message" => "Job not found."], Response::HTTP_NOT_FOUND);
        }
        return $this->json(["job" => $job->toArray()], Response::HTTP_OK);
    }

    public function getJobDecodedData(Request $request) {
        $decoded = json_decode($request->getContent());
        $title = $decoded->title;
        $description = $decoded->description ?? null;
        $type = $decoded->type ?? null;
        $requirements = $decoded->requirements ?? null;
        $category = $decoded->category ?? null;
        return [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'requirements' => $requirements,
            'category' => $category,
        ];
    }
}
