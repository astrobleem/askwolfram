<?php

// alexa ask wolfram skill
// astrobleem/chad doebelin
// 
//This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

// test artifacts 
//$myQ = 'when will the sun set tomorrow';
//$myQ = 'how many calories are in a pepsi';
//$myQ = 'who was gandalf the grey';
$myQ = 'what is chromium';
//$myQ = 'how many teaspoons in a tablespoon';

// functions for making alexa speak

function speechOut($words) {
	header('Content-type: application/json');

	$open = '{"version": "1.0","sessionAttributes":"","response":{"outputSpeech":{"type": "PlainText","text": "';
	$close = '"},"shouldEndSession": false}}';
	$end = '"},"shouldEndSession": false}}';

	echo $open;
	 echo ($words);
	echo $close;
}

function endSession($words) {
	header('Content-type: application/json');

	$open = '{"version": "1.0","sessionAttributes":"","response":{"outputSpeech":{"type": "PlainText","text": "';
	$close = '"},"shouldEndSession": true}}';

	echo $open;
	 echo ($words);
	echo $close;
}

// gets the header received from amazon when it makes the request  
// so i can write it to file and tail -f the file this lets me watch 
// what's happening in the session

$data = json_decode(file_get_contents('php://input'),TRUE);
$text = print_r($data,true);
$ver = $data['version'];
$requestType = $data['request'][type];

// request type just writes the type of request to the file
// the text variable file_put_contents writes the php array to disk
// tail this file   
//file_put_contents('fromamazon.txt',$requestType);   
file_put_contents('fromamazon.txt',$text);


// if for some reason you wanted to show something to web users.
if(!$ver)
{
//echo("<html>web request detected</html>");
}

if($requestType == 'SessionEndedRequest')
{
endSession('goodbye');
}
//$requestType = 'LaunchRequest';
if($requestType == 'LaunchRequest')
{
	// new session!
	speechOut('What is your question?');
}

//$requestType = 'IntentRequest';
if($requestType == 'IntentRequest')
{

// the question comes from the header sent by 'mamazon
$myQ = $data['request'][intent][slots][Ans][value];

// build the string
$path = 'http://api.wolframalpha.com/v2/query?input=';
$append_id = '&appid=';

// GET YOUR OWN WOLFRAM API KEY ITS FREE
$append_id .= 'XXXXXX-XXXXXXXX';
// GET YOUR OWN WOLFRAM API KEY ITS FREE
// https://developer.wolframalpha.com/portal/apisignup.html


$question = urlencode($myQ);
$getstring = $path.$question.$append_id;
$data = simplexml_load_string(file_get_contents($getstring));
$i = 0;
$answered = 0;
$answer = '';
foreach($data as $pod)
foreach($pod as $subpod)
 foreach($subpod as $key => $val){
   if($key == 'plaintext'){

    $i = $i + 1;

// Input Interpretation pod:
    if($i == 1){
//		   if ($val)
//                       $answer .= $val;
//         	   echo("wolfram question interpretation: $val<br>");
//
	       }

// Result pod:
    if($i == 2){   
                   if ($val)
			{
			$answer .= $val;
			$answered = 1;
			}
               }
    if( ($i == 3) && ($answered == 0) ){
		    if ($val)
			{
                          $answer .= $val;
      			  $answered = 1;
			}
                }
 
    if( ($i == 4) && ($answered == 0) ){
		   if ($val)
			{
			$answer .= $val;
			$answered = 1;
			}
	        }
   } // foreach
} // foreach

// this gets the extra junk and formatting out of the response
$answer = strip_tags($answer);
$answer = preg_replace("/[^A-Za-z0-9 ]/", '', $answer);
$answer = preg_replace("/[^[:alnum:][:space:]]/ui", '', $answer);


// now it's plaintext, lets have alexa say it.
speechOut($answer);

} // end of intent request


?>
