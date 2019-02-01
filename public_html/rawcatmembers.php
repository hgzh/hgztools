<?php
	require_once('/data/project/hgztools/public_html/general.php');
	
	class HgzRawCatmembers extends Hgz {
		
		// request parameters
		protected $par = [];
		
		/* __construct()
			create objects
		*/
		public function __construct() {
			// create new database object
			$this->db = new Database();
			
			// create new request validator
			$this->rq = new RequestValidator();
			
			// execute tool
			$this->run();
			$this->finish();
		}
		
		/* initRequestValidator()
			define accepted url parameters
		*/
		private function initRequestValidator() {
			// user to get articles for
			$this->rq->addAllowed('GET', 'lang',    '', '/^[a-z]{1,7}$/',  true);
			$this->rq->addAllowed('GET', 'project', '', '/^[a-z]{1,15}$/', true);
			$this->rq->addAllowed('GET', 'category', '', '',  true, false);
			
			$this->par = $this->rq->getParams();
		}
		
		/* finish()
			finish tool, close database connection, output page content
		*/
		private function finish() {
			$this->db->close();
		}
		
		/* run()
			run tool
		*/
		private function run() {
			$this->initRequestValidator();

			header('Content-Type: text/plain; charset=utf-8');
			
			if ($this->rq->allRequiredDefined() == true) {
				$visited = [];
				$cats = [];
				$pages = [];
				
				$cats[0] = str_replace(' ', '_', $this->par['category']);

				$this->db->replicaConnect(Database::getName($this->par['lang'], $this->par['project']));
				
				$i = 0;
				$cnt = 0;
				while (isset($cats[$i])) {
					foreach ($visited as $v) {
						if ($v == $cats[$i]) {
							unset($cats[$i]);
							$i++;
							continue 2;
						}
					}
					
					$t1  = 'SELECT page_title, cl_type, page_namespace';
					$t1 .= ' FROM categorylinks';
					$t1 .= ' INNER JOIN page ON cl_from = page_id';
					$t1 .= ' WHERE cl_to = ?';
					$q1 = $this->db->prepare($t1);
					$q1->bind_param('s', $cats[$i]);
					
					$q1->execute();
					$r1 = Database::fetchResult($q1);
					
					foreach ($r1 as $l1) {
						if ($l1['cl_type'] == 'subcat') {
							$found = false;
							foreach ($cats as $c) {
								if ($c == $l1['page_title']) {
									$found = true;
								}
							}
							if ($found == false) {
								$cats[] = $l1['page_title'];
							}
						} elseif ($l1['cl_type'] == 'page') {
							$found = false;
							foreach ($pages as $p) {
								if ($p == $l1['page_title']) {
									$found = true;
								}
							}
							if ($found == false) {
								$pages[] = Database::getNsNameFromNr($l1['page_namespace']) . $l1['page_title'];
								echo Database::getNsNameFromNr($l1['page_namespace']) . $l1['page_title'] . "\n";
								$cnt++;
								if ($cnt == 5000) {
									break 2;
								}
							}
						}
					}
					
					$visited[] = $cats[$i];
					unset($cats[$i]);
					$q1->close();
					$i++;
				}
				
			} else {
				echo "Please specify request parameters.\n";
				echo "  - lang : Project language (e.g. de)\n";
				echo "  - project : Project name (e.g. wikipedia)\n";
				echo "  - category : base category\n";
				echo "Note: report is aborted after 5000 results.";
			}
		}
	}
	
	$instance = new HgzRawCatmembers();
	
?>