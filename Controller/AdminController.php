<?php

namespace Owp\OwpCrudAdmin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AdminController extends AbstractController
{
    use TargetPathTrait;

    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/admin/format/list", name="admin_format_list")
     */
    public function admin(FormatRepository $formatRepository)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->getTargetPath($this->get('session'), 'main'));
        }

        return $this->render('format/admin.html.twig', [
            'fields' => ['label' => 'Libellé'],
            'entities' => $formatRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/format/{format}", name="admin_format_form", requirements={"format"="\d+"})
     */
    public function form(Request $request, ?Format $format = null)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            //return $this->redirect($this->getTargetPath($this->get('session'), 'main'));
        }

        $format = $format ?? new Format();
        $form = $this->createForm(FormatFormType::class, $format);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dispatcher->dispatch(new GenericEvent($format), ($format->getId() ? 'app.entity.pre_update' : 'app.entity.pre_persist'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($format);
            $entityManager->flush();
        }

        return $this->render('format/form.html.twig', [
            'format' => $format,
            'formatForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/format/{format}/delete", name="admin_format_delete", requirements={"format"="\d+"})
     */
    public function delete(Format $format)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->getTargetPath($this->get('session'), 'main'));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($format);
        $entityManager->flush();

        return $this->redirect($this->getTargetPath($this->get('session'), 'main'));
    }
}
