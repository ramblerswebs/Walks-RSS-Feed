<?php

/*
 * Copyright (C) 2024 Chris Vaughan
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

define("WALKMANAGER", "https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?");
define("APIKEY", "853aa876db0a37ff0e6780db2d2addee");
const TIMEFORMAT = "Y-m-d\TH:i:s";

// set current directory to current run directory
$exepath = dirname(__FILE__);
define('BASE_PATH', dirname(realpath(dirname(__FILE__))));
chdir($exepath);
require('classes/autoload.php');
spl_autoload_register('autoload');
Logfile::create("logfiles/logfile");
$opts = new Options();
$groups = $opts->gets("groups");

// get feed URL
$feed = new Feedoptions();
$feed->groupCode = $groups;
$feed->include_events = false;
Logfile::writeWhen("Group: " . $groups);
if ($groups == "") {
    die("No group supplied");
}

$url = $feed->getFeedURL();

// read walks
$content = file_get_contents($url);
if ($content === false) {
    Logfile::writeWhen("Unable to read walks feed");
    die();
} else {
    
}
$walksData = json_decode($content);
$walks = $walksData->data;

// create RSS/XML file
// Set the content type to be XML, so that the browser will   recognise it as XML.
header('Content-Type: text/xml; charset=utf-8', true);
// "Create" the document.
// $xml = new DOMDocument("1.0", "UTF-8");
// <rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">

$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$rss = $xml->createElement('rss');
$rss->setAttribute('version', '2.0');
//$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', 'http://base.google.com/ns/1.0');
$xml->appendChild($rss);

$channel = $xml->createElement('channel');
$channel->appendChild($xml->createElement('title', 'Feed of Ramblers groups(s) walks and events'));
$channel->appendChild($xml->createElement('description', 'Provide an RSS feed of walks by one or more Ramblers Groups or Areas'));
$channel->appendChild($xml->createElement('link', 'https://rss.theramblers.org.uk'));
$rss->appendChild($channel);

foreach ($walks as $walk) {

    $item = $xml->createElement("item");
    $channel->appendChild($item);
// Set the attributes. 
    $walkText = "";
    if ($walk->distance_miles > 0) {
        $walkText = $walk->shape . " Walk, " . $walk->distance_miles . "mi/" . $walk->distance_km . "Km, " . $walk->difficulty->description;
    }
    $startDate = DateTime::createFromFormat(TIMEFORMAT, substr($walk->start_date_time, 0, 19));
    $dateText = $startDate->format('D, jS F');
    $timeText = $startDate->format('g:ia');
    $walkText = $dateText . " at " . $timeText . ", " . $walk->title . " " . $walkText;
    creatElement($xml, $item, "title", $walkText);
    creatElement($xml, $item, "category", $walk->group_name);
    creatElement($xml, $item, "description", $walk->description);
    //   creatElement($xml, $item, "additionalInfo", $walk->additional_details);

    $guid = creatElement($xml, $item, "guid", $walk->id);
    $guid->setAttribute('isPermaLink', 'false');
    creatElement($xml, $item, "link", $walk->url);
}


// Parse the XML.
$file = $xml->saveXML();
echo removeFirstLine($file);

function creatElement($xml, $root, $name, $content) {
    $ele = $xml->createElement($name);
    $root->appendChild($ele);
    $ele2 = $xml->createTextNode($content);
    $ele->appendChild($ele2);
    return $ele;
}

function removeFirstLine($file) {
    $fl = '<?xml version="1.0" encoding="UTF-8"?>';
    return str_replace($fl, "", $file);
}
