<?php

namespace App\Controller;

use App\Entity\Resume;
use App\Entity\User;
use App\Repository\ResumeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/seeker/resume')]
class ResumeController extends AbstractController
{
    private User $currentUser;

    private ResumeRepository $resumeRepository;


    public function __construct(TokenStorageInterface $tokenStorage, ResumeRepository $resumeRepository) {
        $this->currentUser = $tokenStorage->getToken()->getUser();
        $this->resumeRepository = $resumeRepository;
    }


    #[Route('/add', name: 'app_resume_add')]
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

    #[Route('/delete/{id}', name: 'app_resume_delete')]
    public function delete(int $id) {
      $hasAccess = $this->isGranted('ROLE_JOB_SEEKER');
      $resume = $this->resumeRepository->find($id);
      $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
      if ($resume->getAuthor()->getId() != $this->currentUser->getId()) {
          return $this->json(["message" => "You not author this resume."], Response::HTTP_FORBIDDEN);
      }
      if ($hasAccess) {
          $this->resumeRepository->remove($resume);
          return $this->json(["message" => "Delete successfully."], Response::HTTP_OK);
      }
      return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
    }

    #[Route('/update/{id}', name: 'app_resume_update')]
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

    #[Route('/view/{id}', name: 'app_resume_view')]
    public function view(int $id) {
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
            return $this->json(["resume" => $resume->toArray()], Response::HTTP_OK);
        }
        return $this->json(["message" => "Access denied."], Response::HTTP_FORBIDDEN);
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

    #[Route('/send/{id}/{recipient_id}', name: 'app_resume_view')]
    public function sendResume(int $id, int $recipient_id) {
        
    }
}
