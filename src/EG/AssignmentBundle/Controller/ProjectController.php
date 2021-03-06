<?php
/**
 * Created by PhpStorm.
 * User: Emeric
 * Date: 11/04/2016
 * Time: 11:33
 */

namespace EG\AssignmentBundle\Controller;


use Doctrine\ORM\EntityRepository;
use EG\AssignmentBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller of the Project entity
 *
 * Class ProjectController
 * @package EG\AssignmentBundle\Controller
 */
class ProjectController extends Controller
{
    /**
     * Load the project page with the id = $id with all the information or the page that list the projects
     *
     * @param $id
     * @return mixed
     */
    public function viewAction($id = null){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');

        if(null != $id){

            $repositoryPublication = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
            $publications =  $repositoryPublication->findBy(array('project' => $id));

            $project = $repository->find($id);
            return $this->render('EGAssignmentBundle:Project:view.html.twig', array(
                'project' => $project,
                'publications' => $publications
            ));
        }

        $project = $repository->findBy(array('past' => 0));
        return $this->render('EGAssignmentBundle:Project:viewall.html.twig', array(
            'projects' => $project
        ));


    }

    /**
     * Add a new project in the database
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER')")
     */
    public function addAction(Request $request){

        $project = new Project();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($project)
            ->add('name',           TextType::class)
            ->add('description',    TextareaType::class)
            ->add('members',        EntityType::class, array(
                'class' => 'EGAssignmentBundle:Member',
                'choice_label' => 'Name',
                'multiple' => true,
                'em' => $em,
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('m')
                        ->where('m.expired = 0');
                }
            ))
            ->add('save',           SubmitType::class, array('label' => 'New project'))
            ->getForm();


        $form->handleRequest($request);

        if($form->isValid()){

            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $project->setCreator($user);


            $em->persist($project);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Project create.');
            return $this->redirect($this->generateUrl('eg_project_view', array('id' => $project->getId())));

        }

        return $this->render('EGAssignmentBundle:Project:add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * Edit information of the project with the id = $id
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER')")
     */
    public function editAction($id, Request $request){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');

        $project = $repository->find($id);

        if ($this->container->get('security.token_storage')->getToken()->getUser() != $project->getCreator() and
            !in_array('ROLE_ADMIN',$this->container->get('security.token_storage')->getToken()->getUser()->getRoles())){
            throw new AccessDeniedException('Access Denied ! Not your project');
        }

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Member');
        $membersList = $repository->findAll();
        $members = $project->getMembers();
        $membersArray = array();
        $i = 1;

        foreach($members as $member){
            foreach($membersList as $all){
                if($member->getId() === $all->getId()){
                    $membersArray[$i] = $all;
                    $i++;
                }
            }
        }

        $project->setMembers($membersArray);

        $form = $this->createFormBuilder($project)
            ->add('name',           TextType::class)
            ->add('description',    TextareaType::class)
            ->add('members',         EntityType::class, array(
                'class' => 'EGAssignmentBundle:Member',
                'choice_label' => 'Name',
                'multiple' => true,
                'required' => false,
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('m')
                        ->where('m.expired = 0');
                }
            ))
            ->add('imgFile',        FileType::class, array('required' => false))
            ->add('save',           SubmitType::class, array('label' => 'Edit project'))
            ->getForm();


        $form->handleRequest($request);

        if($form->isValid()){

            $em = $this->getDoctrine()->getManager();

            $project->upload();

            $em->persist($project);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Project update.');
            return $this->redirect($this->generateUrl('eg_project_view', array('id' => $project->getId())));

        }

        return $this->render('EGAssignmentBundle:Project:add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * Set at true the past value with the id = $id or load the page with all the past projects
     *
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER')")
     */
    public function pastAction($id = null)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');

        if($id != null) {

            $project = $repository->find($id);

            if ($this->container->get('security.token_storage')->getToken()->getUser() != $project->getCreator() and
                !in_array('ROLE_ADMIN',$this->container->get('security.token_storage')->getToken()->getUser()->getRoles())){
                throw new AccessDeniedException('Access Denied ! Not your project');
            }

            $project->setPast(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

        }

        $projects = $repository->findBy(array('past' => 1));

        return $this->render('EGAssignmentBundle:Project:viewall.html.twig', array(
            'projects' => $projects
        ));

    }

}
