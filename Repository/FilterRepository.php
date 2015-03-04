<?php


namespace Wpierre\Scafo\ScafoBundle\Repository;

use Doctrine\ORM\EntityRepository,
    Wpierre\Scafo\ScafoBundle\Entity\Filter;

class FilterRepository extends EntityRepository
{
    public function getByInstanceOrdered($instance){
        return $this->getEntityManager()->createQuery('SELECT f FROM WpierreScafoScafoBundle:Filter f WHERE f.instance = '.$instance.' ORDER BY f.orderNumber ASC')->getResult();
    }
    

    public function getGoodFilter($text)
    {
        $text = strtolower($text);
    	//gather the filters
    	$filters = $this->getEntityManager()->createQuery('SELECT f FROM WpierreScafoScafoBundle:Filter f ORDER BY f.orderNumber ASC')->getResult();
    	//echo "Il y a ".count($filters). "filtres\n";
    	//see if we can find a filter that matches with the text
    	foreach ($filters as $filter){
    		if ($filter->doesFilterMatch($text)){
    			return $filter;
    		}
    	}
    	return null;    	

    }
    
    /**
     * Déplace tous les filtres pour faire de la place pour la nouvelle position d'un (nouveau) filtre
     * @param int/String $order_number La position à libérer, integer normalement, 'last' pour placer en dernière position
     * @return integer La position à mettre dans le filtre
     */
    public function makeRoomForNewFilterPosition($order_number, $instance){
        //Si c'est un int (une position), on déplace tous les filtres vers une position supérieure pour faire de la place
        if (is_int($order_number)){
            $query = $this->getEntityManager()->createQuery('UPDATE WpierreScafoScafoBundle:Filter f SET f.orderNumber = f.orderNumber + 1 WHERE f.orderNumber >= :ordernumber AND f.instance = :instanceId')->setParameters(array('ordernumber'=>$order_number,'instanceId'=>$instance));
            $query->getResult();
            return $order_number;
        } else {
            //Si c'est 'last', alors on renvoie la dernière position en l'incrémentant de 1
            $query = $this->getEntityManager()->createQuery('SELECT MAX(f.orderNumber) FROM WpierreScafoScafoBundle:Filter f');
            $result = $query->getSingleResult();
            $max_id = array_shift($result);
            return ++$max_id;
        }
        return null;
    }
}