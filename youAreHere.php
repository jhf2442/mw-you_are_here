<?php
 
/*
  youAreHere
 
  Author: Joel Hatsch

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


Configuration : define following variables in Localconfig.php
$youAreHere_separator=' -> ';
$youAreHere_topCategory='Portal';
$youAreHere_topLink='[[Hauptseite|Wiki home]]';

in the wiki page : {{#youAreHere:{{FULLPAGENAME}}}}

*/
 
$wgExtensionCredits['parserhook'][] = array(
        'name'        => 'youAreHere',
        'description' => 'Shows the category hierarchy for the given page',
        'author'      => 'Joel HATSCH',
        'url'         => 'https://github.com/jhf2442/mw-you_are_here'
);
 


$wgExtensionFunctions[] = 'Setup_youAreHere';
$wgHooks['LanguageGetMagic'][]       = 'Magic_youAreHere';
 
function Setup_youAreHere() {
    global $wgParser;
    $wgParser->setFunctionHook( 'youAreHere', 'Render_youAreHere' );
}
 
function Magic_youAreHere( &$magicWords, $langCode ) {
    $magicWords['youAreHere'] = array( 0, 'youAreHere' );
    return true;
}
 

function youAreHere_Recurse($tree,$txt) {
  global $youAreHere_Results,$youAreHere_separator,$youAreHere_topCategory,$youAreHere_topLink,$wgContLang;
	$categoryText=$wgContLang->getNSText( NS_CATEGORY );
  $results=array();
  if(sizeof($tree)==0) return;
  foreach(array_keys($tree) as $item) {
    if ($item==$categoryText.':'.$youAreHere_topCategory) {
      array_push($youAreHere_Results,$youAreHere_topLink.$txt);
    } else {
      $item1=str_replace($categoryText.':','',$item);
      $item1=str_replace('_',' ',$item1);
      youAreHere_Recurse($tree[$item],$youAreHere_separator.'[[:'.$item.'|'.$item1.']]'.$txt);
    }
  }
}


function Render_youAreHere( $parser, $pageTitle = '') {
	// The input parameters are wikitext with templates expanded.  The output should be wikitext too
  global $youAreHere_Results;
  $youAreHere_Results=array();
  $title=Title::newFromText( $pageTitle );
  if($title->getNamespace()==NS_CATEGORY) {
    youAreHere_Recurse($title->getParentCategoryTree(),' -> [[:'.$title->getFullText().'|'.$title->getText().']]');
  } else {
    youAreHere_Recurse($title->getParentCategoryTree(),' -> [['.$title->getPrefixedText().'|'.$title->getText().']]');
  }
  $output='';
  if(sizeof($youAreHere_Results)!=0) {
    foreach($youAreHere_Results as $item) {
      if($output!='') {
	$output.="<br>\n";
      }
      $output.=$item;
    }
  } else {
    $output='<b>Warning : Could not determine path to wiki home !<br>Please check your category settings !</b>';
  }
  return array($output, 'noparse' => false);
}

?>