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
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="EG\AssignmentBundle\Repository\ProjectRepository")
 */
class Project
{
    /**
     * ID unique for each Project
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Project's name
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * Project's description
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, unique=true)
     */
    private $description;

    /**
     * Currently project or not
     *
     * @var boolean
     *
     * @ORM\Column(name="past", type="boolean")
     */
    private $past = false;

    /**
     * path to the project's image
     *
     * @var string
     *
     * @ORM\Column(name="imageUrl", type="string", length=255)
     */
    private $imageUrl = "../project.png";

    /**
     * Project's image
     *
     * @var file
     */
    private $imgFile;

    /**
     * Project's creator
     *
     * @var member
     *
     * @ORM\ManyToOne(targetEntity="EG\AssignmentBundle\Entity\Member")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * Project's members
     *
     * @var array
     *
     * @ORM\Column(name="members", type="array")
     */
    private $members;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set past
     *
     * @param boolean $past
     *
     * @return Project
     */
    public function setPast($past)
    {
        $this->past = $past;

        return $this;
    }

    /**
     * Get past
     *
     * @return boolean
     */
    public function getPast()
    {
        return $this->past;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     *
     * @return Project
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set members
     *
     * @param array $members
     *
     * @return Project
     */
    public function setMembers($members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * Get members
     *
     * @return array
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set creator
     *
     * @param \EG\AssignmentBundle\Entity\Member $creator
     *
     * @return Project
     */
    public function setCreator(\EG\AssignmentBundle\Entity\Member $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \EG\AssignmentBundle\Entity\Member
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * get imgFile
     *
     * @return file
     */
    public function getImgFile()
    {

        return $this->imgFile;

    }

    /**
     * set imgFile
     *
     * @param UploadedFile $imgFile
     * @return $this
     */
    public function setImgFile(UploadedFile $imgFile)
    {
        $this->imgFile = $imgFile;
        return $this;
    }

    /**
     * Upload the imgFile on the good directory
     */
    public function upload()
    {
        if (null === $this->imgFile) {
            return;
        }

        $name = $this->imgFile->getClientOriginalName();;
        $this->imgFile->move($this->getUploadRootDir(), $name);
        $this->imageUrl = $name;
    }

    /**
     * Check if the directory to stock the image exist
     * if it exist delete the directory to avoid to stock every images
     * and create the directory
     *
     * @return string
     */
    public function getUploadDir()
    {
        $fs = new Filesystem();

        if($fs->exists('uploads/projectImg/'.$this->id)) {
            $fs->remove(array('uploads/projectImg/'.$this->id));
        }

        $fs->mkdir('uploads/projectImg/'.$this->id, 0777);
        return 'uploads/projectImg/'.$this->id;

    }

    /**
     * Return the good path of directory
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

}
