<?php
include('db.php');	
$query = $db->prepare('SELECT * FROM prodotti');
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);

echo('<a href="index.php?section=insertproduct&action=add">Aggiungi prodotto</a>');

echo('<table border="1"><tr><th>Nome</th><th>Sconto</th><th>Prezzo</th><th>Provvigione Default</th</tr>');
foreach ($results as $row){
	echo('<tr><td>'.$row['nome'].'</td><td>'.$row['sconto'].'</td><td>'.$row['prezzo'].'</td><td>'.$row['provvigionedefault'].'</td><td><a href="index.php?section=insertproduct&action=mod&id='.$row['id'].'">modifica</a></td></tr>');
}
echo('</table>');	
?>