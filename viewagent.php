<?php
include('db.php');
include('agent.php');
$id=$_GET['id'];
$query=$db->prepare('SELECT * FROM agenti WHERE id = :id');
$query->execute(array(':id' => $id));
$row = $query->fetch(PDO::FETCH_ASSOC);
$agente = new Agent($row['id'], $row['nome'], $row['cognome'], $row['codicefiscale'], $row['partitaiva'], $row['email'], $row['iva'], $row['enasarco'], $row['ritacconto'], $row['contributoinps'], $row['tipoinps']);
echo('<p>Agente: '.$agente->nome.' '.$agente->cognome.'</p>');
echo('<p>Codice fiscale: '.$agente->codicefiscale.'</p>');
echo('<p>Partita IVA: '.$agente->partitaiva.'</p>');
echo('<p>e-mail: '.$agente->email.'</p>');
echo('<p>% IVA: '.$agente->iva.'</p>');
echo('<p>% Enasarco: '.$agente->enasarco.'</p>');
echo('<p>% Ritenuta d\'acconto: '.$agente->ritacconto.'</p>');
echo('<p>% Contributo INPS: '.$agente->contributoinps.'</p>');
echo('<p>Tipo di contributo INPS: '.$agente->tipoinps.'</p>');
echo('<p align="center">Aree assegnate all\'agente</p>');
$query=$db->prepare('SELECT DISTINCT nome FROM aree, "agente-aree" AS aa WHERE aree.codice = aa.area AND aa.idagente = :id');
$query->execute(array(':id' => $id));
$result = $query->fetchAll(PDO::FETCH_ASSOC);

/* SEZIONE AREE ASSEGNATE ALL'AGENTE*/

echo('<table border="1" width="60%" align="center"><tr><th>Zona</th><th>Microaree</th></tr>');
foreach($result as $row){
	$query=$db->prepare('SELECT codice FROM aree, "agente-aree" AS aa WHERE aree.codice = aa.area AND aa.idagente = :id AND aree.nome = :nome');
	$query->execute(array(':id' => $id, ':nome' => $row['nome']));
	$subresult= $query->fetchAll(PDO::FETCH_ASSOC);
	echo('<tr><td>'.$row['nome'].'</td>');
	echo('<td>');
	foreach($subresult as $microarea){
		echo($microarea['codice'].'       <a href="index.php?section=addagentarea&action=deletemicro&id='.$id.'&microarea='.$microarea['codice'].'">[X]</a>'.'<br>');
	}
	echo('</td></tr>');
}
echo('</table>');
echo('<a href="index.php?section=addagentarea&action=selectprovince&id='.$id.'">Aggiungi area</a>');

/* SEZIONE PRODOTTI ASSEGNATI ALL'AGENTE*/
echo('<p align="center">Prodotti assegnati all\'agente</p>  <a href="index.php?section=addagentproduct&action=selectproduct&id='.$id.'">Assegna nuovo prodotto all\'agente</a>');
$index=0;
$query=$db->prepare('SELECT prodotti.id, prodotti.nome, provvigione FROM prodotti, "agente-prodotto" AS ap WHERE idagente = :idagente AND prodotti.id = ap.codprodotto ORDER BY prodotti.nome'); // seleziona i prodotti
$query->execute(array(':idagente' => $id));
$products = $query->fetchAll(PDO::FETCH_ASSOC);

foreach($products as $prod){
	$class = $index%2==0?"producteven":"productodd";			 
	echo('<div class="'.$class.'"><p align="center">'.$prod['nome'].'         <a href="index.php?section=addagentproduct&action=deleteproduct&id='.$id.'&product='.$prod['id'].'">[X]</a>'.'</p>');
	$query=$db->prepare('SELECT DISTINCT nome FROM aree, "agente-aree" AS aa, "agente-prodotto" AS ap, "agente-prodotto-area" AS apa WHERE aree.codice = aa.area AND aa.idagente = :idagente AND ap.codprodotto = :codprodotto AND apa.idagentearea = aa.id AND apa.idagenteprodotto = ap.id');   //Seleziona le provincie assegnate all'agente per un determinato prodotto'
	$query->execute(array(':idagente' => $id, ':codprodotto' => $prod['id']));
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	echo('<table width="90%"><tr>');  //tabella esterna solo per allineamento
	echo('<td class="celldata"><div class="tabledata"><table border="1"><tr><th>Zona</th><th>Microaree</th></tr>');  //tabella aree
	foreach($result as $row){
		$query=$db->prepare('SELECT codice FROM aree, "agente-aree" AS aa, "agente-prodotto" AS ap, "agente-prodotto-area" AS apa WHERE aree.codice = aa.area AND aa.idagente = :idagente AND ap.codprodotto = :codprodotto AND apa.idagentearea = aa.id AND apa.idagenteprodotto = ap.id AND aree.nome = :nome');  //Seleziona le microaree dentro la provincia
		$query->execute(array(':idagente' => $id, ':nome' => $row['nome'], ':codprodotto' => $prod['id']));
		$subresult= $query->fetchAll(PDO::FETCH_ASSOC);
		echo('<tr><td>'.$row['nome'].'</td>');
		echo('<td>');
		foreach($subresult as $microarea){    
			echo($microarea['codice'].'<br>');
		}
		echo('</td></div></tr>');
	}
	echo('</table></td>');
	
	$query=$db->prepare('SELECT target, percentuale FROM "agente-prodotto" AS ap, "agente-prodotto-target" AS apt WHERE ap.codprodotto = :codprodotto AND ap.idagente = :idagente AND apt.idagenteprodotto = ap.id ORDER BY target');  //Seleziona gli eventuali target/bonus relativi all'agente per un determinato prodotto'
	$query->execute(array(':idagente' => $id, ':codprodotto' => $prod['id']));
	$targets=$query->fetchAll(PDO::FETCH_ASSOC);
	echo('<td class="celldata"><div class="tabledata"><table border="1"><tr><th>Target</th><th>Percentuale</th></tr>'); //tabella target
	if(count($targets)>0){
		foreach($targets as $targ){
			echo('<tr><td>'.$targ['target'].'</td><td>'.$targ['percentuale'].'</td>');
		}
		
	}
	else{
		echo('<tr><td>/</td><td>/</td></tr>');
	}
	echo('</table></div></td>');
	echo('</tr></table></div>');
	$index++;
}


?>
