<?php
/**
 * Created by PhpStorm.
 * User: Emeric
 * Date: 07/04/2016
 * Time: 11:33
 */

namespace EG\AssignmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for the basics pages
 *
 * Class AssignmentController
 * @package EG\AssignmentBundle\Controller
 */
class AssignmentController extends Controller
{

    /**
     * Load the home page with all informations
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Project');
        $projects = $repository->findBy(array('past' => 0));

        $projectList = array();
        $publicationList = array();

        $numberProjects = sizeof($projects);

        if ($numberProjects > 5)
            for ($i = 1; $i < 6; $i++) {
                $projectList[$i] = $projects[$numberProjects - $i];
            }
        else {
            for ($i = 1; $i <= $numberProjects; $i++) {
                $projectList[$i] = $projects[$numberProjects - $i];
            }
        }

        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
        $publications = $repository->findAll();

        $numberPublications = sizeof($publications);

        if ($numberPublications > 5)
            for ($i = 1; $i < 6; $i++) {
                $publicationList[$i] = $publications[$numberPublications - $i];
            }
        else {
            for ($i = 1; $i <= $numberPublications; $i++) {
                $publicationList[$i] = $publications[$numberPublications - $i];
            }
        }

        return $this->render('EGAssignmentBundle:Assignment:index.html.twig', array(
            'projects' => $projectList,
            'publications' => $publicationList
        ));

    }

    /**
     * Load the contact page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contactAction()
    {
        return $this->render('EGAssignmentBundle:Assignment:contact.html.twig');
    }

    /**
     * Create the download link to the pdf publication
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function downloadPdfAction($id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
        $publication = $repository->find($id);
        $file = $publication->getPdfUrl();

        $path = 'uploads/publication/' . $publication->getProject()->getId() . '/pdf/';

        $fs = new Filesystem();

        if ($fs->exists($path . $file)) {

            $response = new Response();
            $response->setContent(file_get_contents($path . $file));
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-disposition', 'filename=' . $file);

            return $response;

        } else {
            throw new NotFoundHttpException('PDF not found !');
        }
    }

    /**
     * Create the download link to the file of where to get the document
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function downloadFileAction($id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('EGAssignmentBundle:Publication');
        $publication = $repository->find($id);
        $file = $publication->getFileUrl();

        $path = 'uploads/publication/' . $publication->getProject()->getId() . '/file/';

        $fs = new Filesystem();

        if ($fs->exists($path . $file)) {

            $response = new Response();
            $response->setContent(file_get_contents($path . $file));
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-disposition', 'filename=' . $file);

            return $response;
        }
        else{
            throw new NotFoundHttpException('PDF not found !');
        }
    }
}
