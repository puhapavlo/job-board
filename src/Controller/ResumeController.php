<?php

namespace App\Controller;

use App\Entity\Resume;
use App\Entity\User;
use App\Repository\JobRepository;
use App\Repository\ResumeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/resume')]
class ResumeController extends AbstractController
{
    private User|null $currentUser;

    private ResumeRepository $resumeRepository;

    private JobRepository $jobRepository;


    public function __construct(TokenStorageInterface $tokenStorage, ResumeRepository $resumeRepository, JobRepository $jobRepository) {
        $this->currentUser = $tokenStorage->getToken()?->getUser();
        $this->resumeRepository = $resumeRepository;
        $this->jobRepository = $jobRepository;
    }


    #[Route('/seeker/add', name: 'app_resume_add')]
    public function add(Request $request): Response
    {
      $hasAccess = $this->isGranted('ROLE_JOB_SEEKER');
      $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
      if ($hasAccess) {
        $decoded = $this->getResumeDecodedData($request);
        $resume = new Resume();
        $resume->setSummary($decoded['summary']);
        $resume->setAuthor($this->currentUser);
        $resume->setPhone($decoded['phone']);
        $resume->setEducation($decoded['education']);
        $resume->setExperience($decoded['experience']);
        $resume->setSkills($decoded['skills']);
        $resume->setCertifications($decoded['certifications']);
        $this->resumeRepository->save($resume, true);
        return $this->json(["resume" => $resume->toArray()], Response::HTTP_CREATED);
      }
      return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/seeker/delete/{id}', name: 'app_resume_delete')]
    public function delete(int $id) {
      $hasAccess = $this->isGranted('ROLE_JOB_SEEKER');
      $resume = $this->resumeRepository->find($id);
      $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
      if ($resume->getAuthor()->getId() != $this->currentUser->getId()) {
          return $this->json(["message" => "You not author this resume."], Response::HTTP_FORBIDDEN);
      }
      if ($hasAccess) {
          $this->resumeRepository->remove($resume, true);
          return $this->json(["message" => "Delete successfully."], Response::HTTP_OK);
      }
      return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/seeker/update/{id}', name: 'app_resume_update')]
    public function update(Request $request, int $id) {
        $hasAccess = $this->isGranted('ROLE_JOB_SEEKER');
        $resume = $this->resumeRepository->find($id);
        if (is_null($resume)) {
            return $this->json(["message" => "Resume not found."], Response::HTTP_NOT_FOUND);
        }
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        if ($resume->getAuthor()->getId() != $this->currentUser->getId()) {
            return $this->json(["message" => "You not author this resume."], Response::HTTP_FORBIDDEN);
        }
        if ($hasAccess) {
            $decoded = $this->getResumeDecodedData($request);
            if (!is_null($decoded['phone'])) {
                $resume->setPhone($decoded['phone']);
            }
            if (!is_null($decoded['summary'])) {
                $resume->setSummary($decoded['summary']);
            }
            if (!is_null($decoded['education'])) {
                $resume->setEducation($decoded['education']);
            }
            if (!is_null($decoded['experience'])) {
                $resume->setExperience($decoded['experience']);
            }
            if (!is_null($decoded['skills'])) {
                $resume->setSkills($decoded['skills']);
            }
            if (!is_null($decoded['certifications'])) {
                $resume->setCertifications($decoded['certifications']);
            }
            $this->resumeRepository->save($resume, TRUE);
            return $this->json(["resume" => $resume->toArray()], Response::HTTP_OK);
        }
        return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/view/{id}', name: 'app_resume_view', methods: ['GET'])]
    public function view(int $id, Request $request) {
        $resume = $this->resumeRepository->find($id);
        $jobs = $resume->getJobs();
        $resume_res = $resume->toArray();
        $resume_res['author']['picture'] = $resume_res['author']['picture'] ? $request->getSchemeAndHttpHost() . $resume_res['author']['picture'] : null;
        foreach ($jobs as $job) {
            if ($job->getAuthor()->getId() == $this->currentUser->getId()) {
                return $this->json(["resume" => $resume_res], Response::HTTP_OK);
            }
        }
        $hasAccess = $this->isGranted('ROLE_JOB_SEEKER');
        if (is_null($resume)) {
            return $this->json(["message" => "Resume not found."], Response::HTTP_NOT_FOUND);
        }
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        if ($resume->getAuthor()->getId() != $this->currentUser->getId()) {
            return $this->json(["message" => "You not author this resume."], Response::HTTP_FORBIDDEN);
        }
        if ($hasAccess) {
            return $this->json(["resume" => $resume_res], Response::HTTP_OK);
        }
        return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/send/{recipient_id}', name: 'app_resume_send')]
    public function sendResume(int $recipient_id) {
        $resume = $this->resumeRepository->findBy(['author' => $this->currentUser]);
        $job = $this->jobRepository->find($recipient_id);
        $resume = reset($resume);
        $resume->addJob($job);
        $this->resumeRepository->save($resume, true);
        $this->jobRepository->save($job, true);
        return $this->json(["message" => $resume->toArray()], Response::HTTP_OK);
    }

    #[Route('/attach/{job_id}/check')]
    public function checkAttachResume(int $job_id) {
        $resume = $this->resumeRepository->findBy(['author' => $this->currentUser]);
        if (empty($resume)) {
            return $this->json(['check' => false]);
        }
        $resume = reset($resume);
        $resume_jobs = $resume->getJobs();
        foreach ($resume_jobs as $resume_job) {
            if ($resume_job->getId() == $job_id) {
                return $this->json(['check' => true]);
            }
        }
        return $this->json(['check' => false]);
    }

    #[Route('/get/job_seeker')]
    public function getResumeForUser() {
        $resumes_res = [];
        $resumes = $this->resumeRepository->findBy(['author' => $this->currentUser]);
        foreach ($resumes as $resume) {
            $resumes_res[] = $resume->toArray();
        }
        return $this->json($resumes_res);
    }

    public function getResumeDecodedData(Request $request): array {
        $decoded = json_decode($request->getContent());
        $phone = $decoded->phone ?? null;
        $summary = $decoded->summary ?? null;
        $education = $decoded->education ?? null;
        $experience = $decoded->experience ?? null;
        $skills = $decoded->skills ?? null;
        $certifications = $decoded->certifications ?? null;
        return [
            'phone' => $phone,
            'summary' => $summary,
            'education' => $education,
            'experience' => $experience,
            'skills' => $skills,
            'certifications' => $certifications,
        ];
    }
}
