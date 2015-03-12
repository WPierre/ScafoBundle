<?php

namespace WPierre\Scafo\ScafoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $datas = Array();
        $datas['instances'] = $this->get('doctrine')->getRepository('WPierreScafoScafoBundle:ConfigInstance')->findAll();
        if (count($datas['instances']) < 1){
        	return $this->redirect($this->generateUrl('wpierre_scafo_scafo_tests',array()));
        }
        $datas['instance'] = null;
        return $this->render('WPierreScafoScafoBundle:Default:index.html.twig', $datas);
    }
}
