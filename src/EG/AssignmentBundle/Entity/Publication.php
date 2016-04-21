<?php
/**
 * Created by PhpStorm.
 * User: Emeric
 * Date: 10/04/2016
 * Time: 12:21
 */

namespace EG\AssignmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Publication
 *
 * @ORM\Table(name="publication")
 * @ORM\Entity(repositoryClass="EG\AssignmentBundle\Repository\PublicationRepository")
 */
class Publication
{
    /**
     * ID unique for each Publication
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * path to the pdf file
     *
     * @var string
     *
     * @ORM\Column(name="pdf", type="string", length=255, nullable=true, unique=true)
     */
    private $pdfUrl;

    /**
     * PDF file
     *
     * @var file
     */
    private $pdf;

    /**
     * Submission's date
     *
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetimetz")
     */
    private $date;

    /**
     * path to the text file
     *
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255)
     */
    private $fileUrl;

    /**
     * Text file
     *
     * @var file
     */
    private $file;

    /**
     * Publication's author
     *
     * @var member
     *
     * @ORM\OneToOne(targetEntity="EG\AssignmentBundle\Entity\Member")
     */
    private $author;

    /**
     * Publication of this project
     *
     * @var project
     *
     * @ORM\ManyToOne(targetEntity="EG\AssignmentBundle\Entity\Project")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * Publication constructor.
     * set the date value with the current date
     */
    public function __construct()
    {
        $this->date = new \Datetime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pdf
     *
     * @param string $pdfUrl
     *
     * @return Publication
     */
    public function setPdfUrl($pdfUrl)
    {
        $this->pdfUrl = $pdfUrl;

        return $this;
    }

    /**
     * Get pdf
     *
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->pdfUrl;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Publication
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set file
     *
     * @param string $fileUrl
     *
     * @return Publication
     */
    public function setFileUrl($fileUrl)
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFileUrl()
    {
        return $this->fileUrl;
    }

    /**
     * Set author
     *
     * @param \EG\AssignmentBundle\Entity\Member $author
     *
     * @return Publication
     */
    public function setAuthor(\EG\AssignmentBundle\Entity\Member $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \EG\AssignmentBundle\Entity\Member
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set project
     *
     * @param \EG\AssignmentBundle\Entity\Project $project
     *
     * @return Publication
     */
    public function setProject(\EG\AssignmentBundle\Entity\Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \EG\AssignmentBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Get PDF
     *
     * @return mixed
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * set PDF
     *
     * @param UploadedFile|null $pdf
     * @return $this
     */
    public function setPdf(UploadedFile $pdf = null)
    {
        $this->pdf = $pdf;

        return $this;
    }

    /**
     * Get File
     *
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * set File
     *
     * @param UploadedFile|null $file
     * @return $this
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Upload the PDF file in the good directory
     */
    public function uploadPDF()
    {
        if (null === $this->pdf) {
            return;
        }

        $name = $this->pdf->getClientOriginalName();
        $this->pdf->move($this->getUploadRootDir(true), $name);
        $this->pdfUrl = $name;
    }

    /**
     * Uploads the text file in the good directory
     */
    public function uploadFile()
    {
        if (null === $this->file) {
            return;
        }

        $name = $this->file->getClientOriginalName();
        $this->file->move($this->getUploadRootDir(false), $name);
        $this->fileUrl = $name;
    }

    /**
     * Check if the directory to stock the pdf exist
     * if it doesn't create the directory
     *
     * @return string
     */
    public function getUploadDirPdf()
    {
        $fs = new Filesystem();

        if(!$fs->exists('uploads/publication/'.$this->project->getId().'/pdf')){
            $fs->mkdir('uploads/publication/'.$this->project->getId().'/pdf', 0777);
        }
        return 'uploads/publication/'.$this->project->getId().'/pdf';
    }

    /**
     * Check if the directory to stock the file exist
     * if it doesn't create the directory
     *
     * @return string
     */
    public function getUploadDirFile()
    {
        $fs = new Filesystem();

        if(!$fs->exists('uploads/publication/'.$this->project->getId().'/file')){
            $fs->mkdir('uploads/publication/'.$this->project->getId().'/file', 0777);
        }
        return 'uploads/publication/'.$this->project->getId().'/file';
    }

    /**
     * Delete pdf
     */
    public function deletePdf(){

        $fs = new Filesystem();
        $fs->remove($this->getUploadDirPdf().'/'.$this->pdfUrl);

    }

    /**
     * Delete file
     */
    public function deleteFile(){

        $fs = new Filesystem();
        $fs->remove($this->getUploadDirFile().'/'.$this->fileUrl);

    }

    /**
     * return the good path of directory in function of
     * it's a pdf or a text file
     *
     * @return string
     */
    protected function getUploadRootDir($pdf)
    {
        if($pdf){
            return __DIR__.'/../../../../web/'.$this->getUploadDirPdf();
        }
        else{
            return __DIR__.'/../../../../web/'.$this->getUploadDirFile();
        }
    }

}
