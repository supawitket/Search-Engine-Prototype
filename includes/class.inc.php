<?
	class Document {
		private $doc = array();

		public function insertURL($url) {
			array_push($this->doc, $url);
		}
		public function getURL($id) {
			return $this->doc[$id];
		}
		public function showDocuments() {
			echo '<div class="showDocuments">';
			echo '<h3>'.count($this->doc).' document added to database</h3>';
			foreach ($this->doc as $key => $value) {
				echo '<li class="upper">'.$value.'</li>';
			}
			echo '</div>';
		}
		public function getDocuments() {
			return $this->doc;
		}
	}
	class PostingLists {

		public $postings;
		public $count;

		public function __construct() {
			$this->postings = array();
			$this->count = 0;
		}
		public function addLocation($id, $location) {
			$this->postings[] = array($id, $location);
		}
		public function showPostingLists() {
			$docID = array();
			array_push($docID, '-1');
			foreach ($this->postings as $key => $value) {
				$found = false;
				for($i=0;$i<count($docID);$i++) {
					if($value[0] == $docID[$i]) {
						$found = true;
					}
				}
				if($found == false)
					array_push($docID, $value[0]);
			}
			unset($docID[0]);
			foreach ($docID as $key => $value) {
				echo 'Document ID : '.$value.'<br>';
				$myLocation = array();
				foreach ($this->postings as $key => $value2) {
					if($value2[0] == $value)
						array_push($myLocation, $value2[1]);
				}
				$myNewLocation = implode(' , ', $myLocation);
				echo 'Location: '.$myNewLocation.'<br>';
			}
		}
		public function getPostingsLists() {
			return $this->postings;
		}
	}
	class Term {

		public $data;
		public $next;
		private $PL;

		public function __construct($data) {
			$this->data = $data;
			$this->next = NULL;
			$this->PL = new PostingLists;
		}
		public function readData() {
			return $this->data;
		}
		public function addLocation($id, $location) {
			$this->PL->addLocation($id, $location);
		}
		public function showPostingLists() {
			echo '<div>';
				$this->PL->showPostingLists();
			echo '</div>';
		}
		public function getPostingsLists() {
			return $this->PL->getPostingsLists();
		}
	}
	class Dictionary {
		
		private $firstTerm;
		private $lastTerm;
		private $count;
		private $doc;


		public function __construct() {
			$this->firstTerm = NULL;
			$this->lastTerm = NULL;
			$this->doc = new Document;
			$count = 0;
		}
		public function addDocument($url) {
			$this->doc->insertURL($url);
		}
		public function showDocuments() {
			$this->doc->showDocuments();
		}
		public function tokenization() {
			echo '<div class="tokenization">';
			echo '<h3>Tokenization process</h3>';
			$myDoc = $this->doc->getDocuments();
			$docID = 0;
			foreach ($myDoc as $key => $value) {
				$fh = fopen($value, 'r');
				$theData = preg_replace('/[^\p{L}\p{N}\s]/u', '', strtolower(fread($fh, filesize($value))));
				fclose($fh);
				$tokens = explode(" ", $theData);

				$i = 0;
				$dictionary = array('Hello World');
				foreach ($tokens as $key => $tok) {
					if(!array_search($tok, $dictionary))
						array_push($dictionary, $tok);
				}
				unset($dictionary[0]);
				sort($dictionary);
				foreach ($dictionary as $key => $tok) {
					$this->insertLastTerm($tok);
				}

				foreach ($tokens as $key => $tok) {
					$this->insertLocation($tok, $docID, $i);
					$i++;
				}
				$docID++;
			}
			echo '<li class="upper">'.$this->count.' words added to dictionary</li>';
			echo '</div>';
			$this->showDictionary();
		}
		public function showDictionary() {
			echo '<div class="showDictionary">';
			echo '<h3>Inverted index</h3>';
			echo '<div class="scroll">';
			$this->showTerm();
			echo '</div>';
			echo '</div>';
		}
		public function showTerm() {
			$current = $this->firstTerm;
			while($current != NULL) {
				echo '
					<div class="showTerm">
						<div class="dictionary">
							<b>Dictionry</b>';
							echo '"'.$current->data.'"';
				echo '
						</div>
						<div class="PostingLists">
							<b>Postings Lists</b>';
							$current->showPostingLists();
				echo '
						</div>
					</div>';
				$current = $current->next;
			}
		}
		public function insertLocation($key, $docID, $location) {
			$theTerm = $this->find($key);
			if($theTerm != NULL) {
				$theTerm->addLocation($docID, $location);
			}
		}
		public function insertLastTerm($data) {
			$term = new Term($data);
			if($this->firstTerm != NULL) {
				$this->lastTerm->next = $term;
				$term->next = NULL;
				$this->lastTerm = &$term;
			} else {
				$term->next = $this->firstTerm;
				$this->firstTerm = &$term;

				if($this->lastTerm == NULL) {
					$this->lastTerm = $term;
				}
			}
			$this->count++;
		}
		public function find($key) {
			$current = $this->firstTerm;
			while($current->data != $key) {
				if($current->data == NULL)
					return NULL;
				else
					$current = $current->next;
			}
			return $current;
		}
		public function getTerm($termPos) {
			if($termPos <= $this->count) {
				$current = $this->firstTerm;
				$pos = 1;
				while($pos != $termPos) {
					if($current->next == NULL)
						return NULL;
					else
						$current = $current->next;
					$pos++;
				}
				return $current;
			} else {
				return NULL;
			}
		}
		public function getText($list, $docID) {
			$myDocument = $this->doc->getDocuments();
			$myFile = $myDocument[$docID];
			$fh = fopen($myFile, 'r');
			$theData = preg_replace('/[^\p{L}\p{N}\s]/u', '', strtolower(fread($fh, filesize($myFile))));
			fclose($fh);
			$tokens = explode(" ", $theData);
			$limit = 50;
			$start = intval($list[0] - $limit);
			if(count($tokens) > $list[count($list) - 1])
				$end = intval($list[count($list) - 1] + $limit);
			else
				$end = $list[count($list) - 1];
			echo '<div class="getText">';
			for($i=$start;$i<$end;$i++) {
				if($list[0] == $i)
					echo '<b>';
				echo $tokens[$i].' ';
				if($list[count($list) - 1] == $i)
					echo '</b>';
			}
			echo '</div>';
		}
		public function search($search) {
			echo '<div class="search">';
			$search = preg_replace('/[^\p{L}\p{N}\s]/u', '', strtolower($search));
			echo '<h3>Results for "'.$search.'"</h3>';
			$myDocument = $this->doc->getDocuments();
			$search_tok = explode(" ", $search);
			$i=0;
			foreach ($myDocument as $key => $value) {
				$found = $this->search_tok($search_tok, $i);
				if($found != NULL) {
					echo '<li>';
					echo 'Docuemnt ID: '.$i.' named "'.$value.'"';
					$this->getText($found, $i);
					echo '</li>';
				}
				$i++;
			}
			echo '</div>';
		}
		public function search_tok($search_tok, $docID) {
			$result = array();
			$current = NULL;
			foreach ($search_tok as $key => $value) {
				$current = $this->binary_search($value, $docID, 0, 0);
				$postingsLists = $this->getTerm($current)->getPostingsLists();
				$myPostings = array();
				foreach ($postingsLists as $key => $value) {
					if($value[0] == $docID)
						array_push($myPostings, $value[1]);
				}
				array_push($result, $myPostings);
			}
			return $this->search_location($result);
		}
		public function search_location($result) {
			$found = false;
			for($i=0;$i<count($result[0]) && $found == false;$i++) {
				$location_result = array();
				array_push($location_result, $result[0][$i]);

				for($j=1;$j<count($result);$j++) {
					for($k=0;$k<count($result[$j]);$k++) {
						$next = intval($location_result[$j - 1]+1);
						if($next == $result[$j][$k])
							array_push($location_result, $result[$j][$k]);

						if(count($location_result) == count($result))
							$found = true;
					}
				}
			}

			if(count($location_result) == count($result))
				return $location_result;
			else
				return NULL;
		}
		public function binary_search($key, $docID, $left, $right) {
			if($right == 0)
				$right = $this->count;

			$mid = round(($left + $right)/2);
			$current = $this->getTerm($mid);

			if(strcmp($key, $current->data) == 0) {
				return $mid;
			} else if(strcmp($key, $current->data) < 0) {
				$right = $mid;
				return $this->binary_search($key, $docID, $left, $right);
			}  else if(strcmp($key, $current->data) > 0) {
				$left = $mid;
				return $this->binary_search($key, $docID, $left, $right);
			} 

		}
	}
?>