<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Codeigniter IMDb Scraper Config File
// Version: 1.1
// Author: Sven van Hees
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

/*
| -------------------------------------------------------------------------
| Scrape Options
| -------------------------------------------------------------------------
| These are some default options you can choose from to refine you results.
| 
| * local_info 	    - If set to TRUE it will use the titles that are specific to your country if there are any.
| * include_slug	  - When this is TRUE, it will pass a clean title slug in the results array named title_slug
|	  e.g "Star Trek: Into Darkness" , Will transform in "star-trek-into-darkness".
| * slug_delimiter	- Here you can choose what kind of delimiter you want for your title slug
|	  If you don't what it is, leave it as is.
| * acteur_limit	  - Specify how many cast members / actors you want to include in the final output. 1 - 30 
*/
$config['local_info'] = FALSE;
$config['include_slug'] = TRUE;
$config['slug_delimiter'] = '-';
$config['actor_limit'] = '5';

/*
| -------------------------------------------------------------------------
| Light Mode
| -------------------------------------------------------------------------
| If this is set to TRUE, it will only load basic movie information.
| This will make the scraping a lot faster. The scrape options above will
| still work in light mode.
|
| With light mode enabled my page was loaded within 2.7 seconds
| And when I had it disabled, it took 5.3 seconds 
|
| The light mode will only scrape the following:
| 
| * title_id 	  * rating		  * release_date
| * timestamp 	* runtime 		* plot and storyline
| * imdb_url 	  * genres 		  * cast
| * title 		  * directors 	* stars
| * title_slug	* writers 		* poster - small & medium
| * year 		    * mpaa_rating	* trailer
*/
$config['light_mode'] = TRUE;

/*
| -------------------------------------------------------------------------
| Extra Information
| -------------------------------------------------------------------------
| If set to TRUE, the Codeigniter IMDB scraper will scrape the following extra info.
| This will be ignored if Light_mode is set to true!
|
| * A more detailed plot	- More detailed then the summary
| * Release information		- More detailed release information
| * Also known as			    - Diffrent title aliases
| * Release Dates			    - Theatrical only
| * Recomended Titles 		- Gives movie recommendations
| * Media Images			    - Like screen shots, photo's from actors etc.
| * IMDB Video's			    - Will only get the url to the film page, not the actual film it's self
| * Youtube Trailer 		  - This will automatically get one youtube trailer with the config below.
*/
$config['get_extra_info'] = TRUE;

/*
| -------------------------------------------------------------------------
| Youtube Player Options.
| -------------------------------------------------------------------------
| Here you can add all the supported player parameters as specified here:
| https://developers.google.com/youtube/player_parameters?hl=nl&csw=1
| 
| Add options as the following: parameter=state
| 
*/
$config['trailer_options'] = array('autoplay=0', 'showinfo=0', 'showsearch=0', 'version=3', 'modestbranding=1', 'fs=1', 'iv_load_policy=3');
