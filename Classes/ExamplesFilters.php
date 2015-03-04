<?php

namespace Wpierre\Scafo\ScafoBundle\Classes;

use Wpierre\Scafo\ScafoBundle\Entity\Filter;

class ExamplesFilters {
	
	/**
	 * 
	 */
	private $filters = Array(
		'factures_gdf' => Array(
				"libelle"=>"Factures GDF",
				"lang"=>"fr",
				"details"=> Array(
						"condition_type" => 2,
						"condition_text" => 'facture gdf suez "vos consommations facturées"',
						"path" => "/GDF/Factures",
						"filename_formater" => "Facture GDF %date%",
						"doc_date_formater" => "%date%"
						)
				
		),	
		'factures_edf' => Array(
				"libelle"=>"Factures EDF",
				"lang"=>"fr",
				"details"=> Array(
						"condition_type" => 2,
						"condition_text" => 'EDF "total TTC"',
						"path" => "/EDF/Factures",
						"filename_formater" => "Facture EDF %date%",
						"doc_date_formater" => "%date%"
				)
		
		),
		'impots_revenus' => Array(
				"libelle"=>"Impôts sur les revenus",
				"lang"=>"fr",
				"details"=> Array(
						"condition_type" => 1,
						"condition_text" => 'IMPÔT SUR LES REVENUS',
						"path" => "/Impots/Impots sur revenu",
						"filename_formater" => "Impôts %date%",
						"doc_date_formater" => "%date%"
				)
		
		),
		'taxe_habitation' => Array(
				"libelle"=>"Taxe d'habitation",
				"lang"=>"fr",
				"details"=> Array(
						"condition_type" => 1,
						"condition_text" => 'taxe d\'habitation',
						"path" => "/Impots/Taxe habitation",
						"filename_formater" => "Taxe habitation %date%",
						"doc_date_formater" => "%date%"
				)
		
		),
	);
	
	public function getFilters(){
		return $this->filters;
	}
	
	/**
	 * Extrait les données d'un filtre exemple et renvoie une entité filtre pré-remplie
	 * @param string $id
	 * @return \Wpierre\Scafo\ScafoBundle\Entity\Filter
	 */
	public function getFilterById($id){
		$datas = $this->filters[$id];
		$filter = new Filter();
		$filter->setTitle($datas['libelle']);
		$filter->setConditionType($datas['details']['condition_type']);
		$filter->setConditionText($datas['details']['condition_text']);
		$filter->setPath($datas['details']['path']);
		$filter->setFilenameFormater($datas['details']['filename_formater']);
		$filter->setDocDateFormater($datas['details']['doc_date_formater']);
		
		return $filter;
	}
}