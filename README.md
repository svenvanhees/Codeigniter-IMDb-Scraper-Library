Codeigniter-IMDb-Scraper
========================

A IMDb scraper optimized for codeigniter


--------------------------------------------------------------------------------------------------------------------------
EXAMPLE 1:
To use this scraper, upload the 2 files, both named imdb_scraper, to their repective folders.
Once you have done that, we can begin.

We have 2 ways of getting movie information. This can be done by using a title(e.g Alysium) or a IMDb ID(e.g tt1535108)
To get the information from the movie Alysium we can do the following:

	/*Change the config file to your needs*/
	The config file is well documented and is ready to be used without changes

	/*In your Controller*/
	//Load the imdb_scraper library 
	$this->load->library('imdb_scraper');

	//Set a query 
	$query = 'Alysium';

	//Scrape the info and pass the results to the view
	$data['moviedetails'] = $this->imdb_scraper->getMovieInfo($query);

	/*In your view*/
	//Here we output the array
	<?php
		echo '<pre>';
		print_r($moviedetails);
		echo '</pre>;
	?>



