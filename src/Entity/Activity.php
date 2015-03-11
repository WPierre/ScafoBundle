<?php

namespace WPierre\Scafo\ScafoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Activity
 *
 * @ORM\Table(name="activity")
 * @ORM\Entity
 */
class Activity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="instance_id", type="integer", nullable=false)
     */
    private $instanceId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="execution_date", type="datetime", nullable=false)
     */
    private $executionDate = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="output_file", type="string", length=200, nullable=false)
     */
    private $outputFile;

    /**
     * @var string
     *
     * @ORM\Column(name="from_files", type="string", length=512, nullable=true)
     */
    private $fromFiles;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=512, nullable=false)
     */
    private $comment;



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
     * Set instanceId
     *
     * @param integer $instanceId
     * @return Activity
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * Get instanceId
     *
     * @return integer 
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set executionDate
     *
     * @param \DateTime $executionDate
     * @return Activity
     */
    public function setExecutionDate($executionDate)
    {
        $this->executionDate = $executionDate;

        return $this;
    }

    /**
     * Get executionDate
     *
     * @return \DateTime 
     */
    public function getExecutionDate()
    {
        return $this->executionDate;
    }

    /**
     * Set outputFile
     *
     * @param string $outputFile
     * @return Activity
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    /**
     * Get outputFile
     *
     * @return string 
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * Set fromFiles
     *
     * @param string $fromFiles
     * @return Activity
     */
    public function setFromFiles($fromFiles)
    {
        $this->fromFiles = $fromFiles;

        return $this;
    }

    /**
     * Get fromFiles
     *
     * @return string 
     */
    public function getFromFiles()
    {
        return $this->fromFiles;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Activity
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }
}
