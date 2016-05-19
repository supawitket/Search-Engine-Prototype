<html>
<head>
	<title>Search Engine</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
	<h1>Search Engine Assignmen</h1>
	<div class="page">
		<?php 

			require_once("includes/class.inc.php");

			$dictionary = new Dictionary();
			$dictionary->addDocument("sources/BeautifulMind.txt");
			$dictionary->addDocument("sources/Gladiator.txt");
			$dictionary->addDocument("sources/Gravity.txt");
			$dictionary->addDocument("sources/Hobbit1.txt");
			$dictionary->addDocument("sources/Interstellar.txt");
			$dictionary->addDocument("sources/Prometheus.txt");
			$dictionary->addDocument("sources/ResidentEvil.txt");
			$dictionary->addDocument("sources/StarWars.txt");
			$dictionary->addDocument("sources/TheFaultInOurStars.txt");
			$dictionary->addDocument("sources/Titanic.txt");
			$dictionary->showDocuments();
			$dictionary->tokenization();
			echo '
				<form action="" class="searchBox" method="post">
					<input type="text" name="search" placeholder="search..." value="'.$_POST['search'].'">
				</form>
			';
			if($_POST && !empty($_POST['search'])) {
				$dictionary->search($_POST['search']);
			}


			

		?>
	</div>
</body>
</html>