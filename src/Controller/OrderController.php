<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order',name:'app_order')]
    public function getProducts(EntityManagerInterface $em): Response
    {
        $products=$em->getRepository(Product::class)->findAll();
        //dd($employees);

        return $this->render('order/index.html.twig',['products'=>$products]);
    }

    #[Route('/clear/winkelwagen',name:'clear_winkelwagen')]
    public function clearWinkelwagen(EntityManagerInterface $em,Request $request): Response
    {
        $order=$request->getSession()->get('order');
        if($order) {
            $request->getSession('order')->clear();
            $this->addFlash('danger','winkelwagen leeg!');

        }
        return $this->redirectToRoute('app_order');
    }
    #[Route('/order/winkelwagen',name:'order_winkelwagen')]
    public function orderWinkelwagen(EntityManagerInterface $em,Request $request)
    {
        $order = $request->getSession()->get('order');
        if(!$order) {
            $this->addFlash('danger','Je hebt geen producten');
            return $this->redirectToRoute('app_order');
        }
        $order=new Order();
        $order->setDate(new \DateTime('now'));
        $order->setStatus('In behandeling');

        foreach ($order as $line) {
            $orderLine=new OrderLine();
            $product=$em->getRepository(Product::class)->find($line[0]);
            $orderLine->setProduct($product);
            $orderLine->setAmount($line[1]);
            $orderLine->setPurchase($order);
            $em->persist($orderLine);
        }

        $em->persist($order);
        $em->flush();
        $request->getSession('order')->clear();
        $this->addFlash('success','De bestelling is voltooid');
        return $this->redirectToRoute('app_order');

    }

    #[Route('/show/winkelwagen',name:'show_winkelwagen')]
    public function showWinkelwagen(EntityManagerInterface $em,Request $request)
    {
        $order = $request->getSession()->get('order');
        if(!$order) {
            $this->addFlash('danger','Je hebt geen producten');
            return $this->redirectToRoute('app_order');
        }

        $orderlinesLines=[];
        foreach ($order as $line) {
            $orderLine=new OrderLine();
            $product=$em->getRepository(Product::class)->find($line[0]);
            $orderLine->setProduct($product);
            $orderLine->setAmount($line[1]);
            $orderLines[]=$orderLine;
        }
        return $this->render('order/winkelwagen.html.twig',[
            'orderLines'=>$orderLines,
        ]);
    }
    #[Route('/makeorder/{id}',name:'app_makeorder')]
    public function makeOrder(EntityManagerInterface $em,Request $request, int $id): Response
    {
        $product=$em->getRepository(Product::class)->find($id);
        $form = $this->createFormBuilder()
            ->add('amount', IntegerType::class, [
                'required'=>true,
                'data'=>1,
                'label'=>'aantal'
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $session=$request->getSession();
        $form->handleRequest($request);
        if($form->isSubmitted() ) {
            //als de variabele order in de sesion array niet bestaat maak deze aan
            if(!$session->get('order')) {
                $session->set('order',[]);
            }
            $amount=$form->get('amount')->getData();
            //haal sessie op en voeg orderline toe
            $order=$session->get('order');
            $order[]=[$id,$amount];
            $session->set('order',$order);

            $this->addFlash('success','product toegevoegd!');
            return $this->redirectToRoute('app_order');
        }
        return $this->render('order/order.html.twig',[
            'product'=>$product,
            'form'=>$form
        ]);
    }

}
