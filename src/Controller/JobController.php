<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\User;
use App\Repository\JobRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/job')]
class JobController extends AbstractController {

    private User|null $currentUser;

    private JobRepository $jobRepository;


    public function __construct(TokenStorageInterface $tokenStorage, JobRepository $jobRepository) {
        $this->currentUser = $tokenStorage->getToken()?->getUser();
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
            $job->setLocation($decoded['location']);
            $job->setSalary($decoded['salary']);
            $job->setCompany($decoded['company']);
            $this->jobRepository->save($job , true);
            return $this->json(["job" => $job->toArray()], Response::HTTP_CREATED);
        }
        return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/recruiter/update/{id}')]
    public function update(Request $request, int $id) {
        $hasAccess = $this->isGranted('ROLE_RECRUITER');
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');
        if ($hasAccess) {
            $job = $this->jobRepository->find($id);
            if ($job->getAuthor()->getId() != $this->currentUser->getId()) {
                return $this->json(["message" => "You not author this job."], Response::HTTP_FORBIDDEN);
            }
            $decoded = $this->getJobDecodedData($request);
            $job->setAuthor($this->currentUser);
            $job->setTitle($decoded['title']);
            $job->setType($decoded['type']);
            $job->setDescription($decoded['description']);
            $job->setCategory($decoded['category']);
            $job->setRequirements($decoded['requirements']);
            $job->setLocation($decoded['location']);
            $job->setSalary($decoded['salary']);
            $job->setCompany($decoded['company']);
            $this->jobRepository->save($job , true);
            return $this->json(["job" => $job->toArray()], Response::HTTP_OK);
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
            $this->jobRepository->remove($job, true);
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
        return $this->json($job->toArray(), Response::HTTP_OK);
    }

    #[Route('/all')]
    public function viewAll(Request $request,  PaginatorInterface $paginator) {
        $jobQueryBuilder = $this->jobRepository
            ->createQueryBuilder('j');
        $itemsPerPage = 5;
        $category = $request->query->get('category');
        $type = $request->query->get('type');
        $location = $request->query->get('location');
        if (!is_null($category)) {
            $jobQueryBuilder
                ->andWhere('j.category = :category')
                ->setParameter('category', $category);
        }
        if (!is_null($type)) {
            $jobQueryBuilder
                ->andWhere('j.type = :type')
                ->setParameter('type', $type);
        }
        if (!is_null($location)) {
            $jobQueryBuilder
                ->andWhere('j.location = :location')
                ->setParameter('location', $location);
        }
        $jobQuery = $jobQueryBuilder->getQuery();
        $jobsCount = count($jobQuery->execute());
        $jobs = $paginator->paginate(
            $jobQuery,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );

        $jobs_res = [];

        foreach ($jobs as $job) {
            $jobs_res[] = $job->toArray();
        }

        return $this->json(['count' => $jobsCount, 'itemsPerPage' => $itemsPerPage, 'jobs' => $jobs_res]);
    }

    #[Route('/recruiter/view/all')]
    public function getRecruiterJobs() {
        $jobs = $this->jobRepository->findBy(['author' => $this->currentUser]);
        $job_res = [];
        foreach ($jobs as $job) {
            $job_res[] = $job->toArrayWithResumes();
        }
        return $this->json($job_res, Response::HTTP_OK);
    }

    public function getJobDecodedData(Request $request) {
        $decoded = json_decode($request->getContent());
        $title = $decoded->title;
        $description = $decoded->description ?? null;
        $type = $decoded->type ?? null;
        $requirements = $decoded->requirements ?? null;
        $category = $decoded->category ?? null;
        $location = $decoded->location ?? null;
        $salary = $decoded->salary ?? null;
        $company = $decoded->company ?? null;
        return [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'requirements' => $requirements,
            'category' => $category,
            'location' => $location,
            'salary' => $salary,
            'company' => $company
        ];
    }
}
