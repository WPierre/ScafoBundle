<?php

namespace Wpierre\Scafo\ScafoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * ConfigInstance
 *
 * @ORM\Table(name="config_instance")
 * @ORM\Entity
 */
class ConfigInstance
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
     * @ORM\Column(name="instance_name", type="string", length=20, nullable=false)
     */
    private $instanceName;

    /**
     * @ORM\OneToMany(targetEntity="Parameter", mappedBy="instance")
     **/
    private $parameters;

    /**
     * @ORM\OneToMany(targetEntity="Filter", mappedBy="instance")
     **/
    private $filters;
    
    private $param_list;

    public function __construct() {
        $this->parameters = new ArrayCollection();
        $this->filters = new ArrayCollection();
    }

    public function getConfig($param_name){
        if ($this->param_list == null){
            $this->param_list = $this->parameters;
        }
        $retour = null;
        foreach ($this->param_list as $parameter){
            if ($parameter->getParamName() == $param_name){
                $retour = $parameter->getValue();
                break;
            }
        }
        return $retour;
    }
    
    public function getParameters(){
        return $this->parameters;
    }
    
    public function getFilters(){
        return $this->filters;
    }
    
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
     * Set instanceName
     *
     * @param string $instanceName
     * @return ConfigInstance
     */
    public function setInstanceName($instanceName)
    {
        $this->instanceName = $instanceName;

        return $this;
    }

    /**
     * Get instanceName
     *
     * @return string 
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }
}
