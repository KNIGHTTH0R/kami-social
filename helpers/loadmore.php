<?php
	/* settings */
	session_start();
	$number_of_posts = 5; //5 at a time
	$_SESSION['posts_start'] = $_SESSION['posts_start'] ? $_SESSION['posts_start'] : $number_of_posts;

	/* loading of stuff */
	if(isset($_GET['start'])) {
		/* spit out the posts within the desired range */
		echo get_posts($_GET['start'],$_GET['desiredPosts']);
		/* save the user's "spot", so to speak */
		$_SESSION['posts_start']+= $_GET['desiredPosts'];
		/* kill the page */
		die();
	}
	
	/* grab stuff */
	function get_posts($start = 0, $number_of_posts = 5) {
		/* connect to the db */
		$connection = mysql_connect('localhost','yourUsername','yourPassword');
		mysql_select_db('davidwalsh83_blog',$connection);
		$posts = array();
		/* get the posts */
		$query = "SELECT post_title, post_content, post_name, ID FROM wp_posts WHERE post_status = 'publish' ORDER BY post_date DESC LIMIT $start,$number_of_posts";
		$result = mysql_query($query);
		while($row = mysql_fetch_assoc($result)) {
			preg_match("/<p>(.*)<\/p>/",$row['post_content'],$matches);
			$row['post_content'] = strip_tags($matches[1]);
			$posts[] = $row;
		}
		/* return the posts in the JSON format */
		return json_encode($posts);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  dir="ltr">
<head>
	<title>Create  a Twitter-Like "Load More" Widget Using CSS, HTML, JSON, and jQuery or MooTools Javascript</title>
	<style type="text/css">
		#posts-container			{ width:400px; border:1px solid #ccc; -webkit-border-radius:10px; -moz-border-radius:10px; }
		.post						{ padding:5px 10px 5px 100px; min-height:65px; border-bottom:1px solid #ccc; background:url(dwloadmore.png) 5px 5px no-repeat; cursor:pointer;  }
		.post:hover					{ background-color:lightblue; }
		a.post-title 				{ font-weight:bold; font-size:12px; text-decoration:none; }
		a.post-title:hover			{ text-decoration:underline; color:#900; }
		a.post-more					{ color:#900; }
		p.post-content				{ font-size:10px; line-height:17px; padding-bottom:0; }
		#load-more					{ background-color:#eee; color:#999; font-weight:bold; text-align:center; padding:10px 0; cursor:pointer; }
		#load-more:hover			{ color:#666; }
		.activate					{ background:url(loadmorespinner.gif) 140px 9px no-repeat #eee; }
	</style>
	<script type="text/javascript" src="jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="jquery.scrollTo-1.4.2.js"></script>
	<script type="text/javascript">
		//when the DOM is ready
		$(document).ready(function(){
			//settings on top
			var domain = 'http://davidwalsh.name/';
			var initialPosts = <?php echo get_posts(0,$_SESSION['posts_start']); ?>;
			//function that creates posts
			var postHandler = function(postsJSON) {
				$.each(postsJSON,function(i,post) {
					//post url
					var postURL = '' + domain + post.post_name;
					var id = 'post-' + post.ID;
					//create the HTML
					$('<div></div>')
					.addClass('post')
					.attr('id',id)
					//generate the HTML
					.html('<a href="' + postURL + '" class="post-title">' + post.post_title + '</a><p class="post-content">' + post.post_content + '<br /><a href="' + postURL + '" class="post-more">Read more...</a></p>')
					.click(function() {
						window.location = postURL;
					})
					//inject into the container
					.appendTo($('#posts'))
					.hide()
					.slideDown(250,function() {
						if(i == 0) {
							$.scrollTo($('div#' + id));
						}
					});
				});	
			};
			//place the initial posts in the page
			postHandler(initialPosts);
			//first, take care of the "load more"
			//when someone clicks on the "load more" DIV
			var start = <?php echo $_SESSION['posts_start']; ?>;
			var desiredPosts = <?php echo $number_of_posts; ?>;
			var loadMore = $('#load-more');
			//load event / ajax
			loadMore.click(function(){
				//add the activate class and change the message
				loadMore.addClass('activate').text('Loading...');
				//begin the ajax attempt
				$.ajax({
					url: 'jquery-version.php',
					data: {
						'start': start,
						'desiredPosts': desiredPosts
					},
					type: 'get',
					dataType: 'json',
					cache: false,
					success: function(responseJSON) {
						//reset the message
						loadMore.text('Load More');
						//increment the current status
						start += desiredPosts;
						//add in the new posts
						postHandler(responseJSON);
					},
					//failure class
					error: function() {
						//reset the message
						loadMore.text('Oops! Try Again.');
					},
					//complete event
					complete: function() {
						//remove the spinner
						loadMore.removeClass('activate');
					}
				});
			});
		});
	</script>
</head>
<body>
	
	<!-- Widget XHTML Starts Here -->
	<div id="posts-container">
		<!-- Posts go inside this DIV -->
		<div id="posts"></div>
		<!-- Load More "Link" -->
		<div id="load-more">Load More</div>
	</div>
	<!-- Widget XHTML Ends Here -->
	
</body>
</html>