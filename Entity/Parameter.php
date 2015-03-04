<?php

namespace Wpierre\Scafo\ScafoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter
 *
 * @ORM\Table(name="parameter", uniqueConstraints={@ORM\UniqueConstraint(name="unique_index", columns={"id", "param_name"})}, indexes={@ORM\Index(name="instance_id", columns={"instance_id"})})
 * @ORM\Entity
 */
class Parameter
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
     * @var string
     *
     * @ORM\Column(name="param_name", type="string", length=30, nullable=false)
     */
    private $paramName;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=100, nullable=true)
     */
    private $value;

    /**
     * @var \ConfigInstance
     *
     * @ORM\ManyToOne(targetEntity="ConfigInstance", inversedBy="parameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="instance_id", referencedColumnName="id")
     * })
     */
    private $instance;



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
     * Set paramName
     *
     * @param string $paramName
     * @return Parameter
     */
    public function setParamName($paramName)
    {
        $this->paramName = $paramName;

        return $this;
    }

    /**
     * Get paramName
     *
     * @return string 
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Parameter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set instance
     *
     * @param \Wpierre\Scafo\ScafoBundle\Entity\ConfigInstance $instance
     * @return Parameter
     */
    public function setInstance(\Wpierre\Scafo\ScafoBundle\Entity\ConfigInstance $instance = null)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * Get instance
     *
     * @return \Wpierre\Scafo\ScafoBundle\Entity\ConfigInstance 
     */
    public function getInstance()
    {
        return $this->instance;
    }
}
