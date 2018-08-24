<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_readaloud\output;

use \mod_readaloud\constants;


class gradenow_renderer extends \plugin_renderer_base {

    public function render_gradenow($gradenow) {
        $audio = $this->render_audioplayer($gradenow->attemptdetails('audiourl'));
        $wpm = $this->render_wpmdetails();
        $accuracy = $this->render_accuracydetails();
        $sessionscore = $this->render_sessionscoredetails();
        $mistakes = $this->render_mistakedetails();
        $actionheader = \html_writer::div($audio . $mistakes . $wpm . $accuracy . $sessionscore,
            constants::MOD_READALOUD_GRADING_ACTION_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_ACTION_CONTAINER));


        $ret = $this->render_header($gradenow->attemptdetails('userfullname'));
        $ret .= $actionheader;
        $ret .= $this->render_passage($gradenow->attemptdetails('passage'));
        $ret .= $this->render_passageactions();

        return $ret;
    }

    public function render_userreview($gradenow) {
        $audio = $this->render_audioplayer($gradenow->attemptdetails('audiourl'));
        $wpm = $this->render_wpmdetails();
        $accuracy = $this->render_accuracydetails();
        $sessionscore = $this->render_sessionscoredetails();
        $mistakes = $this->render_mistakedetails();
        $actionheader = \html_writer::div($audio . $mistakes . $wpm . $accuracy . $sessionscore,
            constants::MOD_READALOUD_GRADING_ACTION_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_ACTION_CONTAINER));


        $ret = $this->render_header($gradenow->attemptdetails('userfullname'));
        $ret .= $actionheader;
        $ret .= $this->render_passage($gradenow->attemptdetails('passage'));

        return $ret;
    }

    public function render_machinereview($gradenow) {
        $audio = $this->render_audioplayer($gradenow->attemptdetails('audiourl'));
        $wpm = $this->render_wpmdetails();
        $accuracy = $this->render_accuracydetails();
        $sessionscore = $this->render_sessionscoredetails();
        $mistakes = $this->render_mistakedetails();
        $actionheader = \html_writer::div($audio . $mistakes . $wpm . $accuracy . $sessionscore,
            constants::MOD_READALOUD_GRADING_ACTION_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_ACTION_CONTAINER));


        $ret = $this->render_header($gradenow->attemptdetails('userfullname'));
        $ret .= $actionheader;
        $ret .= $this->render_passage($gradenow->attemptdetails('passage'));

        return $ret;
    }

    public function render_header($username) {
        $ret = $this->output->heading(get_string('showingattempt',constants::MOD_READALOUD_LANG,$username),3);
        return $ret;
    }

    public function render_passage($passage){
        // load the HTML document
        $doc = new \DOMDocument;
        // it will assume ISO-8859-1  encoding, so we need to hint it:
        //see: http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        @$doc->loadHTML(mb_convert_encoding($passage, 'HTML-ENTITIES', 'UTF-8'));

        // select all the text nodes
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//text()');
        //init the text count
        $wordcount=0;
        foreach ($nodes as $node) {
            $trimmednode = trim($node->nodeValue);
            if(empty($trimmednode)){continue;}

            //explode missed new lines that had been copied and pasted. eg A[newline]B was not split and was one word
            //This resulted in ai selected error words, having different index to their passage text counterpart
            $seperator = ' ';
            //$words = explode($seperator, $node->nodeValue);
            $words = preg_split('/\s+/', $node->nodeValue);

            foreach($words as $word){
                $wordcount++;
                $newnode = $doc->createElement('span',$word);
                $spacenode = $doc->createElement('span',$seperator);
                //$newnode->appendChild($spacenode);
                //print_r($newnode);
                $newnode->setAttribute('id',constants::MOD_READALOUD_CLASS . '_grading_passageword_' . $wordcount);
                $newnode->setAttribute('data-wordnumber',$wordcount);
                $newnode->setAttribute('class',constants::MOD_READALOUD_CLASS . '_grading_passageword');
                $spacenode->setAttribute('class',constants::MOD_READALOUD_CLASS . '_grading_passagespace');
                $spacenode->setAttribute('data-wordnumber',$wordcount);
                $spacenode->setAttribute('id',constants::MOD_READALOUD_CLASS . '_grading_passagespace_' . $wordcount);
                $node->parentNode->appendChild($newnode);
                $node->parentNode->appendChild($spacenode);
                $newnode = $doc->createElement('span',$word);
            }
            $node->nodeValue ="";
        }

        $usepassage= $doc->saveHTML();


        $ret = \html_writer::div($usepassage,constants::MOD_READALOUD_CLASS . '_grading_passagecont');
        return $ret;
    }
    public function render_passageactions(){

        $spotcheckbutton = \html_writer::tag('button',
            get_string('spotcheckbutton',constants::MOD_READALOUD_LANG),
            array('type'=>'button','id'=>constants::MOD_READALOUD_CLASS .'_modebutton','class'=>constants::MOD_READALOUD_CLASS .'_modebutton btn btn-success'));
        $aigradebutton = \html_writer::tag('button',
            get_string('doaigrade',constants::MOD_READALOUD_LANG),
            array('type'=>'button','id'=>constants::MOD_READALOUD_CLASS .'_aigradebutton','class'=>constants::MOD_READALOUD_CLASS .'_aigradebutton btn btn-warning hidden'));
        $clearbutton = \html_writer::tag('button',
            get_string('doclear',constants::MOD_READALOUD_LANG),
            array('type'=>'button','id'=>constants::MOD_READALOUD_CLASS .'_clearbutton','class'=>constants::MOD_READALOUD_CLASS .'_clearbutton btn btn-link'));

        $buttons = $spotcheckbutton . $aigradebutton . $clearbutton;

        $container = \html_writer::div($buttons,constants::MOD_READALOUD_CLASS . '_grading_passageactions');
        return $container;
    }

    public function render_audioplayer($audiourl){
        $audioplayer = \html_writer::tag('audio','',
            array('controls'=>'','src'=>$audiourl,'id'=>constants::MOD_READALOUD_GRADING_PLAYER));
        $ret = \html_writer::div($audioplayer,constants::MOD_READALOUD_GRADING_PLAYER_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_PLAYER_CONTAINER));
        return $ret;
    }


    public function render_hiddenaudioplayer(){
        $audioplayer = \html_writer::tag('audio','',array('src'=>'','id'=>constants::MOD_READALOUD_HIDDEN_PLAYER,'class'=>constants::MOD_READALOUD_HIDDEN_PLAYER));
        return $audioplayer;
    }
    public function render_wpmdetails(){
        global $CFG;
        $title = \html_writer::div(get_string('wpm',constants::MOD_READALOUD_LANG),'panel-heading');
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE . ' panel-body',array('id'=>constants::MOD_READALOUD_GRADING_WPM_SCORE));
        $ret = \html_writer::div($title . $score ,constants::MOD_READALOUD_GRADING_WPM_CONTAINER . ' panel panel-primary',
            array('id'=>constants::MOD_READALOUD_GRADING_WPM_CONTAINER));
        return $ret;
    }
    public function render_sessionscoredetails(){
        global $CFG;
        $title = \html_writer::div(get_string('grade_p',constants::MOD_READALOUD_LANG),'panel-heading');
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE . ' panel-body',array('id'=>constants::MOD_READALOUD_GRADING_SESSION_SCORE));
        $ret = \html_writer::div($title . $score ,constants::MOD_READALOUD_GRADING_SESSIONSCORE_CONTAINER . ' panel panel-primary',
            array('id'=>constants::MOD_READALOUD_GRADING_SESSIONSCORE_CONTAINER));
        return $ret;
    }
    public function render_accuracydetails(){
        global $CFG;
        $title = \html_writer::div(get_string('accuracy_p',constants::MOD_READALOUD_LANG),'panel-heading');
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE . ' panel-body',array('id'=>constants::MOD_READALOUD_GRADING_ACCURACY_SCORE));
        $ret = \html_writer::div($title . $score ,constants::MOD_READALOUD_GRADING_ACCURACY_CONTAINER . ' panel panel-primary',
            array('id'=>constants::MOD_READALOUD_GRADING_ACCURACY_CONTAINER));
        return $ret;
    }
    public function render_mistakedetails(){
        global $CFG;
        $title = \html_writer::div(get_string('mistakes',constants::MOD_READALOUD_LANG),'panel-heading');
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE . ' panel-body',array('id'=>constants::MOD_READALOUD_GRADING_ERROR_SCORE));
        $ret = \html_writer::div($title . $score ,constants::MOD_READALOUD_GRADING_ERROR_CONTAINER . ' panel panel-danger',
            array('id'=>constants::MOD_READALOUD_GRADING_ERROR_CONTAINER));
        return $ret;
    }
    public function render_wpmdetails_old(){
        global $CFG;
        $img = \html_writer::tag('img','',array('src'=>$CFG->wwwroot . '/mod/readaloud/pix/wpm.png','class'=>constants::MOD_READALOUD_GRADING_WPM_IMG));
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE,array('id'=>constants::MOD_READALOUD_GRADING_WPM_SCORE));
        $ret = \html_writer::div($img . $score ,constants::MOD_READALOUD_GRADING_WPM_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_WPM_CONTAINER));
        return $ret;
    }
    public function render_accuracydetails_old(){
        global $CFG;
        $img = \html_writer::tag('img','',array('src'=>$CFG->wwwroot . '/mod/readaloud/pix/accuracy.png','class'=>constants::MOD_READALOUD_GRADING_ACCURACY_IMG));
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE,array('id'=>constants::MOD_READALOUD_GRADING_ACCURACY_SCORE));
        $ret = \html_writer::div($img . $score ,constants::MOD_READALOUD_GRADING_ACCURACY_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_ACCURACY_CONTAINER));
        return $ret;
    }
    public function render_mistakedetails_old(){
        global $CFG;
        $img = \html_writer::tag('img','',array('src'=>$CFG->wwwroot . '/mod/readaloud/pix/cross.png','class'=>constants::MOD_READALOUD_GRADING_ERROR_IMG));
        $score = \html_writer::div('0',constants::MOD_READALOUD_GRADING_SCORE,array('id'=>constants::MOD_READALOUD_GRADING_ERROR_SCORE));

        $ret = \html_writer::div($img.$score,constants::MOD_READALOUD_GRADING_ERROR_CONTAINER,array('id'=>constants::MOD_READALOUD_GRADING_ERROR_CONTAINER));
        return $ret;
    }


}