<?php

require_once( 'iCalcreator.class.php' );

$source_url = "https://docs.google.com/spreadsheet/pub?hl=en_US&hl=en_US&key=0AqGo3FxK2RVodEVNbzgyZVMzRFY1a0tmWktteUg4ZVE&output=csv";

$bands = explode("%0A", urlencode(file_get_contents($source_url) ) );

$location = array_shift($bands);

$url = "http://api.bandsintown.com/events/search?location=Southampton,UK&radius=10&format=json&app_id=GigCalendar".implode("&artists[]=", $bands);

$gigs = json_decode( file_get_contents($url) );

$config = array( 'unique_id' => $source_url );
$cal = new vcalendar( $config );

$cal->setProperty( 'method', 'PUBLISH' );
$cal->setProperty( "x-wr-calname", "GigCalendar for $location" );
$cal->setProperty( "X-WR-CALDESC", "GigCalendar for $location" );
$cal->setProperty( "X-WR-TIMEZONE", "Europe/London" );

foreach( $gigs as $gig )
{
	$calevent = & $cal->newComponent( 'vevent' );
	$date = substr(preg_replace("/-/", "", $gig->{"datetime"}), 0, 6);
	$calevent->setProperty( 'dtstart', $date, array('VALUE' => 'DATE'));
	  // alt. date format, now for an all-day event
	$calevent->setProperty( 'summary', $gig->{"artists"}[0]->{"name"} );
	$calevent->setProperty( 'description', $gig->{"artists"}[0]->{"name"}.' playing at '.$gig->{"venue"}->{"name"}.", ".$gig->{"venue"}->{"city"} );
	$calevent->setProperty( 'LOCATION', $gig->{"venue"}->{"name"}.", ".$gig->{"venue"}->{"city"} );
}


$cal->returnCalendar();
?>
