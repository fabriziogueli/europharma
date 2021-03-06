<?php
include('db.php');
include('agent.php');
$action=$_GET['action'];
$id = $_GET['id'];
if($action=='selectproduct'){
	$query = $db->prepare('SELECT nome, id FROM prodotti WHERE id NOT IN (SELECT codprodotto FROM "agente-prodotto" WHERE idagente = :id)');
	$query->execute(array(':id' => $id));
	$prodotti=$query->fetchAll(PDO::FETCH_ASSOC);
	echo('<div class="caricodati" align="center" style="width:400px;"><div id="portfolio" class="container"><div class="title">
		<br>	<h1><p>Aggiungi un nuovo Prodotto</p></h1>
		</div>');

	echo('<form method="POST" action="index.php?section=addagentproduct&action=selectaree&id='.$id.'">');
	echo('<table><tr><td>Prodotto </td><td><select name="prodotto">');
	foreach($prodotti as $prodotto){
		echo('<option value="'.$prodotto['id'].'">'.$prodotto['nome'].'</option>');
	}
	echo('</select></td></tr><tr><td><br>Provvigione</td><td><br><input type="number" step="any" min="0" name="provvigione" required></td></tr>');
	echo('<tr><td><br><input type="submit" value="Invia"></td></tr></table></form>');
	echo('</div></div>');
}if($action=='selectaree'){
	$idprodotto = $_POST['prodotto'];
	$provvigione = $_POST['provvigione'];
	$query = $db->prepare('SELECT nome, area FROM aree, "agente-aree" as aa WHERE aa.idagente = :id AND aree.codice = aa.area AND aree.codice NOT IN (SELECT area FROM "agente-aree" as aa, "agente-prodotto" as ap, "agente-prodotto-area" as apa WHERE ap.codprodotto = :idprodotto AND ap.id = apa.idagenteprodotto AND aa.id = apa.idagentearea)');
	$query->execute(array(':id' => $id, ':idprodotto' => $idprodotto));
	$microaree = $query->fetchAll(PDO::FETCH_ASSOC);
	if(count($microaree)>0){
		echo('<div class="caricodati" align="center" style="width:400px;"><div id="portfolio" class="container"><div class="title">
		<br>	<h1><p>Crea Fatture</p></h1>
		</div>');
		echo('<form method="POST" action="index.php?section=addagentproduct&action=insertproduct&id='.$id.'">');
		foreach($microaree as $microarea){
			echo($microarea['nome'].' '.substr($microarea['area'],3).' <input type="checkbox" name="microaree[]" value="'.$microarea['area'].'" checked><br>');
		}
		echo('<input name="prodotto" type="hidden" value="'.$idprodotto.'">');
		echo('<input name="provvigione" type="hidden" value="'.$provvigione.'">');
		echo('<input type="submit" value="Invia"></form>');
	}else{
		echo('<br>Nessuna microarea di questo agente è idonea per essere assegnata a questo prodotto<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
	}
	echo('</div></div>');
}
else if($action=='insertproduct'){
	try{
		$idprodotto = $_POST['prodotto'];
		$provvigione = $_POST['provvigione'];
		$microaree = $_POST['microaree'];
		$agent = Agent::getAgentFromDB($id,$db);
		$agent->assignProduct($db,$idprodotto,$provvigione);
		/*$query = $db->prepare('INSERT INTO "agente-prodotto"(codprodotto, provvigione, idagente) VALUES (:codprodotto, :provvigione, :id)');
		$query->execute(array(':id' => $id, ':codprodotto' => $idprodotto, ':provvigione' => $provvigione));
		$query = $db->prepare('SELECT id FROM "agente-prodotto" WHERE codprodotto = :prodotto AND idagente = :id');
		$query->execute(array(':id' => $id, ':prodotto' => $idprodotto));
		$idagenteprodotto = $query->fetch();*/
		foreach($microaree as $microarea){
			/*$query = $db->prepare('SELECT id FROM "agente-aree" WHERE area = :microarea AND idagente = :id');
			$query->execute(array(':id' => $id, ':microarea' => $microarea));
			$idagentearea = $query->fetch();
			$query = $db->prepare('INSERT INTO "agente-prodotto-area" VALUES (:idagentearea, :idagenteprodotto)');
			$query->execute(array(':idagentearea' => $idagentearea[0], ':idagenteprodotto' => $idagenteprodotto[0]));*/
			$agent->assignProductArea($db, $idprodotto, $microarea);
		}
		echo('<br>Operazione eseguita con successo<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
	} catch(Exception $pdoe){
		echo('Errore: '.$pdoe->getMessage());
	}
}

else if($action=='deleteproduct'){
	$prod = $_GET['product'];
	$agent = Agent::getAgentFromDB($id,$db);
	try{
		/*$query = $db->prepare('DELETE from "agente-prodotto" WHERE idagente = :idagente AND codprodotto = :idprodotto');
		$query->execute(array(':idprodotto' => $prod, ':idagente' => $id));*/
		$agent->deleteProduct($db, $prod);
		echo('<br>Operazione eseguita con successo<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
	}catch(Exception $pdoe){
		echo('Errore: '.$pdoe->getMessage());
	}
}

else if($action=='deleteareaproduct'){
	$idagenteprodottoarea = $_GET['idareaproduct'];
	$agent = Agent::getAgentFromDB($id,$db);
	try{
		/*$query = $db->prepare('DELETE from "agente-prodotto" WHERE idagente = :idagente AND codprodotto = :idprodotto');
		$query->execute(array(':idprodotto' => $prod, ':idagente' => $id));*/
		$agent->deleteProductArea($db, $idagenteprodottoarea);
		echo('<br>Operazione eseguita con successo<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
	}catch(Exception $pdoe){
		echo('Errore: '.$pdoe->getMessage());
	}


}

else if($action == 'addareaproduct'){

	$idprodotto = $_GET['product'];
	$query = $db->prepare('WITH myquery AS ((SELECT aree.nome, area FROM aree, "agente-aree" as aa WHERE aa.idagente = :id AND aree.codice = aa.area AND aree.codice NOT IN (SELECT area FROM "agente-aree" as aa, "agente-prodotto" as ap, "agente-prodotto-area" as apa WHERE ap.codprodotto = :idprodotto AND ap.id = apa.idagenteprodotto AND aa.id = apa.idagentearea)) EXCEPT SELECT aree.nome, area from "agente-aree" as aa,aree, "agente-prodotto" as ap, "agente-prodotto-area" as apa  WHERE apa.idagentearea = aa.id AND apa.idagenteprodotto = ap.id AND aree.codice = aa.area AND aa.idagente = :id AND ap.idagente = aa.idagente AND  ap.codprodotto = :idprodotto) SELECT * FROM myquery ORDER BY nome, area'); 
	/*$query = $db->prepare('SELECT aree.nome, area from "agente-aree" as aa,aree, "agente-prodotto" as ap, "agente-prodotto-area" as apa  WHERE apa.idagentearea = aa.id AND apa.idagenteprodotto = ap.id AND aree.codice = aa.area AND aa.idagente = :id AND ap.idagente = aa.idagente AND  ap.codprodotto = :idprodotto'); */
	$query->execute(array(':id' => $id, ':idprodotto' => $idprodotto));
	$microaree = $query->fetchAll(PDO::FETCH_ASSOC);
	if(count($microaree)>0){
		echo('<form method="POST" action="index.php?section=addagentproduct&action=insertareaproduct&id='.$id.'">');
		foreach($microaree as $microarea){
			echo($microarea['nome'].' '.substr($microarea['area'],3).' <input type="checkbox" name="microaree[]" value="'.$microarea['area'].'"><br>');
		}
		echo('<input name="prodotto" type="hidden" value="'.$idprodotto.'">');
		echo('<input type="submit" value="Invia"></form>');
	}else{
		echo('<br>Nessuna altra microarea di questo agente è idonea per essere assegnata a questo prodotto<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
	}
}

else if($action == 'insertareaproduct'){
	$idprodotto = $_POST['prodotto'];
	$agent = Agent::getAgentFromDB($id,$db);
	$microaree = $_POST['microaree'];
	try{
	foreach($microaree as $microarea){
		$agent->assignProductArea($db, $idprodotto, $microarea);
	}
	echo('<br>Operazione eseguita con successo<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
	} catch(Exception $pdoe){
		echo('Errore: '.$pdoe->getMessage());
	}
}

else if($action=='addallproducts'){

		$query1 = $db->prepare('SELECT * FROM "agente-aree" WHERE idagente = :id');
		$query1->execute(array(':id' => $id));
		$aree=$query1->fetchAll(PDO::FETCH_ASSOC);
		$query = $db->prepare('SELECT * FROM prodotti');
		$query->execute();
		$prodotti=$query->fetchAll(PDO::FETCH_ASSOC);
		$agent = Agent::getAgentFromDB($id,$db);
		foreach($prodotti as $prodotto){
			//echo('<option value="'.$prodotto['id'].'">'.$prodotto['nome'].'</option>');
			$agent->assignProduct($db,$prodotto['id'],$prodotto['provvigionedefault']);
			$counter = 0;
			foreach($aree as $area){
				try{
					$agent->assignProductArea($db, $prodotto['id'], $area['area']);
					$counter++;
				}catch(Exception $pdoe){
					//echo('Errore: '.$pdoe->getMessage());
					continue;
				}
			}
		if($counter == 0){
			$agent->deleteProduct($db,$prodotto['id']);

		}
	}
	echo('<br>Operazione eseguita con successo<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');
}

else if($action == 'viewprovvigione'){
$provvigione = $_GET['provvigione'];
$productid = $_GET['product'];
echo('<form method="POST" action="index.php?section=addagentproduct&action=modprovvigione&product='.$productid.'&id='.$id.'">');
	
	echo('Provvigione: <input type="number" step="any" min="0" name="provvigione" value="'.$provvigione.'" required="required">');
	echo('<input type="submit" name="Invia">');
	echo('</form>');

}

else if($action == 'modprovvigione')
{

$provvigione = $_POST['provvigione'];
$productid= $_GET['product'];

$agent = Agent::getAgentFromDB($id,$db);
try{
$agent->updateProvvigioneProduct($db, $productid, $provvigione);
} catch(Exception $pdoe){
					echo('Errore: '.$pdoe->getMessage());
					
				}
echo('<br>Operazione eseguita con successo<br> <a href="index.php?section=viewagent&id='.$id.'">Torna indietro</a>');


}

?>
