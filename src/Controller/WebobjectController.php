<?php

namespace App\Controller;

use App\Entity\Webobject;
use App\Form\WebobjectType;
use App\Repository\WebobjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/webobject")
 */
class WebobjectController extends Controller
{
    /**
     * @Route("/", name="webobject_index", methods="GET")
     */
    public function index(WebobjectRepository $webobjectRepository): Response
    {
        return $this->render('webobject/index.html.twig', ['webobjects' => $webobjectRepository->findAll()]);
    }

    /**
     * @Route("/new", name="webobject_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $webobject = new Webobject();
        $form = $this->createForm(WebobjectType::class, $webobject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($webobject);
            $em->flush();

            return $this->redirectToRoute('webobject_index');
        }

        return $this->render('webobject/new.html.twig', [
            'webobject' => $webobject,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="webobject_show", methods="GET")
     */
    public function show(Webobject $webobject): Response
    {
        echo get_class($webobject);
        return $this->render('webobject/show.html.twig', ['webobject' => $webobject]);
    }

    /**
     * @Route("/{id}/edit", name="webobject_edit", methods="GET|POST")
     */
    public function edit(Request $request, Webobject $webobject): Response
    {
        $form = $this->createForm(WebobjectType::class, $webobject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('webobject_edit', ['id' => $webobject->getId()]);
        }

        return $this->render('webobject/edit.html.twig', [
            'webobject' => $webobject,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="webobject_delete", methods="DELETE")
     */
    public function delete(Request $request, Webobject $webobject): Response
    {
        if ($this->isCsrfTokenValid('delete'.$webobject->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($webobject);
            $em->flush();
        }

        return $this->redirectToRoute('webobject_index');
    }
}
