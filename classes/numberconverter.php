<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 6/4/21
 * Time: 14:24
 */

namespace mod_readaloud;

/**
 * Number to word converter class for Poodll Readaloud
 *
 * @package    mod_readaloud
 * @copyright  2021 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class numberconverter {


    /*
     * This converts any number-digits in the passage, if found in the target, to number-words
     *
     *
     * @param string $passage the passage text
     * @param string $target the text to run the conversion on
     * @return string the converted text
     */
    public static function numbers_to_words_convert($passage,$targettext){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_number_conversions($passagewords);

        foreach($conversions as $conversion){
            $targettext = str_replace($conversion['digits'],$conversion['words'],$targettext);
        }
        return $targettext;
    }

    /*
     * This converts any number-digits in the passage to number-words,
     *  if those number-words are found in the target they are converted there to number-words
     *
     * @param string $passage the passage text
     * @param string $target the text to run the conversion on
     * @return string the converted text
     *
     */
    public static function words_to_numbers_convert($passage,$targettext){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_number_conversions($passagewords);

        foreach($conversions as $conversion){
            $targettext = str_replace($conversion['words'],$conversion['digits'],$targettext);
        }
        return $targettext;
    }

    /*
     * This is just rule based heuristics, keep adding rules when you need 'em
     * @param mixed $passagewords the passage text or an array of passage words
     * @return array the digit to word conversions array
     */
    public static function fetch_number_conversions($passagewords){

        //its possible to call this function with just the passage as text,
        // which might be useful for callers who want the conversions array to pass to JS and not to run the conversion
        if(!is_array($passagewords)){
            $passagewords=self::fetchWordArray($passagewords);
        }

        $conversions=array();
        foreach ($passagewords as $candidate){

            //plain numbers
            if(is_numeric($candidate)){
                //get years
                $yearwords = self::convert_years_to_words($candidate);
                if($yearwords){$conversions[] = ['digits'=>$candidate,'words'=>$yearwords];}
                //get regular numerals
                $numberwords = self::convert_numbers_to_words($candidate);
                if($numberwords){
                    $conversions[] = ['digits'=>$candidate,'words'=>$numberwords];
                    //lets also save a version without 'and'
                    $no_and_numberwords = str_replace(' and ', ' ',  $numberwords);
                    $conversions[] = ['digits'=>$candidate,'words'=>$no_and_numberwords];
                }

            //dollar numbers [currently $ is stripped before we get here. sorry. no currencies]
            }elseif(\core_text::strpos($candidate,'$')===0 && \core_text::strlen($candidate)>1){
                if($candidate=='$1'){
                    $conversions[] = ['digits'=>$candidate,'words'=>'one dollar'];
                }else{
                    $afterdollarbit = \core_text::substr($candidate,1);
                    if(is_numeric($afterdollarbit)) {
                        $numberwords = self::convert_numbers_to_words(\core_text::substr($candidate, 1));
                        if($numberwords) {$conversions[] = ['digits' => $candidate, 'words' => $numberwords . ' dollars'];};
                    }
                }

            //eras/decades
            }else{
                $startbit = \core_text::substr($candidate,0,\core_text::strlen($candidate)-1);
                if(is_numeric($startbit) && $startbit .'s' == $candidate){
                    $erawords=false;
                    switch(\core_text::strlen($candidate)){
                        case 3:
                            $erawords = self::get_era_word((int)$startbit);
                            break;
                        case 5:
                            $isera =true;
                            $erawords =  self::convert_years_to_words($startbit, $isera);

                            break;
                        default:
                    }
                    if($erawords){$conversions[] = ['digits'=>$candidate,'words'=>$erawords];}
                }
            }
        }
        return $conversions;
    }

    /*
     * Years are wordi'fied differently to normal 4 digit numbers, e.g 2020 = "twenty twenty" not "two thousand and twenty"
     * Eras are common in passages e.g "during the 1860s women were not free to...."
     */
    public static function convert_years_to_words($num=false,$isera=false){
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        //if it does not look like a "year" and with year'y word pattern, just pass it back
        if($num <1000 || $num >2999){
            return false;
        }
        $century = $num / 100;
        $centuryword =  self::convert_numbers_to_words($century);

        $remainder = $num % 100;
        switch($remainder){
            case 0:
                //mess around a little with millennial years
                if($century ==10){$centuryword ='one'; $remainderword='thousand';}
                elseif($century ==20){$centuryword ='two'; $remainderword='thousand';}
                elseif($isera){$remainderword = "hundreds";}
                else{$remainderword = "hundred";}
                break;
            case 1: $remainderword = "oh one"; break;
            case 2: $remainderword = "oh two"; break;
            case 3: $remainderword = "oh three"; break;
            case 4: $remainderword = "oh four"; break;
            case 5: $remainderword = "oh five"; break;
            case 6: $remainderword = "oh six"; break;
            case 7: $remainderword = "oh seven"; break;
            case 8: $remainderword = "oh eight"; break;
            case 9: $remainderword = "oh nine"; break;
            default:
                if($isera){
                    $remainderword = self::get_era_word($remainder);
                    if($remainderword ===false){
                        return false;
                    }
                }else {
                    $remainderword = self::convert_numbers_to_words($remainder);
                }
        }
        $ret = trim($centuryword . ' ' . $remainderword);
        $ret = preg_replace('/\s+/', ' ', $ret);
        return $ret;
    }

    /*
     * Eras are simply decades really e.g "the 1920s"
     */
    public static function get_era_word($twodigitnumber){
        switch($twodigitnumber){
            case 10: $eraword ='tens'; break; //is this a thing?
            case 20: $eraword ='twenties'; break;
            case 30: $eraword  ='thirties'; break;
            case 40: $eraword ='forties'; break;
            case 50: $eraword  ='fifties'; break;
            case 60: $eraword  ='sixties'; break;
            case 70: $eraword  ='seventies'; break;
            case 80: $eraword  ='eighties'; break;
            case 90: $eraword  ='nineties'; break;
            default: $eraword = false;
        }
        return $eraword;
    }

    /*
    * The script "borrowed" from: https://stackoverflow.com/a/30299572
     * and modified to clean up extra spaces and add " and " where needed
    */
    public static function convert_numbers_to_words($num = false)
    {
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
                'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
                'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
                'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $and = ($hundreds !='' && ($tens !='' || $singles!='')) ? ' and ' : '';
            $words[] = $hundreds . $and .  $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        $ret= trim(implode(' ', $words));
        $ret = preg_replace('/\s+/', ' ', $ret);
        return $ret;
    }

    /*
   * Convenience function to remove dependency on aigrade and diff
   */

    public static function fetchWordArray($thetext) {

        //tidy up the text so its just lower case words seperated by spaces
        $thetext = self::cleanText($thetext);

        //split on spaces into words
        $textbits = explode(' ', $thetext);

        //remove any empty elements
        $textbits = array_filter($textbits, function($value) {
            return $value !== '';
        });

        //re index array because array_filter converts array to assoc. (ie could have gone from indexes 0,1,2,3,4,5 to 0,1,3,4,5)
        $textbits = array_values($textbits);

        return $textbits;
    }

    /*
     *
     * Convenience function to remove dependency on aigrade and diff
     *
   * Regexp replace with /u will return empty text if not unicodemb4
   * we only really need unicodemb4 for japanese at this stage (2020/09/17)
   * but that means we still need it. This impl is awful. There must be a better way ..
   */
    public static function isUnicodemb4($thetext) {
        //$testtext = "test text: " . "\xf8\xa1\xa1\xa1\xa1"; //this will fail for sure

        $thetext =  \core_text::strtolower($thetext);
        //strip tags is bad for non UTF-8. It might even be the real problem we need to solve here
        //this anecdotally might help: $thetext =utf8_decode($thetext);
        //anyway the unicode problems appear after to combo of strtolower and strip_tags, so we call them first
        $thetext = strip_tags($thetext);
        $testtext = "test text: " . $thetext;

        $test1 = preg_replace('/#\R+#/u', ' ', $testtext);
        if(empty($test1)){return false;}
        $test2 = preg_replace('/\r/u', ' ', $testtext);
        if(empty($test2)){return false;}
        $test3 = preg_replace('/\n/u', ' ', $testtext);
        if(empty($test3)){return false;}
        $test4 = preg_replace("/[[:punct:]]+/u", "", $testtext);
        if(empty($test4)){
            return false;
        }else{
            return true;
        }
    }

    /*
     *
     * Convenience function to remove dependency on aigrade and diff
     *
    * Clean word of things that might prevent a match
     * i) lowercase it
     * ii) remove html characters
     * iii) replace any line ends with spaces (so we can "split" later)
     * iv) remove punctuation
     *
    */
    public static function cleanText($thetext,$unicodemb4=true) {

        //first test its unicodemb4, and then get on with it
        $unicodemb4=self::isUnicodemb4($thetext);

        //lowercaseify
        $thetext = \core_text::strtolower($thetext);

        //remove any html
        $thetext = strip_tags($thetext);

        //replace all line ends with spaces
        if($unicodemb4) {
            $thetext = preg_replace('/#\R+#/u', ' ', $thetext);
            $thetext = preg_replace('/\r/u', ' ', $thetext);
            $thetext = preg_replace('/\n/u', ' ', $thetext);
        }else{
            $thetext = preg_replace('/#\R+#/', ' ', $thetext);
            $thetext = preg_replace('/\r/', ' ', $thetext);
            $thetext = preg_replace('/\n/', ' ', $thetext);
        }

        //remove punctuation. This is where we needed the unicode flag
        //see https://stackoverflow.com/questions/5233734/how-to-strip-punctuation-in-php
        // $thetext = preg_replace("#[[:punct:]]#", "", $thetext);
        //https://stackoverflow.com/questions/5689918/php-strip-punctuation
        if($unicodemb4) {
            $thetext = preg_replace("/[[:punct:]]+/u", "", $thetext);
        }else{
            $thetext = preg_replace("/[[:punct:]]+/", "", $thetext);
        }

        //remove bad chars
        $b_open = "“";
        $b_close = "”";
        $b_sopen = '‘';
        $b_sclose = '’';
        $bads = array($b_open, $b_close, $b_sopen, $b_sclose);
        foreach ($bads as $bad) {
            $thetext = str_replace($bad, '', $thetext);
        }

        //remove double spaces
        //split on spaces into words
        $textbits = explode(' ', $thetext);
        //remove any empty elements
        $textbits = array_filter($textbits, function($value) {
            return $value !== '';
        });
        $thetext = implode(' ', $textbits);
        return $thetext;
    }


}