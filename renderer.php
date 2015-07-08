<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/lib.php');
/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_readaloud
 * @copyright 2015 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_readaloud_renderer extends plugin_renderer_base {

		  /**
     * Returns the header for the module
     *
     * @param mod $instance
     * @param string $currenttab current tab that is shown.
     * @param int    $item id of the anything that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header($moduleinstance, $cm, $currenttab = '', $itemid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($moduleinstance->name, true, $moduleinstance->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
        $context = context_module::instance($cm->id);

    /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $output = $this->output->header();

        if (has_capability('mod/readaloud:manage', $context)) {
         //   $output .= $this->output->heading_with_help($activityname, 'overview', MOD_READALOUD_LANG);

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/readaloud/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= $this->output->heading($activityname);
        }
	

        return $output;
    }
	
	/**
     * Return HTML to display limited header
     */
      public function notabsheader(){
      	return $this->output->header();
      }


    /**
     *
     */
    public function show_welcome($showtext) {
	
		$displaytext = $this->output->box_start();
		$displaytext .= $this->output->heading($showtext, 4, 'main');
		$displaytext .= $this->output->box_end();
		$ret= html_writer::div($displaytext,MOD_READALOUD_INSTRUCTIONS_CONTAINER,array('id'=>MOD_READALOUD_INSTRUCTIONS_CONTAINER));
        return $ret;
    }

	 /**
     *
     */
	public function show_intro($readaloud,$cm){
		$ret = "";
		if (trim(strip_tags($readaloud->intro))) {
			$ret .= $this->output->box_start('mod_introbox');
			$ret .= format_module_intro('readaloud', $readaloud, $cm->id);
			$ret .= $this->output->box_end();
		}
		return $ret;
	}
	
	
	 /**
     
     */
	public function show_passage($readaloud,$cm){
		
		$stop_button =  html_writer::tag('button',get_string('done', MOD_READALOUD_LANG),
				array('class'=>'btn btn-primary ' . MOD_READALOUD_STOP_BUTTON));
		$stop_button_cont= html_writer::div($stop_button,MOD_READALOUD_STOP_BUTTON_CONTAINER,array('id'=>MOD_READALOUD_STOP_BUTTON_CONTAINER));
		$ret = "";
		$ret .= html_writer::div( $readaloud->passage . $stop_button_cont,MOD_READALOUD_PASSAGE_CONTAINER,
							array('id'=>MOD_READALOUD_PASSAGE_CONTAINER));
		return $ret;
	}
	
		 /**
     *
     */
	public function show_progress($readaloud,$cm){
		$hider =  html_writer::div('',MOD_READALOUD_HIDER,array('id'=>MOD_READALOUD_HIDER));
		$message =  html_writer::tag('h4',get_string('processing',MOD_READALOUD_LANG),array());
		$spinner =  html_writer::tag('i','',array('class'=>'fa fa-spinner fa-5x fa-spin'));
		$progressdiv = html_writer::div($message . $spinner ,MOD_READALOUD_PROGRESS_CONTAINER,
							array('id'=>MOD_READALOUD_PROGRESS_CONTAINER));
		$ret = $hider . $progressdiv;
		return $ret;
	}
	
		 /**
     *
     */
	public function show_feedback($readaloud,$cm){
		$displaytext = $this->output->box_start();
		$displaytext .= $this->output->heading(get_string('feedbackheader',MOD_READALOUD_LANG), 3, 'main');
		$displaytext .=  html_writer::div($readaloud->feedback,'',array());
		$displaytext .= $this->output->box_end();
		$ret= html_writer::div($displaytext,MOD_READALOUD_FEEDBACK_CONTAINER,array('id'=>MOD_READALOUD_FEEDBACK_CONTAINER));
        return $ret;
	}
	
		 /**
     *
     */
	public function show_error($readaloud,$cm){
		$displaytext = $this->output->box_start();
		$displaytext .= $this->output->heading(get_string('errorheader',MOD_READALOUD_LANG), 3, 'main');
		$displaytext .=  html_writer::div(get_string('uploadconverterror',MOD_READALOUD_LANG),'',array());
		$displaytext .= $this->output->box_end();
		$ret= html_writer::div($displaytext,MOD_READALOUD_ERROR_CONTAINER,array('id'=>MOD_READALOUD_ERROR_CONTAINER));
        return $ret;
	}
	
	/**
     *
     */
	public function show_button_recorder($readaloud,$cm){
		
		//buttons
		$rec_button =  html_writer::tag('button',get_string('recordnameschool',MOD_READALOUD_LANG),
				array('class'=>'btn btn-primary ' . MOD_READALOUD_RECORD_BUTTON));
		$start_button =  html_writer::tag('button',get_string('beginreading',MOD_READALOUD_LANG),
				array('class'=>'btn btn-primary ' . MOD_READALOUD_START_BUTTON, 'disabled'=>'true'));
		
		//recorder + instructions
		$recorderdiv= html_writer::div('',MOD_READALOUD_RECORDER_CONTAINER,
							array('id'=>MOD_READALOUD_RECORDER_CONTAINER));
		$dummyrecorderdiv= html_writer::div('',MOD_READALOUD_DUMMY_RECORDER . " " . MOD_READALOUD_DUMMY_RECORDER .'_hidden',
							array('id'=>MOD_READALOUD_DUMMY_RECORDER));
		$instructionsrightdiv= html_writer::div('' ,MOD_READALOUD_RECORDER_INSTRUCTIONS_RIGHT,
							array('id'=>MOD_READALOUD_RECORDER_INSTRUCTIONS_RIGHT));
		$instructionsleftdiv= html_writer::div('' ,MOD_READALOUD_RECORDER_INSTRUCTIONS_LEFT,
							array('id'=>MOD_READALOUD_RECORDER_INSTRUCTIONS_LEFT));
		$recordingdiv = html_writer::div($instructionsleftdiv . $recorderdiv . $dummyrecorderdiv . $instructionsrightdiv,MOD_READALOUD_RECORDING_CONTAINER);
		
		//prepare output
		$ret = "";
		$ret .=$recordingdiv;
		$ret .= html_writer::div($rec_button,MOD_READALOUD_RECORD_BUTTON_CONTAINER,array('id'=>MOD_READALOUD_RECORD_BUTTON_CONTAINER));
		$ret .= html_writer::div($start_button,MOD_READALOUD_START_BUTTON_CONTAINER,array('id'=>MOD_READALOUD_START_BUTTON_CONTAINER));

		
		//return it
		return $ret;
	}
  
}

class mod_readaloud_report_renderer extends plugin_renderer_base {


	public function render_reportmenu($moduleinstance,$cm) {
		
		$basic = new single_button(
			new moodle_url(MOD_READALOUD_URL . '/reports.php',array('report'=>'basic','id'=>$cm->id,'n'=>$moduleinstance->id)), 
			get_string('basicreport',MOD_READALOUD_LANG), 'get');

		$attempts = new single_button(
			new moodle_url(MOD_READALOUD_URL . '/reports.php',array('report'=>'attempts','id'=>$cm->id,'n'=>$moduleinstance->id)), 
			get_string('attemptsreport',MOD_READALOUD_LANG), 'get');
			
			
		$ret = html_writer::div($this->render($basic) .'<br />' .$this->render($attempts) .'<br />'  ,MOD_READALOUD_CLASS  . '_listbuttons');

		return $ret;
	}

	public function render_delete_allattempts($cm){
		$deleteallbutton = new single_button(
				new moodle_url(MOD_READALOUD_URL . '/manageattempts.php',array('id'=>$cm->id,'action'=>'confirmdeleteall')), 
				get_string('deleteallattempts',MOD_READALOUD_LANG), 'get');
		$ret =  html_writer::div( $this->render($deleteallbutton) ,MOD_READALOUD_CLASS  . '_actionbuttons');
		return $ret;
	}

	public function render_reporttitle_html($course,$username) {
		$ret = $this->output->heading(format_string($course->fullname),2);
		$ret .= $this->output->heading(get_string('reporttitle',MOD_READALOUD_LANG,$username),3);
		return $ret;
	}

	public function render_empty_section_html($sectiontitle) {
		global $CFG;
		return $this->output->heading(get_string('nodataavailable',MOD_READALOUD_LANG),3);
	}
	
	public function render_exportbuttons_html($cm,$formdata,$showreport){
		//convert formdata to array
		$formdata = (array) $formdata;
		$formdata['id']=$cm->id;
		$formdata['report']=$showreport;
		/*
		$formdata['format']='pdf';
		$pdf = new single_button(
			new moodle_url(MOD_READALOUD_URL . '/reports.php',$formdata),
			get_string('exportpdf',MOD_READALOUD_LANG), 'get');
		*/
		$formdata['format']='csv';
		$excel = new single_button(
			new moodle_url(MOD_READALOUD_URL . '/reports.php',$formdata), 
			get_string('exportexcel',MOD_READALOUD_LANG), 'get');

		return html_writer::div( $this->render($excel),MOD_READALOUD_CLASS  . '_actionbuttons');
	}
	

	
	public function render_section_csv($sectiontitle, $report, $head, $rows, $fields) {

        // Use the sectiontitle as the file name. Clean it and change any non-filename characters to '_'.
        $name = clean_param($sectiontitle, PARAM_FILE);
        $name = preg_replace("/[^A-Z0-9]+/i", "_", trim($name));
		$quote = '"';
		$delim= ",";//"\t";
		$newline = "\r\n";

		header("Content-Disposition: attachment; filename=$name.csv");
		header("Content-Type: text/comma-separated-values");

		//echo header
		$heading="";	
		foreach($head as $headfield){
			$heading .= $quote . $headfield . $quote . $delim ;
		}
		echo $heading. $newline;
		
		//echo data rows
        foreach ($rows as $row) {
			$datarow = "";
			foreach($fields as $field){
				$datarow .= $quote . $row->{$field} . $quote . $delim ;
			}
			 echo $datarow . $newline;
		}
        exit();
        break;
	}

	public function render_section_html($sectiontitle, $report, $head, $rows, $fields) {
		global $CFG;
		if(empty($rows)){
			return $this->render_empty_section_html($sectiontitle);
		}
		
		//set up our table and head attributes
		$tableattributes = array('class'=>'generaltable '. MOD_READALOUD_CLASS .'_table');
		$headrow_attributes = array('class'=>MOD_READALOUD_CLASS . '_headrow');
		
		$htmltable = new html_table();
		$htmltable->attributes = $tableattributes;
		
		
		$htr = new html_table_row();
		$htr->attributes = $headrow_attributes;
		foreach($head as $headcell){
			$htr->cells[]=new html_table_cell($headcell);
		}
		$htmltable->data[]=$htr;
		
		foreach($rows as $row){
			$htr = new html_table_row();
			//set up descrption cell
			$cells = array();
			foreach($fields as $field){
				$cell = new html_table_cell($row->{$field});
				$cell->attributes= array('class'=>MOD_READALOUD_CLASS . '_cell_' . $report . '_' . $field);
				$htr->cells[] = $cell;
			}

			$htmltable->data[]=$htr;
		}
		$html = $this->output->heading($sectiontitle, 4);
		$html .= html_writer::table($htmltable);
		return $html;
		
	}
	
	function show_reports_footer($moduleinstance,$cm,$formdata,$showreport){
		// print's a popup link to your custom page
		$link = new moodle_url(MOD_READALOUD_URL . '/reports.php',array('report'=>'menu','id'=>$cm->id,'n'=>$moduleinstance->id));
		$ret =  html_writer::link($link, get_string('returntoreports',MOD_READALOUD_LANG));
		$ret .= $this->render_exportbuttons_html($cm,$formdata,$showreport);
		return $ret;
	}

}

class mod_readaloud_gradenow_renderer extends plugin_renderer_base {
	public function render_gradenow($gradenow) {
		$ret = $this->render_header($gradenow->attemptdetails('userfullname'));
		$ret = $this->render_audioplayer($gradenow->attemptdetails('audiourl'));
		$ret .= $this->render_passage($gradenow->attemptdetails('passage'));
		//$ret .=  $this->output->heading('somedetails:' . $gradenow->attemptdetails('somedetails') , 5);
		return $ret;
	}
	
	public function render_header($username) {
		$ret = $this->output->heading(get_string('gradenowtitle',MOD_READALOUD_LANG,$username),3);
		return $ret;
	}
	
	public function render_passage($passage){
		// load the HTML document
		$doc = new DOMDocument;
		// it will assume ISO-8859-1  encoding, so we need to hint it:
		//see: http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
		@$doc->loadHTML(mb_convert_encoding($passage, 'HTML-ENTITIES', 'UTF-8'));

		// select all the text nodes
		$xpath = new DOMXPath($doc);
		$nodes = $xpath->query('//text()');
		//init the text count
		$wordcount=0;
		foreach ($nodes as $node) {
			if(empty(trim($node->nodeValue))){continue;}
			//$words = preg_split('#\s+#', $node->nodeValue, null, PREG_SPLIT_NO_EMPTY);
			$delim = ' ';
			$words = explode($delim, $node->nodeValue);

			foreach($words as $word){
				$wordcount++;
				$newnode = $doc->createElement('span',$word);
				$spacenode = $doc->createElement('span',$delim);
				//$newnode->appendChild($spacenode);
				//print_r($newnode);
				$newnode->setAttribute('id',MOD_READALOUD_CLASS . '_passageword_' . $wordcount);
				$newnode->setAttribute('data-wordnumber',$wordcount);
				$newnode->setAttribute('class',MOD_READALOUD_CLASS . '_passageword');
				$spacenode->setAttribute('class',MOD_READALOUD_CLASS . '_passagespace');
				$spacenode->setAttribute('id',MOD_READALOUD_CLASS . '_passagespace_' . $wordcount);
				$node->parentNode->appendChild($newnode);
				$node->parentNode->appendChild($spacenode);
				$newnode = $doc->createElement('span',$word);
			}
			$node->nodeValue ="";	
		}

		$usepassage= $doc->saveHTML();

		
		$ret = html_writer::div($usepassage,'mod_readaloud_passagecontainer');
		return $ret;
	}
	
	public function render_audioplayer($audiourl){
		$ret = html_writer::tag('audio','',
									array('controls'=>'','src'=>$audiourl));
		return $ret;
	}
}