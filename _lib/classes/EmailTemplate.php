<?php
/*
 * This class is used for implementing a nice and configurable email template 
 */

class EmailTemplate extends SetterGetter {
	
	private $aVarAssigned = array();
	private $aVarAssignedRef = array();
	
	private $aMETA = array();
	
	public function __construct($viewFile, $decorations = '_default') {
		$this->setViewFile($viewFile);
		$this->setDecorations($decorations);
	}
	
	###############################################################################
	## SET FUNCTIONS
	###############################################################################
	public function addMETA($sName, $sContent) {
		$this->aMETA[$sName] = $sContent;
	}
	
	###############################################################################
	## ASSIGN FUNCTION
	###############################################################################
	public function assign($index, $mVar) {
		$this->aVarAssigned[$index] = $mVar;
	}
	
	public function assign_by_ref($index, &$mVar) {
		$this->aVarAssignedRef[$index] =& $mVar;
	}
	
	###############################################################################
	## RENDER FUNCTION
	###############################################################################
	public function render() {
		ob_start();
		
		$this->assign('_aMETA', $this->aMETA);
		
		// register all the assigned values
		extract($this->aVarAssigned);
		extract($this->aVarAssignedRef, EXTR_REFS);
		
		// email header field
		require_once(EMAIL_VIEW_DIR.'/_core/header.php');
		
		// email decorations file header
		require_once(EMAIL_DECORATIONS_DIR . '/' . $this->getDecorations() . '/header.php');
		
		// email template main file
		$viewFile = EMAIL_VIEW_DIR .'/'. $this->getViewFile();
		if (!file_exists($viewFile)) {
			die("View file ".$viewFile." not found. Please create it.");
		}
		require_once($viewFile);
		
		// email decorations file footer
		require_once(EMAIL_DECORATIONS_DIR . '/' . $this->getDecorations() . '/footer.php');
		
		// email footer file
		require_once(EMAIL_VIEW_DIR.'/_core/footer.php');
		
		$buffer = ob_get_clean();
		return $buffer;
	}
	
	###############################################################################
	## SEND EMAIL FUNCTION
	###############################################################################
	public function send($to, $subject, $body = null) {
		if (!$to) {
			return false;
		}
		
		require_once(FUNCTIONS_DIR . '/email.php');
		
		if (!$body) {
			$body = $this->render();
		}
		
		return email($to, $subject, $body);
	}
}