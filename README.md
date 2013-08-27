Codeigniter-IMDb-Scraper
========================

A IMDb Scraper Optimized For Codeigniter

--------------------------------------------------------------------------------------------------------------------------
Main Features:

	*Difrent scrape options to refine your results
	*Includes a light mode to make searching faster
	*Youtube trailer url in the final results, also has options available in the config
	*Also includes a timestamp in the final array for easy database filter and retrieval
	*More to follow...	

--------------------------------------------------------------------------------------------------------------------------
EXAMPLE 1:
To use this scraper, upload the 2 files, wich are both in their repective folders and named imdb_conf and.php imdb_lib.php
Once you have done that, we can begin.

We have 2 ways of getting movie information. This can be done by using a title(e.g Alysium) or a IMDb ID(e.g tt1535108)
To get the information from the movie Alysium we can do the following:

	/*Change the config file to your needs*/
	The config file is well documented and is ready to be used without changes

	/*In your Controller*/
	//Load the imdb_scraper library 
	$this->load->library('imdb');

	//Set a query, this can either be a name e.g. Alysium or a IMDb Id e.g. tttt1535108
	$query = 'Alysium';

	//Scrape the info and pass the results to the view
	$data['moviedetails'] = $this->imdb->getMovieInfo($query);

	/*In your view*/
	//Here we output the array, but you can do anything you want with it
	<?php
		echo '<pre>';
		print_r($moviedetails);
		echo '</pre>;
	?>



