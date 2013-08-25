<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Codeigniter IMDb Scraper
// Version: 1.1
// Author: Sven van Hees
// Last Updated: August 25, 2013 
//
// This is a modified script for the use Codeigniter! This script is based on the author listed below.
// 
// Issues   : https://github.com/Tony0892/Codeigniter-IMDb-Scraper/issues
// Wiki     : https://github.com/Tony0892/Codeigniter-IMDb-Scraper/wiki
// //////////////////////////////////////////////////////////////////////////////////////////////////////
// Based on the work of: Abhinay Rathore
// Website: http://www.AbhinayRathore.com
//
// Codeigniter IMDb Scraper Library
// Copyright (C) 2013  Sven van Hees
//
//    This program is free software: you can redistribute it and/or modify
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
/////////////////////////////////////////////////////////////////////////////////////////////////////////
 
class Imdb_scraper
{   

    private $CI;

    function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('imdb_scraper');
    }

    // Get movie information by either the movie title, or the IMDb Id.
    // This method searches the given title on Google, Bing or Ask to get the best possible match.
    public function getMovieInfo($title)
    {
        if(strpos($title, 'tt')){
            return $this->getMovieInfoById($title);
        } else {
            $imdbId = $this->getIMDbIdFromSearch(trim($title));
            if($imdbId === NULL){
                $arr = array();
                $arr['error'] = "No Title found in Search Results!";
                return $arr;
            }
        return $this->getMovieInfoById($imdbId);
        }
    }
     
    // Get movie information by IMDb Id.
    public function getMovieInfoById($imdbId)
    {
        $arr = array();
        if($this->CI->config->item('local_info') == TRUE){
            $imdbUrl = "http://www.imdb.com/title/" . trim($imdbId) . "/";
        } else {
            $imdbUrl = "http://akas.imdb.com/title/" . trim($imdbId) . "/";
        }
        return $this->scrapeMovieInfo($imdbUrl);
    }
     
    // Scrape movie information from IMDb page and return results in an array.
    private function scrapeMovieInfo($imdbUrl, $getExtraInfo = true)
    {
        $arr = array();
        $html = $this->geturl("${imdbUrl}combined");
        $title_id = $this->match('/<link rel="canonical" href="http:\/\/www.imdb.com\/title\/(tt\d+)\/combined" \/>/ms', $html, 1);
        if(empty($title_id) || !preg_match("/tt\d+/i", $title_id)) {
            $arr['error'] = "No Title found on IMDb!";
            return $arr;
        } 
        if($this->CI->config->item('light_mode') == TRUE){
            $arr['title_id'] = $title_id;
            $arr['timestamp'] = date('Y-m-d H:i:s');
            $arr['imdb_url'] = $imdbUrl;
            $arr['title'] = str_replace('"', '', trim($this->match('/<title>(IMDb \- )*(.*?) \(.*?<\/title>/ms', $html, 2)));
            if($this->CI->config->item('include_slug') == TRUE){
                $arr['title_slug'] = $this->getTitleSlug($arr['title']);
            }
            $arr['year'] = trim($this->match('/<title>.*?\(.*?(\d{4}).*?\).*?<\/title>/ms', $html, 1));
            $arr['rating'] = $this->match('/<b>(\d.\d)\/10<\/b>/ms', $html, 1);
            $arr['runtime'] = trim($this->match('/Runtime:<\/h5><div class="info-content">.*?(\d+) min.*?<\/div>/ms', $html, 1));
            $arr['genres'] = $this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Genre.?:(.*?)(<\/div>|See more)/ms', $html, 1), 1);
            $arr['directors'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Directed by<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
            $arr['writers'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Writing credits<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
            $arr['mpaa_rating'] = $this->match('/MPAA<\/a>:<\/h5><div class="info-content">Rated (G|PG|PG-13|PG-14|R|NC-17|X) /ms', $html, 1);
            $arr['release_date'] = $this->match('/Release Date:<\/h5>.*?<div class="info-content">.*?([0-9][0-9]? (January|February|March|April|May|June|July|August|September|October|November|December) (19|20)[0-9][0-9])/ms', $html, 1);
            $arr['plot'] = trim(strip_tags($this->match('/Plot:<\/h5>.*?<div class="info-content">(.*?)(<a|<\/div)/ms', $html, 1)));
            $cast = $this->match_all_key_value('/<td class="nm"><a.*?href="\/name\/(.*?)\/".*?>(.*?)<\/a>/ms', $this->match('/<h3>Cast<\/h3>(.*?)<\/table>/ms', $html, 1));
            $arr['stars'] = array_slice($cast, 0, $this->CI->config->item('actor_limit'));
            $plotPageHtml = $this->geturl("${imdbUrl}plotsummary");
            $arr['storyline'] = trim(strip_tags($this->match('/<p class="plotpar">(.*?)(<i>|<\/p>)/ms', $plotPageHtml, 1)));
            $arr['poster'] = $this->match('/<div class="photo">.*?<a name="poster".*?><img.*?src="(.*?)".*?<\/div>/ms', $html, 1);
            if ($arr['poster'] != '' && strpos($arr['poster'], "media-imdb.com") > 0) { //Get large and small posters
                $arr['poster'] = preg_replace('/_V1.*?.jpg/ms', "_V1._SY300.jpg", $arr['poster']);
                $arr['poster_large'] = preg_replace('/_V1.*?.jpg/ms', "_V1._SY500.jpg", $arr['poster']);
            } else {
                $arr['poster'] = "";
            }
            $arr['trailer'] = $this->getYoutubeTrailer($arr['title']);
            return $arr;
        }
        $arr['title_id'] = $title_id;
        $arr['timestamp'] = date('Y-m-d H:i:s');
        $arr['imdb_url'] = $imdbUrl;
        $arr['title'] = str_replace('"', '', trim($this->match('/<title>(IMDb \- )*(.*?) \(.*?<\/title>/ms', $html, 2)));
        if($this->CI->config->item('include_slug') == TRUE){
            $arr['title_slug'] = $this->getTitleSlug($arr['title']);
        }
        $arr['original_title'] = trim($this->match('/class="title-extra">(.*?)</ms', $html, 1));
        $arr['year'] = trim($this->match('/<title>.*?\(.*?(\d{4}).*?\).*?<\/title>/ms', $html, 1));
        $arr['rating'] = $this->match('/<b>(\d.\d)\/10<\/b>/ms', $html, 1);
        $arr['metascore'] = $this->match('/<a href="criticreviews?ref_=tt_ov_rt">(\d*)\/100<\/a>/ms', $html, 1);
        $arr['genres'] = $this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Genre.?:(.*?)(<\/div>|See more)/ms', $html, 1), 1);
        $arr['directors'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Directed by<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
        $arr['writers'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Writing credits<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
        $arr['cast'] = $this->match_all_key_value('/<td class="nm"><a.*?href="\/name\/(.*?)\/".*?>(.*?)<\/a>/ms', $this->match('/<h3>Cast<\/h3>(.*?)<\/table>/ms', $html, 1));
        $arr['stars'] = array_slice($arr['cast'], 0, $this->CI->config->item('actor_limit'));
        $arr['producers'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Produced by<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
        $arr['musicians'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Original Music by<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
        $arr['cinematographers'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Cinematography by<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
        $arr['editors'] = $this->match_all_key_value('/<td valign="top"><a.*?href="\/name\/(.*?)\/">(.*?)<\/a>/ms', $this->match('/Film Editing by<\/a><\/h5>(.*?)<\/table>/ms', $html, 1));
        $arr['mpaa_rating'] = $this->match('/MPAA<\/a>:<\/h5><div class="info-content">Rated (G|PG|PG-13|PG-14|R|NC-17|X) /ms', $html, 1);
        $arr['release_date'] = $this->match('/Release Date:<\/h5>.*?<div class="info-content">.*?([0-9][0-9]? (January|February|March|April|May|June|July|August|September|October|November|December) (19|20)[0-9][0-9])/ms', $html, 1);
        $arr['tagline'] = trim(strip_tags($this->match('/Tagline:<\/h5>.*?<div class="info-content">(.*?)(<a|<\/div)/ms', $html, 1)));
        $arr['plot'] = trim(strip_tags($this->match('/Plot:<\/h5>.*?<div class="info-content">(.*?)(<a|<\/div)/ms', $html, 1)));
        $arr['plot_keywords'] = $this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Plot Keywords:<\/h5>.*?<div class="info-content">(.*?)<\/div/ms', $html, 1), 1);
        $arr['poster'] = $this->match('/<div class="photo">.*?<a name="poster".*?><img.*?src="(.*?)".*?<\/div>/ms', $html, 1);
        $arr['poster_large'] = "";
        $arr['poster_full'] = "";
        if ($arr['poster'] != '' && strpos($arr['poster'], "media-imdb.com") > 0) { //Get large and small posters
            $arr['poster'] = preg_replace('/_V1.*?.jpg/ms', "_V1._SY300.jpg", $arr['poster']);
            $arr['poster_large'] = preg_replace('/_V1.*?.jpg/ms', "_V1._SY500.jpg", $arr['poster']);
            $arr['poster_full'] = preg_replace('/_V1.*?.jpg/ms', "_V1._SY0.jpg", $arr['poster']);
        } else {
            $arr['poster'] = "";
        }
        $arr['runtime'] = trim($this->match('/Runtime:<\/h5><div class="info-content">.*?(\d+) min.*?<\/div>/ms', $html, 1));
        $arr['top_250'] = trim($this->match('/Top 250: #(\d+)</ms', $html, 1));
        $arr['oscars'] = trim($this->match('/Won (\d+) Oscars?\./ms', $html, 1));
        if(empty($arr['oscars']) && preg_match("/Won Oscar\./i", $html)) $arr['oscars'] = "1";
        $arr['awards'] = trim($this->match('/(\d+) wins/ms',$html, 1));
        $arr['nominations'] = trim($this->match('/(\d+) nominations/ms',$html, 1));
        $arr['votes'] = $this->match('/>(\d+,?\d*) votes</ms', $html, 1);
        $arr['language'] = $this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Language.?:(.*?)(<\/div>|>.?and )/ms', $html, 1), 1);
        $arr['country'] = $this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Country:(.*?)(<\/div>|>.?and )/ms', $html, 1), 1);
        if($this->CI->config->item('get_extra_info') == TRUE) {
            $plotPageHtml = $this->geturl("${imdbUrl}plotsummary");
            $arr['storyline'] = trim(strip_tags($this->match('/<p class="plotpar">(.*?)(<i>|<\/p>)/ms', $plotPageHtml, 1)));
            $releaseinfoHtml = $this->geturl("http://www.imdb.com/title/" . $arr['title_id'] . "/releaseinfo");
            $arr['also_known_as'] = $this->getAkaTitles($releaseinfoHtml);
            $arr['release_dates'] = $this->getReleaseDates($releaseinfoHtml);
            $arr['recommended_titles'] = $this->getRecommendedTitles($arr['title_id']);
            $arr['media_images'] = $this->getMediaImages($arr['title_id']);
            $arr['videos'] = $this->getVideos($arr['title_id']);
            $arr['trailer'] = $this->getYoutubeTrailer($arr['title']);
        }
        return $arr;
    }
     
    // Scan all Release Dates.
    private function getReleaseDates($html)
    {
        $releaseDates = array();
        foreach($this->match_all('/<tr>(.*?)<\/tr>/ms', $this->match('/Date<\/th><\/tr>(.*?)<\/table>/ms', $html, 1), 1) as $r) {
            $country = trim(strip_tags($this->match('/<td><b>(.*?)<\/b><\/td>/ms', $r, 1)));
            $date = trim(strip_tags($this->match('/<td align="right">(.*?)<\/td>/ms', $r, 1)));
            array_push($releaseDates, $country . " = " . $date);
        }
        return array_filter($releaseDates);
    }
 
    // Scan all AKA Titles.
    private function getAkaTitles($html)
    {
        $akaTitles = array();
        foreach($this->match_all('/<tr>(.*?)<\/tr>/msi', $this->match('/Also Known As(.*?)<\/table>/ms', $html, 1), 1) as $m) {
            $akaTitleMatch = $this->match_all('/<td>(.*?)<\/td>/ms', $m, 1);
            $akaTitle = trim($akaTitleMatch[0]);
            $akaCountry = trim($akaTitleMatch[1]);
            array_push($akaTitles, $akaTitle . " = " . $akaCountry);
        }
        return array_filter($akaTitles);
    }
 
    // Collect all Media Images.
    private function getMediaImages($titleId)
    {
        $url  = "http://www.imdb.com/title/" . $titleId . "/mediaindex";
        $html = $this->geturl($url);
        $media = array();
        $media = array_merge($media, $this->scanMediaImages($html));
        foreach($this->match_all('/<a href="\?page=(.*?)">/ms', $this->match('/<span style="padding: 0 1em;">(.*?)<\/span>/ms', $html, 1), 1) as $p) {
            $html = $this->geturl($url . "?page=" . $p);
            $media = array_merge($media, $this->scanMediaImages($html));
        }
        return $media;
    }
 
    // Scan all media images.
    private function scanMediaImages($html)
    {
        $pics = array();
        foreach($this->match_all('/src="(.*?)"/ms', $this->match('/<div class="thumb_list" style="font-size: 0px;">(.*?)<\/div>/ms', $html, 1), 1) as $i) {
            array_push($pics, preg_replace('/_V1\..*?.jpg/ms', "_V1._SY0.jpg", $i));
        }
        return array_filter($pics);
    }
     
    // Get recommended titles by IMDb title id.
    public function getRecommendedTitles($titleId)
    {
        $json = $this->geturl("http://www.imdb.com/widget/recommendations/_ajax/get_more_recs?specs=p13nsims%3A${titleId}");
        $resp = json_decode($json, true);
        $arr = array();
        if(isset($resp["recommendations"])) {
            foreach($resp["recommendations"] as $val) {
                $name = $this->match('/title="(.*?)"/msi', $val['content'], 1);
                $arr[$val['tconst']] = $name;
            }
        }
        return array_filter($arr);
    }
     
    // Get all Videos and Trailers and Display The Movie Page
    public function getVideos($titleId)
    {
        $html = $this->geturl("http://www.imdb.com/title/${titleId}/videogallery");
        $videos = array();
        foreach ($this->match_all('/<a.*?href="(\/video\/imdb\/.*?)".*?>.*?<\/a>/ms', $html, 1) as $v) {
            $videos[] = "http://www.imdb.com${v}";
        }
        return array_filter($videos);
    }

    // Get Youtube Trailer URL With Options Privided In The Config File
    public function getYoutubeTrailer($title)
    {
        $title = preg_replace("/\([0-9].*\)/", "",$title);  
        $toReplace = array("'"," ");
        $replWith = array("+","+");
        $title = str_replace($toReplace, $replWith,$title);
        $title .= '+trailer';
        $feedURL = 'http://gdata.youtube.com/feeds/api/videos?q='.$title.'&start-index=1&max-results=1';
        $sxml = simplexml_load_file($feedURL);
        foreach ($sxml->entry as $entry) {
            $media = $entry->children('http://search.yahoo.com/mrss/');
            $attrs = $media->group->player->attributes();
            $trailer = $attrs['url']; 
        }
        $trailer = str_replace('watch?v=','v/',$trailer.'?');
        $settings = $this->CI->config->item('trailer_options');
        if(is_array($settings)){
            foreach ($settings as $parameter) {
                $trailer .= '&amp;'.$parameter;
            }
        }
        return htmlspecialchars($trailer);
    }

    //Make The Title Slug From the Title
    private function getTitleSlug($title, $replace=array())
    {
        if( !empty($replace) ) {
            $title = str_replace((array)$replace, ' ', $title);
        }
        if($this->CI->config->item('slug_delimiter') == NULL){
            $delimiter = $this->CI->config->item('slug_delimiter');
        } else {
            $delimiter = '-';
        }
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $slug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $slug);
        $slug = strtolower(trim($slug, '-'));
        $slug = preg_replace("/[\/_|+ -]+/", $delimiter, $slug);
        return $slug;
    }
 
    //************************[ Extra Functions ]******************************
 
    // Movie title search on Google, Bing or Ask. If search fails, return FALSE.
    private function getIMDbIdFromSearch($title, $engine = "google"){
        switch ($engine) {
            case "google":  $nextEngine = "bing";  break;
            case "bing":    $nextEngine = "ask";   break;
            case "ask":     $nextEngine = FALSE;   break;
            case FALSE:     return NULL;
            default:        return NULL;
        }
        $url = "http://www.${engine}.com/search?q=imdb+" . rawurlencode($title);
        $ids = $this->match_all('/<a.*?href="http:\/\/www.imdb.com\/title\/(tt\d+).*?".*?>.*?<\/a>/ms', $this->geturl($url), 1);
        if (!isset($ids[0]) || empty($ids[0])) //if search failed
            return $this->getIMDbIdFromSearch($title, $nextEngine); //move to next search engine
        else
            return $ids[0]; //return first IMDb result
    }
     
    private function geturl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }
 
    private function match_all_key_value($regex, $str, $keyIndex = 1, $valueIndex = 2){
        $arr = array();
        preg_match_all($regex, $str, $matches, PREG_SET_ORDER);
        foreach($matches as $m){
            $arr[$m[$keyIndex]] = $m[$valueIndex];
        }
        return $arr;
    }
     
    private function match_all($regex, $str, $i = 0){
        if(preg_match_all($regex, $str, $matches) === false)
            return false;
        else
            return $matches[$i];
    }
 
    private function match($regex, $str, $i = 0){
        if(preg_match($regex, $str, $match) == 1)
            return $match[$i];
        else
            return false;
    }
}
