<?php
/**
 * Created by PhpStorm.
 * User: Emeric
 * Date: 14/04/2016
 * Time: 21:15
 */

namespace EG\AssignmentBundle\Controller;


use EG\AssignmentBundle\Entity\Publication;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Controller for the Publication entity
 *
 * Class PublicationController
 * @package EG\AssignmentBundle\Controller
 */
class PublicationController extends Controller
{
    /**
     * Load the publication page with the id = $id with all the information or the page that list the publications
     *
     * @param $id
     * @return mixed
     */
    public function viewAction($id = null){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');

        if(null != $id){
            $publication = $repository->find($id);
            return $this->render('EGAssignmentBundle:Publication:view.html.twig', array(
                'publication' => $publication
            ));
        }
        else{
            $publications = $repository->findAll();
            return $this->render('EGAssignmentBundle:Publication:viewall.html.twig', array(
                'publications' => $publications
            ));
        }

    }

    /**
     * Add a publication to the database
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER')")
     */
    public function addAction(Request $request){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');
        $projectList = $repository->findBy(array('past' => 0));

        $publication = new Publication();

        $form = $this->createFormBuilder($publication)
            ->add('project', ChoiceType::class, array(
                'choices' => $projectList,
                'choice_label' => 'Name'
            ))
            ->add('pdf',            FileType::class, array('required' => false))
            ->add('file',           FileType::class)
            ->add('save',           SubmitType::class, array('label' => 'New publication'))
            ->getForm();


        $form->handleRequest($request);

        if($form->isValid()){
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $publication->setAuthor($user);
            $publication->uploadPDF();
            $publication->uploadFile();

            $em = $this->getDoctrine()->getManager();
            $em->persist($publication);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Publication create.');
            return $this->redirect($this->generateUrl('eg_project_view', array('id' => $publication->getProject()->getId())));

        }

        return $this->render('EGAssignmentBundle:Publication:add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * Edit the publication with the id = $id
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER')")
     */
    public function editAction($id, Request $request){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');
        $projectList = $repository->findBy(array('past' => 0));

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
        $publication = $repository->find($id);

        $form = $this->createFormBuilder($publication)
            ->add('project',    ChoiceType::class, array(
                'choices' => $projectList,
                'choice_label' => 'Name'
            ))
            ->add('pdf',        FileType::class, array('required' => false))
            ->add('file',       FileType::class, array('required' => false))
            ->add('save',       SubmitType::class, array('label' => 'Edit publication'))
            ->getForm();


        $form->handleRequest($request);

        if($form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $publication->uploadPDF();
            $publication->uploadFile();
            $em->persist($publication);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Project create.');
            return $this->redirect($this->generateUrl('eg_publication_view', array('id' => $publication->getId())));

        }

        return $this->render('EGAssignmentBundle:Publication:add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * delete the publication with the id = $id
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER')")
     */
    public function deleteAction($id, Request $request){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
        $publication = $repository->find($id);

        $publication->deletePdf();
        $publication->deleteFile();

        $em = $this->getDoctrine()->getManager();
        $em->remove($publication);
        $em->flush();

        $publications = $repository->findAll();
        return $this->render('EGAssignmentBundle:Publication:viewall.html.twig', array(
            'publications' => $publications
        ));
    }

}
