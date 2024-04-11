<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky', name: 'app_lucky')]
    public function index(): Response
    {
        return $this->render('lucky/index.html.twig', [
            'controller_name' => 'LuckyController',
        ]);
    }

    #[Route('/lucky/number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return $this->render('lucky/random.html.twig',[
            'number'=>$number
        ]);
    }
    #[Route('/employee',name:'app_employee')]
    public function getEmployees(EntityManagerInterface $em): Response
    {
        $employees=$em->getRepository(Employee::class)->findAll();
        //dd($employees);

        return $this->render('lucky/employees.html.twig',[
            'employees'=>$employees
        ]);
    }
    #[Route('/employee/add',name:'add_employee')]
    public function addEmployees(EntityManagerInterface $em, Request $request): Response
    {
        $employee = new Employee();
        // ...

        $form = $this->createForm(EmployeeType::class, $employee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $employee = $form->getData();
            //dd($employee);
            $em->persist($employee);
            $em->flush();

            // ... perform some action, such as saving the task to the database

            return $this->redirectToRoute('app_employee');
        }
        return $this->render('lucky/new.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/employee/delete/{id}',name:'delete_employee')]
    public function deleteEmployees(EntityManagerInterface $em,int $id, Request $request): Response
    {
        $employee=$em->getRepository(Employee::class)->find($id);
        $em->remove($employee);
        $em->flush();
        $name=$employee->getLname();
        $this->addFlash('success',"$name is verwijderd" );
        return $this->redirectToRoute('app_employee');
    }

    #[Route('/employee/update/{id}',name:'update_employee')]
    public function updateEmployees(EntityManagerInterface $em,int $id, Request $request): Response
    {
        $employee = $em->getRepository(Employee::class)->find($id);
        //dd($employee);
        // ...

        $form = $this->createForm(EmployeeType::class, $employee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $employee = $form->getData();
            //dd($employee);
            $em->persist($employee);
            $em->flush();

            // ... perform some action, such as saving the task to the database

            return $this->redirectToRoute('app_employee');
        }
        return $this->render('lucky/new.html.twig', [
            'form' => $form,
        ]);
    }


}
