<?php

namespace Wpierre\Scafo\ScafoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Filter
 *
 * @ORM\Table(name="filter")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Wpierre\Scafo\ScafoBundle\Repository\FilterRepository")
 */
class Filter
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
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="order_number", type="integer", nullable=true)
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="condition_text", type="string", length=255, nullable=false)
     */
    private $conditionText;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=254, nullable=false)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="filename_formater", type="string", length=255, nullable=true)
     */
    private $filenameFormater;

    /**
     * @var integer
     *
     * @ORM\Column(name="condition_type", type="integer", nullable=false)
     */
    private $conditionType;

    /**
     * @var string
     *
     * @ORM\Column(name="doc_date_formater", type="string", length=255, nullable=true)
     */
    private $docDateFormater;


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
     * Get idFilter
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Filter
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set orderNumber
     *
     * @param integer $orderNumber
     * @return Filter
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Get orderNumber
     *
     * @return integer 
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * Set conditionText
     *
     * @param string $conditionText
     * @return Filter
     */
    public function setConditionText($conditionText)
    {
        $this->conditionText = $conditionText;

        return $this;
    }

    /**
     * Get conditionText
     *
     * @return string 
     */
    public function getConditionText()
    {
        return $this->conditionText;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Filter
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set filenameFormater
     *
     * @param string $filenameFormater
     * @return Filter
     */
    public function setFilenameFormater($filenameFormater)
    {
        $this->filenameFormater = $filenameFormater;

        return $this;
    }

    /**
     * Get filenameFormater
     *
     * @return string 
     */
    public function getFilenameFormater()
    {
        return $this->filenameFormater;
    }

    /**
     * Set conditionType
     *
     * @param integer $conditionType
     * @return Filter
     */
    public function setConditionType($conditionType)
    {
        $this->conditionType = $conditionType;

        return $this;
    }

    /**
     * Get conditionType
     *
     * @return integer 
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }

    /**
     * Set docDateFormater
     *
     * @param string $docDateFormater
     * @return Filter
     */
    public function setDocDateFormater($docDateFormater)
    {
        $this->docDateFormater = $docDateFormater;

        return $this;
    }

    /**
     * Get docDateFormater
     *
     * @return string 
     */
    public function getDocDateFormater()
    {
        return $this->docDateFormater;
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
    
     /**
     *  Search for the best filter that matches the document
     *  
     * @param string $text
     * return Filter
     */
    static public function getGoodFilter($text){
    	//lowercase the text (the filters are case unsensitive)
    	$text = strtolower($text);
    	//gather the filters
    	$filters = self::getDoctrine()
    	->getRepository('WpierreScafoScafoBundle:Filter')->createQueryBuilder('f')->orderBy('f.order_number', 'ASC');
    	
    	//see if we can find a filter that matches with the text
    	foreach ($filters as $filter){
    		if ($filter->doesFilterMatch($text)){
    			return $filter;
    		}
    	}
    	return null;    	
    }
    
    /**
     * See if filter matches the text
     * 
     * @param string $text
     * @return boolean
     */
    public function doesFilterMatch($text){
    	//$logger = $this->get('logger');
    	//$logger->info('Trying filter #'.$this->getIdFilter().' with condition "'.htmlentities($this->getCondition()).'" and type ='.$this->getConditionType());
    	
    	//lowercase the condition so the search is case unsensitive
    	$condition = strtolower($this->conditionText); 
    	if ($this->getConditionType() == 1){
    		//simple word search
    		if (strpos($text,$condition) !== false){
    		//	$logger->info('Filter #'.$this->getIdFilter().' matches the text, stopping the search.');
    			return true;
    		}
    	} elseif ($this->getConditionType() == 2){
    		return $this->DoesGoogleSyntaxMatch($text);
    	} elseif ($this->getConditionType() == 3){
    		
    	} else {
    		//non existant type, throw error
    		//$logger->err("There's a problem with filter with id=".$this->getIdFilter().'  its type is '.$this->getConditionType());
    	}
    	return false;
    }
    
    public function DoesGoogleSyntaxMatch($contenu){
        $requete = strtolower(trim($this->getConditionText()));
        $contenu = strtolower($contenu);
        //echo $requete;
        $termes_positifs = array();
        $termes_negatifs = array();

        //on cherche d'abord à isoler les textes en guillemets
        while (strpos($requete,'"') !== false){ //si on trouve au moins un guillemet
                //Le flag signe sert à marquer s'il y a un tiret devant l'expression
                $flag_signe = true;
                $pos_debut = strpos($requete,'"');
                $pos_fin = strpos($requete,'"',$pos_debut+1);
                if ($pos_fin === false){ die("Il y a un nombre impair de guillemets dans la requête. Merci de la corriger.");}
                //on regarde si la chaine encapsulée n'est pas précédée d'un tiret
                if ($pos_debut>0 && substr($requete,$pos_debut-1,1) == "-"){
                        $pos_debut--;
                        $flag_signe = false;
                }
                //on extrait la chaine trouvée
                $substring = substr($requete,$pos_debut,$pos_fin-$pos_debut+1);


                //echo "trouvé des guillemets aux positions $pos_debut et $pos_fin avec l'expression : *$substring*";
                //la chaine trouvée est supprimée de la requête
                $requete = str_replace($substring,"",$requete);
                //on trime le tout pour ne pas avoir de problème pour la suite
                $requete = trim($requete);
                //echo "nouvelle requete : *".$requete."*";

                //traitement de la chaine extraite
                if (!$flag_signe){
                        $termes_negatifs[] = substr($substring,2,strlen($substring)-3);
                } else {
                        $termes_positifs[] = substr($substring,1,strlen($substring)-2);
                }

        }
        //ensuite on prend le reste
        $termes_restants = explode(" ",$requete);
        foreach($termes_restants as $terme){
                if ($terme != ""){
                        if (substr($terme,0,1) == "-"){
                                $termes_negatifs[] = substr($terme,1,strlen($terme)-1);
                        } else {
                                $termes_positifs[] = $terme;
                        }
                }
        }

        //on affiche le résultat
        //echo "termes positifs :";
        //var_dump($termes_positifs);
        //echo "termes negatifs :";
        //var_dump($termes_negatifs);

        $flag_match = true;
        foreach($termes_positifs as $terme){
                if (strpos($contenu,$terme) === false){
                        $flag_match = false;
                        //echo "terme positif non trouvé : ".$terme;
                        break;
                }
        }
        if ($flag_match){
                foreach($termes_negatifs as $terme){
                        if (strpos($contenu,$terme) !== false){
                                $flag_match = false;
                                //echo "terme négatif trouvé : ".$terme;
                                break;
                        }
                }
        }

        //resultat dans flag_match
/*        if ($flag_match){
                echo "la requête matche !";
        } else {
                echo "la requête ne matche pas";
        }
 * 
 */
        return $flag_match;
    }
}
