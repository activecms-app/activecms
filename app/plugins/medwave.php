<?php

class medwave
{
	function register()
	{

	}

	function unregister()
	{

	}

	function import_formats()
	{
		return [
			'medwave-jats-es' => 'XML-JATS - Español',
			'medwave-jats-en' => 'XML-JATS - Inglés'
		];
	}

	/**
	 * Import a file in JATS XML format into medwave article structure
	 */
	function import($folder, $type, $format, $content, $user) {

		$xml = simplexml_load_string($content);
		if( !$xml ) {
			throw new Exception('No se pudo procesar archivo xml', 1);
		}

		$name = (string)$xml->front->{'article-meta'}->{'article-id'};
		$name = trim(substr($name, strrpos($name, '.')+1 ));
		if( !ctype_alnum($name) ) {
			throw new Exception('El identificador del artículo no contiene carácteres validos', 1);
		}

		//Search for existing object in the folder
		$object = $folder->getFileByName($name);

		$title = (string)$xml->front->{'article-meta'}->{'title-group'}->{'article-title'}->asXML();
		$title = str_replace('<article-title>', '', $title);
		$title = str_replace('</article-title>', '', $title);
		$title = self::normalizeTextFormat($title);
		$title_en = '';
		if( isset($xml->front->{'article-meta'}->{'title-group'}->{'trans-title-group'}->{'trans-title'}) ) {
			$trans_title_group_attributes = $xml->front->{'article-meta'}->{'title-group'}->{'trans-title-group'}->attributes('xml', true);
			if( isset($trans_title_group_attributes) && $trans_title_group_attributes == 'es' ) {
				$title_en = $title;
				$title = (string)$xml->front->{'article-meta'}->{'title-group'}->{'trans-title-group'}->{'trans-title'};
			} else {
				$title_en = (string)$xml->front->{'article-meta'}->{'title-group'}->{'trans-title-group'}->{'trans-title'};
			}
		}

		$newarticle = false;
		if( !$object->Id ) {
			$object = $folder->newChild($user, $type, $name, $title);
			if( is_null($object) )
			{
				throw new Exception('No se pudo importar elcontenido', 1);
			}
			$newarticle = true;
		}

		if( $newarticle ) {
			$object->setData(104, $name); //id

			$object->setData(1, (string)$xml->xpath("//front/article-meta/article-id[@pub-id-type='doi']")[0]); //doi
			if( isset($xml->front->{'article-meta'}->volume) ) {
				$object->setData(7, (string)$xml->front->{'article-meta'}->volume); //volume
			}
			if( isset($xml->front->{'article-meta'}->issue) ) {
				$object->setData(333, (string)$xml->front->{'article-meta'}->issue); //issue
			}
			if( isset($xml->front->{'article-meta'}->{'author-notes'}->corresp) ) {
				$object->setData(13, (string)$xml->front->{'article-meta'}->{'author-notes'}->corresp); //correpondencia
				if( isset($xml->front->{'article-meta'}->{'author-notes'}->corresp->email) ) {
					$object->setData(3, (string)$xml->front->{'article-meta'}->{'author-notes'}->corresp->email); //email
				}
			}

			$keywordsNodes = $xml->xpath("//front/article-meta/kwd-group/kwd");
			if( count($keywordsNodes) ) {
				$object->setData(14, implode(', ', $keywordsNodes));
			}
			//Afiliaciones
			$aff = [];
			$affNodes = $xml->xpath("//front/article-meta/aff");
			if( count($affNodes) )
			{
				foreach($affNodes as $affNode)
				{
					if( isset($affNode->label)) {
						unset($affNode->label);
					}
					$aff[(string)$affNode['id']] = strip_tags($affNode->asXML());
				}
			}
			//Autores
			$authorsNodes = $xml->xpath("//front/article-meta/contrib-group/contrib[@contrib-type='author']");
			if( count($authorsNodes) )
			{
				$num = 0;
				$authors_orcid = [];
				$authors_name = [];
				$authors_surname = [];
				$authors_aff = [];
				foreach($authorsNodes as $authorsNode)
				{
					$authors_orcid[$num] = str_replace('http://orcid.org/', '', (string)$authorsNode->{'contrib-id'});
					$authors_name[$num] = (string)$authorsNode->name->{'given-names'};
					$authors_surname[$num] = (string)$authorsNode->name->surname;
					//TODO: mas de una afiliacion
					if( isset($aff[(string)$authorsNode->xref['rid']]) )
					{
						$authors_aff[$num] = $aff[(string)$authorsNode->xref['rid']];
					}
					$num++;
				}
			}
			$object->setData(17, $authors_orcid);
			$object->setData(18, $authors_name);
			$object->setData(19, $authors_surname);
			$object->setData(21, $authors_aff);

			//Publication date
			$pubdate = $xml->xpath("//front/article-meta/pub-date[@pub-type='epub']");
			if( count($pubdate) && isset($pubdate[0]->year) && isset($pubdate[0]->month) && isset($pubdate[0]->day) ) {
				
				$object->setData(278, $pubdate[0]->year . '-' . $pubdate[0]->month . '-' . $pubdate[0]->day);
			}
			//Abstract
			if( isset($xml->front->{'article-meta'}->abstract) ) {
				$abstract = $xml->front->{'article-meta'}->abstract;
				if( isset($abstract->p) ) {
					$object->setData(4, (string)$xml->front->{'article-meta'}->abstract->p);
				} elseif (isset($abstract->sec)) {
					$abstract_title = [];
					$abstract_text = [];
					foreach ($abstract->children() as $sec) {
						$abstract_title[] = (string)$sec->title;
						$abstract_text[] = (string)$sec->p;
					}
					if( $format == 'medwave-jats-es' ) {
						$object->setData(57, $abstract_title); //estructura_es.titulo
						$object->setData(58, $abstract_text); //estructura_es.texto
					} else {
						$object->setData(60, $abstract_title); //estructura_en.titulo
						$object->setData(61, $abstract_text); //estructura_en.texto
					}
				}
			}
			//Title Translate
			$object->setData(73, $title_en);
			//Abstract Translate
			if( isset($xml->front->{'article-meta'}->{'trans-abstract'}) ) {
				$abstract = $xml->front->{'article-meta'}->{'trans-abstract'};
				if( $abstract->count() == 2) {
					$object->setData(5, (string)$xml->front->{'article-meta'}->{'trans-abstract'}->p);
				} else {
					$abstract_title = [];
					$abstract_text = [];
					$first = true;
					foreach ($abstract->children() as $abstractpart) {
						if( $first ) {
							$first = false;
							continue;
						}
						if( $abstractpart->getName() == 'title' ) {
							$abstract_title[] = (string)$abstractpart;
						} else {
							$abstract_text[] = (string)$abstractpart;
						}
					}
					if( $format == 'medwave-jats-es' ) {
						$object->setData(60, $abstract_title); //estructura_en.titulo
						$object->setData(61, $abstract_text); //estructura_en.texto
					} else {
						$object->setData(57, $abstract_title); //estructura_es.titulo
						$object->setData(58, $abstract_text); //estructura_es.texto
					}
				}
			}

			$dateReceived = $xml->front->{'article-meta'}->{'history'}->xpath("date[@date-type='received']");
			if( count($dateReceived) )
			{
				$object->setData(33, $dateReceived[0]->year . '-' . $dateReceived[0]->month . '-' . $dateReceived[0]->day);
			}
			$dateAccepted = $xml->front->{'article-meta'}->{'history'}->xpath("date[@date-type='accepted']");
			if( count($dateAccepted) )
			{
				$object->setData(34, $dateAccepted[0]->year . '-' . $dateAccepted[0]->month . '-' . $dateAccepted[0]->day);
			}
	
			//References
			$references = $xml->xpath('back/ref-list/ref');
			if( count($references) ) {
				$num = 0;
				$references_id = [];
				$references_text = [];
				$references_doi = [];
				$references_pmid = [];
				$references_url = [];
				foreach($references as $reference) {
					$references_id[$num] = (string)$reference['id'];
					$references_text[$num] = strip_tags($reference->{'mixed-citation'}->asXML());
					if( isset($reference->{'mixed-citation'}->{'ext-link'}) ) {
						$references_doi[$num] = str_replace('doi:', '', (string)$reference->{'mixed-citation'}->{'ext-link'});
						$references_text[$num] = str_replace(' https://doi.org/', '', str_replace((string)$reference->{'mixed-citation'}->{'ext-link'}, '', $references_text[$num]));
					} else {
						$references_doi[$num] = '';
					}
					$references_pmid[$num] = ''; //TODO reference with PMID
					if( isset($reference->{'mixed-citation'}->uri) ) {
						$references_url[$num] = (string)$reference->{'mixed-citation'}->uri;
						$references_text[$num] = str_replace($references_url[$num], '', $references_text[$num]);
					} else {
						$references_url[$num] = '';
					}
					$num++;
				}
				$object->setData(28, $references_id);
				$object->setData(29, $references_text);
				$object->setData(30, $references_doi);
				$object->setData(31, $references_pmid);
				$object->setData(32, $references_url);
			}
		}

		//Categorie
		if( isset($xml->front->{'article-meta'}->{'article-categories'}) ) {
			if( $format == 'medwave-jats-es' ) {
				$object->setData(367, (string)$xml->front->{'article-meta'}->{'article-categories'}->xpath("subj-group[@subj-group-type='heading']")[0]->subject);
			} else {
				$object->setData(366, (string)$xml->front->{'article-meta'}->{'article-categories'}->xpath("subj-group[@subj-group-type='heading']")[0]->subject);
			}
		}
		if( $format == 'medwave-jats-es' ) {
			//Notes
			$object->setData(78, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='contributors']")[0]->p);
			$object->setData(80, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='conflict']")[0]->p);
			$object->setData(83, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='ethics']")[0]->p);
			$object->setData(35, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='provenance-and-peer-review']")[0]->p);
			$object->setData(86, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='language-submission']")[0]->p);
			$object->setData(84, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='data-sharing-statement']")[0]->p);
			if( isset($xml->front->{'article-meta'}->{'funding-group'}->{'funding-statement'}) ) {
				$object->setData(81, (string)$xml->front->{'article-meta'}->{'funding-group'}->{'funding-statement'});
			}
		} else {
			//Notes
			$object->setData(87, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='contributors']")[0]->p);
			$object->setData(89, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='conflict']")[0]->p);
			$object->setData(92, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='ethics']")[0]->p);
			$object->setData(36, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='provenance-and-peer-review']")[0]->p);
			$object->setData(95, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='language-submission']")[0]->p);
			$object->setData(93, (string)$xml->front->{'article-meta'}->{'author-notes'}->xpath("fn[@fn-type='data-sharing-statement']")[0]->p);
			if( isset($xml->front->{'article-meta'}->{'funding-group'}->{'funding-statement'}) ) {
				$object->setData(90, (string)$xml->front->{'article-meta'}->{'funding-group'}->{'funding-statement'});
			}
		}
		//Key Ideas
		if( isset($xml->body->{'boxed-text'}->list) ) {
			$keyideas = $xml->body->{'boxed-text'}->list->xpath('list-item');
		} elseif( isset($xml->body->{'boxed-text'}->sec->list) ) {
			$keyideas = $xml->body->{'boxed-text'}->sec->list->xpath('list-item');
		}
		if( count($keyideas) ) {
			$content_keyideas = [];
			foreach($keyideas as $keyidea) {
				$content_keyideas[] = self::normalizeTextFormat(strip_tags((string)$keyidea->p->asXML(), '<italic><bold>'));
			}
			if( $format == 'medwave-jats-es' ) {
				$object->setData(75, $content_keyideas);
			} else {
				$object->setData(77, $content_keyideas);
			}
		}

		$content = '';
		$sections = $xml->body->xpath('sec');

		if( count($sections) ) {
			$content = '';
			$figures_id = [];
			$figures_label = [];
			$figures_caption = [];
			$figures_notes = [];
			$tables_id = [];
			$tables_label = [];
			$tables_caption = [];
			$tables_table = [];
			$tables_notes = [];
			$boxed_id = [];
			$boxed_label = [];
			$boxed_caption = [];
			$boxed_content = [];
			foreach($sections as $section)
			{
				$figures = $section->xpath('fig|sec/fig|sec/sec/fig');
				if( count($figures) ) {
					foreach($figures as $figure) {
						$figures_id[] = (string)$figure['id'];
						$figures_label[] = (string)$figure->label;
						$figures_caption[] = str_replace('</caption>', '', str_replace('<caption>', '', (string)$figure->caption->asXML()));
						$attribs = $figure->xpath('attrib');
						$notes = '';
						if( count($attribs) ) {
							foreach($attribs as $attrib) {
								if( !empty($notes) ) $notes .= '<br>';
								$notes .= str_replace('</attrib>', '', str_replace('<attrib>', '', $attrib->asXML()));
							}
							$figures_notes[] = $notes;
						}
						else {
							$figures_notes[] = '';
						}
						$this->simplexml_insert_after(simplexml_load_string('<p>[#' . (string)$figure['id'] . ']</p>'), $figure);
						unset($figure[0][0]);
					}
				}
				$tables = $section->xpath('//table-wrap');
				if( count($tables) ) {
					foreach($tables as $table) {
						$tables_id[] = (string)$table['id'];
						$tables_label[] = (string)$table->label;
						$tables_caption[] = isset($table->caption->p) ? (string)$table->caption->p : $table->caption->asXML();
						$tables_table[] = $table->table->asXML();
						$notes = '';
						foreach ($table->{'table-wrap-foot'}->children() as $footnote) {
							$notes .= $footnote->asXML();
						}
						$tables_notes[] = $notes;
						$this->simplexml_insert_after(simplexml_load_string('<p>[#' . (string)$table['id'] . ']</p>'), $table);
						unset($table[0][0]);
					}
				}
				$boxeds = $section->xpath('boxed-text|sec/boxed-text|sec/sec/boxed-text');
				if( count($boxeds) ) {
					foreach($boxeds as $boxed) {
						$boxed_id[] = (string)$boxed['id'];
						$boxed_label[] = (string)$boxed->label;
						$boxed_caption[] = isset($boxed->caption->title) ? (string)$boxed->caption->title : $boxed->caption->asXML();
						$content_box = '';
						foreach ($boxed->children() as $boxedpart) {
							if( $boxedpart->getName() == 'p' ) {
								$content_box .= $boxedpart->asXML();
							}
						}
						$boxed_content[] = $content_box;
						$this->simplexml_insert_after(simplexml_load_string('<p>[#' . (string)$boxed['id'] . ']</p>'), $boxed);
						unset($boxed[0][0]);
					}
				}
				$this->replaceTags(dom_import_simplexml($section));
				$section_content = $section->asXML();
				$section_content = preg_replace('/^<sec /', '<section ', $section_content);
				$section_content = preg_replace('/<\/sec>$/', '</section>', $section_content);
				$section_content = preg_replace('/(<section[^>]+>)\n?<title>(.*?)<\/title>/', '\1<h1>\2</h1>', $section_content);
				$section_content = preg_replace('/<sec id="s\d-\d">\n?<title>([^<]+)<\/title>/', '<h2>\1</h2>', $section_content);
				$section_content = str_replace('</sec>', '', $section_content);
				$section_content = str_replace('<list-item>', '<li>', $section_content);
				$section_content = str_replace('</list-item>', '</li>', $section_content);
				$section_content = self::normalizeTextFormat($section_content);
				$content .= $section_content;
			}

			if( $format == 'medwave-jats-es' ) {
				$object->setData(71, self::remove_xref($content));
			} else {
				$object->setData(72, self::remove_xref($content));
			}

			if( count($figures_id) ) {
				if( $format == 'medwave-jats-es' ) {
					$object->setData(300, $figures_id);
					$object->setData(301, $figures_label);
					$object->setData(302, self::remove_xref($figures_caption));
					$object->setData(304, self::remove_xref($figures_notes));
				} else {
					$object->setData(306, $figures_id); //figures_en.id
					$object->setData(307, $figures_label); //figures_en.label
					$object->setData(308, self::remove_xref($figures_caption)); //figures_en.caption
					$object->setData(310, self::remove_xref($figures_notes)); //figures_en.notes
				}

			}
			if( count($tables_id) ) {
				if( $format == 'medwave-jats-es' ) {
					$object->setData(312, $tables_id); //tablas.id
					$object->setData(313, $tables_label); //tablas.label
					$object->setData(314, self::remove_xref($tables_caption)); //tablas.caption
					$object->setData(315, $tables_table); //tablas.tabla
					$object->setData(316, self::remove_xref($tables_notes)); //tablas.notes
				} else {
					$object->setData(318, $tables_id); //tablas_en.id
					$object->setData(319, $tables_label); //tablas_en.label
					$object->setData(320, self::remove_xref($tables_caption)); //tablas_en.caption
					$object->setData(321, $tables_table); //tablas_en.tabla
					$object->setData(322, self::remove_xref($tables_notes)); //tablas_en.notes
				}
			}
			if( count($boxed_id) ) {
				if( $format == 'medwave-jats-es' ) {
					$object->setData(324, $boxed_id); //cajas.id
					$object->setData(325, $boxed_label); //cajas.label
					$object->setData(326, $boxed_caption); //cajas.caption
					$object->setData(327, self::remove_xref($boxed_content)); //cajas.contenido
				} else {
					$object->setData(329, $boxed_id); //cajas_en.id
					$object->setData(330, $boxed_label); //cajas_en.label
					$object->setData(331, self::remove_xref($boxed_caption)); //cajas_en.caption
					$object->setData(332, self::remove_xref($boxed_content)); //cajas_en.contenido
				}
			}
		}

		return $object;
	}

	private function normalizeTextFormat($text)
	{
		$text = str_replace('<italic>', '<i>', $text);
		$text = str_replace('</italic>', '</i>', $text);
		$text = str_replace('<bold>', '<b>', $text);
		$text = str_replace('</bold>', '</b>', $text);
		return $text;
	}

	private function simplexml_insert_after(SimpleXMLElement $insert, SimpleXMLElement $target)
	{
		$target_dom = dom_import_simplexml($target);
		$insert_dom = $target_dom->ownerDocument->importNode(dom_import_simplexml($insert), true);

		if ($target_dom->nextSibling) 
		{
			return $target_dom->parentNode->insertBefore($insert_dom, $target_dom->nextSibling);
		} else {
			return $target_dom->parentNode->appendChild($insert_dom);
		}
	}

	private function remove_xref($html)
	{
		//Remove references
		$html = preg_replace('/\[[\r\n ]?<xref ref-type="[^"]+" rid="[^"]+">([^<]+)<\/xref>[\r\n ]?\]/', '[$1]', $html);
		//Remove links in content
		return preg_replace('/<xref ref-type="[^"]+" rid="([^"]+)">([^<]+)<\/xref>/', '<a href="#$1">$2</a>', $html);
	}

	private function replaceTags($node) {
		if($node->hasChildNodes()) {
			$children = $node->childNodes;
			for($i = 0; $i < $children->length; $i++) {
				$this->replaceTags($children[$i]);
				if( $children[$i]->nodeName == 'list' ) {
					$listType = $children[$i]->getAttribute('list-type');
					if( $listType == 'bullet' || empty($listType) ) {
						$this->replaceTagName($children[$i], 'ul');
					} else {
						$this->replaceTagName($children[$i], 'ol');
					}
					$typeAttr = null;
					switch ($listType) {
						case 'alpha-lower':
							$typeAttr = $children[$i]->ownerDocument->createAttribute('type');
							$typeAttr->value = 'a';
							break;
						case 'alpha-upper':
							$typeAttr = $children[$i]->ownerDocument->createAttribute('type');
							$typeAttr->value = 'A';
							break;
						case 'roman-lower':
							$typeAttr = $children[$i]->ownerDocument->createAttribute('type');
							$typeAttr->value = 'i';
							break;
						case 'roman-upper':
							$typeAttr = $children[$i]->ownerDocument->createAttribute('type');
							$typeAttr->value = 'I';
							break;
					}
					if( !is_null($typeAttr) ){
						$children[$i]->appendChild($typeAttr);
					}
				}
			}
		}
	}
	
	private function replaceTagName($node, $newName) {
		$newNode = $node->ownerDocument->createElement($newName);
		foreach ($node->childNodes as $child)
		{
			$newNode->appendChild($child->cloneNode(true));
		}
		$node->parentNode->replaceChild($newNode, $node);
	}
}
