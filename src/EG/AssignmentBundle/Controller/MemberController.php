<?php
/**
 * Created by PhpStorm.
 * User: Emeric
 * Date: 11/04/2016
 * Time: 11:33
 */

namespace EG\AssignmentBundle\Controller;


use EG\AssignmentBundle\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for the Member entity
 *
 * Class MemberController
 * @package EG\AssignmentBundle\Controller
 */
class MemberController extends Controller
{
    /**
     * Load the member page with the id = $id with all the information or the page that list the members
     *
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($id = null){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Member');

        if(null != $id) {

            $member = $repository->find($id);

            $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');
            $creator = $repository->findBy(array('creator' => $member));
            $projects = $repository->findAll();

            $projectList = array();
            $i = 1;

            foreach($projects as $project){
                $projectMember = $project->getMembers();
                    foreach($projectMember as $memberList){
                        if($memberList->getId() == $id){
                            $projectList[$i] = $project;
                            $i++;
                        }
                    }
            }

            $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
            $publication = $repository->findBy(array('author' => $member));

            return $this->render('EGAssignmentBundle:Member:view.html.twig', array(
                'member' => $member,
                'creators' => $creator,
                'projects' => $projectList,
                'publications' => $publication
            ));
        }

        $members = $repository->findBy(array('expired' => 0));
        return $this->render('EGAssignmentBundle:Member:viewall.html.twig', array(
            'members' => $members
        ));


    }

    /**
     * Add a new member in the database
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function addAction(Request $request){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Member');
        $supervisorList = $repository->findBy(array('student' => 0,
                                                    'expired' => 0));

        $member = new Member();

        $form = $this->createFormBuilder($member)
            ->add('first_name', TextType::class)
            ->add('last_name',  TextType::class)
            ->add('username',   TextType::class)
            ->add('password',   PasswordType::class)
            ->add('email',      EmailType::class)
            ->add('student',    CheckboxType::class, array('required' => false))
            ->add('supervisor', ChoiceType::class, array(
                'choices' => $supervisorList,
                'choice_label' => 'Name'
            ))
            ->add('save',       SubmitType::class, array('label' => 'Add member'))
            ->getForm();


        $form->handleRequest($request);

        if($form->isValid()){

            if($repository->find(1) == null){
                $member->setRoles(array('ROLE_ADMIN'));
            }
            elseif($member->getStudent()){
                $member->setRoles(array('ROLE_STUDENT'));
            }
            else{
                $member->setRoles(array('ROLE_MEMBER'));
            }
            $em = $this->getDoctrine()->getManager();
            $member->setPassword(password_hash($member->getPassword(), PASSWORD_BCRYPT));

            $member->setEnabled(true);
            $em->persist($member);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Member add.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));

        }

        return $this->render('EGAssignmentBundle:Member:add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * Edit the informations of the member where the id = $id
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER') or has_role('ROLE_STUDENT')")
     */
    public function editAction($id, Request $request){

        if ($this->container->get('security.token_storage')->getToken()->getUser()->getId() != $id and
            !in_array('ROLE_ADMIN',$this->container->get('security.token_storage')->getToken()->getUser()->getRoles())){
            throw new AccessDeniedException('Access Denied ! Not your account');
        }

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Member');
        $supervisorList = $repository->findBy(array('student' => 0,
                                                    'expired' => 0));

        $member = $repository->find($id);

        $pass = $member->getPassword();

        $form = $this->createFormBuilder($member)
            ->add('username',   TextType::class)
            ->add('password',   PasswordType::class, array('required' => false))
            ->add('email',      EmailType::class)
            ->add('student',    CheckboxType::class, array('required' => false))
            ->add('supervisor', ChoiceType::class, array(
                'choices' => $supervisorList,
                'choice_label' => 'Name'
            ))
            ->add('imgFile',      FileType::class, array('required' => false))
            ->add('save',       SubmitType::class, array('label' => 'Validate'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            if($member->getPassword() == ""){
                $member->setPassword($pass);
            }
            else{
                $member->setPassword(password_hash($member->getPassword(), PASSWORD_BCRYPT));
            }
            $em = $this->getDoctrine()->getManager();
            $member->upload();
            $em->persist($member);
            $em->flush();

            return $this->redirect($this->generateUrl('eg_member_view', array(
                'id' => $member->getId()
            )));
        }

        return $this->render('EGAssignmentBundle:Member:edit.html.twig', array(
            'form' => $form->createView(),
            'member' => $member
        ));

    }

    /**
     * Set expired at true for the member $id or load the page with all the past members
     *
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MEMBER') or has_role('ROLE_STUDENT')")
     */
    public function pastAction($id = null){

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Member');

        if($id != null) {

            if ($this->container->get('security.token_storage')->getToken()->getUser()->getId() != $id and
                !in_array('ROLE_ADMIN',$this->container->get('security.token_storage')->getToken()->getUser()->getRoles())){
                throw new AccessDeniedException('Access Denied ! Not your account');
            }

            $member = $repository->find($id);
            $member->setExpired(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();

        }

        $members = $repository->findBy(array('expired' => 1));

        return $this->render('EGAssignmentBundle:Member:viewall.html.twig', array(
            'members' => $members
        ));
    }
}