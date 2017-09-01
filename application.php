<?php
define('SITE', 'https://swgoh.gg');
define('STARS', 7); // futureproof total stars just in case this can be bumped up

include_once('simple_html_dom.php');

function get_guild_members($guild_id, $guild_shortname) {
  // https://swgoh.gg/g/10134/lawl-firm/ for instance
  // 10134 = id
  // lawl-firm = shortname

  $members = array();

  $html = file_get_html(sprintf("%s/g/%d/%s/", SITE, $guild_id, $guild_shortname));
  
  /* don't need these right now in this function but nice to have
  $ret['longname'] = $html->find('div[class="content-container"] h1', 0)->plaintext;
  $ret['longname'] = explode("\n", $ret['longname'])[1];
  $ret['tagline'] = $html->find('p[class="text-center"] i', 0)->innertext;
  */

  foreach($html->find('table[class="table"] td a') as $element) {
    preg_match("/\/u\/(.*)\//", $element->href, $matches)[1];
    $members[] = $matches[1];
  }

  return $members;
}


function get_toons($username) {
  // return list of toons and * based on username
  // like https://swgoh.gg/u/tracer/collection/

  $toons = array();
  // <div class="collection-char-name"><a class="collection-char-name-link" href="/u/branmaro/collection/sith-assassin/" rel="nofollow">Sith Assassin</a></div>

  // name is at:
  // li .collection-char-list div.row div div.collection-char-name

  $html = file_get_html(sprintf("%s/u/%s/collection/", SITE, $username));
  //foreach($html->find('li[class="collection-char-list"] div[class="collection-char-name"]') as $element) {
  foreach($html->find('div[class="collection-char"]') as $element) {
    $name = $element->find('div[class="collection-char-name"]', 0)->plaintext;
    $level = $element->find('div[class="char-portrait-full-level"]', 0)->plaintext;
    if ($level && $level > 0) {      
      // user has toon, we can keep scraping data

      // get star rating
      // inactive stars have class star-inactive AND star
      $inactive_stars = $element->find('div[class="star-inactive"]');
      $toons[$name]['stars'] = STARS - count($inactive_stars);
      $toons[$name]['level'] = $element->find('div[class="char-portrait-full-level"]', 0)->plaintext;
    }
  }

  return $toons;
}


// -----------------------------------------------------------------------------
// test it!

// tab delimited output for google docs
// membername /t toonname \t toon stars \t toon level
print "username\ttoon\tstars\tlevel\n";

$limit = 100; // just here to prevent ever doing something crazy to swgoh.gg :)
$members = get_guild_members('10134', 'lawl-firm');
foreach ($members as $member) {
  $count++;
  if ($count <= $limit) {
    $toons = get_toons($member);
    foreach ($toons as $toon => $data) {
      printf("%s\t%s\t%s\t%s\n", $member, $toon, $data['stars'], $data['level']);
    }
  }
}

?>
