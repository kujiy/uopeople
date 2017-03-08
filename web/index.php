
<?php
date_default_timezone_set("Asia/Tokyo");

if ($_GET["url"] != "" ) {
	$url = $_GET["url"] ;
	$urlp = "http://" . $_GET["url"] ;
	$urls = "https://" . $_GET["url"] ;
	
	if ( !filter_var($url, FILTER_VALIDATE_URL) === false) {
	   $url = $url;
	} else if ( !filter_var($urlp, FILTER_VALIDATE_URL) === false) {
	   $url = $urlp;
	} else if ( !filter_var($urls, FILTER_VALIDATE_URL) === false) {
	   $url = $urls;
	} else {
	    echo "your url is invalid.";
	}
	$today = date("M d, Y");
	
	$title = get_title($url) ;
	
	
	if ($url && $title) {
	    $out = $title .", Accessed at $today" . ", Retrieved from $url";
	}

}
function get_title($url){
	
	try {
		$str = getUrlContent($url);
	
	    if ($str === false) {
	        // Handle the error
	        return false;
	    }
	} catch (Exception $e) {
	    // Handle exception
	}
	
  if(strlen($str)>0){
    $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
    preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
    return $title[1];
  }
}


function getUrlContent($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ($httpcode>=200 && $httpcode<300) ? $data : false;
}


?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script src="https://code.jquery.com/jquery-3.1.1.slim.js" integrity="sha256-5i/mQ300M779N2OVDrl16lbohwXNUdzL/R2aVUXyXWA=" crossorigin="anonymous"></script>
    
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	
	<link rel="stylesheet" href="https://clipboardjs.com/bower_components/primer-css/css/primer.css">
	<script src="https://cdn.jsdelivr.net/clipboard.js/1.6.0/clipboard.min.js"></script>
	
</head>
<body>


    <!-- Begin page content -->
    <div class="container">
      <div class="page-header">
        <h1>APA format maker</h1>
      </div>
      <p class="lead"> Cite your website in APA format for free. </p>
		<form action="./apa.php" method="get" >
		
		<label for="basic-url">Input your reference url:</label>
		
		        <div class="form-group form-group-lg">
		        
		<div class="input-group">
		  <span class="input-group-addon">http://</span>
		  <input type="text" class="form-control" id="url" name="url"  value="<?php echo $url; ?>" placeholder="http://example.com">
		  
		</div><br />
		
		        
		        <button type="submit" class="btn btn-default btn-lg btn-block">Get APA</button>
		        
		</div>
		
		</form>
		
		
		<br>
		
		<div class="panel panel-success hidden">
		  <div class="panel-heading">Here you are.</div>
		  <div class="panel-body" id="ans">
		  <!-- Target -->
					<form class="form-horizontal form-group-lg">
						  <div class="form-group">
								  
								<textarea id="bar" class="form-control" rows="2"><?php echo $out; ?>
								</textarea>
								
								<!-- Trigger -->
								<button class="btn btn-block" data-clipboard-target="#bar">
								   <span class="glyphicon glyphicon-copy" aria-hidden="true"></span>copy to clipboard</button>
		
								<br />
		
								<div class="alert alert-warning" role="alert">Note: This tool doesn't take Author's name.</div>
		
							</div>
					</form>
		
		
		  </div>
		</div>
		
		<div class="panel panel-danger hidden">
		  <div class="panel-heading">Error</div>
		  <div class="panel-body" id="ans">
		    Your url is invalid. Please check the url is correct and aliving.
		  </div>
		</div>
		
		<!-- 履歴 -->
	    <div id="lastResults"></div>
    </div>
    
    
    


  </body>
</html>

<script>
$(function() {
    console.log( "ready!" );
    
    $("#url").focus().select();
    <?php
    if ($url && !$title) {
        echo <<<TTT
        $(".panel-danger").removeClass("hidden");
TTT;
     }
    if ($out) {
        echo <<<TTT
        $(".panel-danger").addClass("hidden"); 
        $(".panel-success").removeClass("hidden"); 
TTT;
     }
     ?>
     
     // click then clear input
     $("input[type=text]").click(function() {
	    $(this).closest('form').find("input[type=text], textarea").select();
	 });
     
	// clipboard
	new Clipboard('.btn');
});












// 履歴保持
if (typeof(Storage) !== "undefined") {
    // Code for localStorage/sessionStorage.
    
    
		   function addHistory(url, apa) {
		   	console.log("addHistory");
			   //Storing New result in previous History localstorage
			   if (localStorage.getItem("history") != null) 
			   {
			       var historyTmp = localStorage.getItem("history");
			       historyTmp += url + "|";
			       localStorage.setItem("history",historyTmp);
			   }
			   else
			   {
			       var historyTmp = url + "|";
			       localStorage.setItem("history",historyTmp);
			   }
			}
			<?php
			if ($url && $out) {
				echo "addHistory('$url', '$out');";
			}
			?>


		   //To Check and show previous results in **lastResults** div
		   if (localStorage.getItem("history") != null)
		   {
		       var historyTmp = localStorage.getItem("history");
		       var oldhistoryarrayDuplicates = historyTmp.split('|').reverse();
		       
		       var oldhistoryarray = [];
				$.each(oldhistoryarrayDuplicates, function(i, el){
				    if($.inArray(el, oldhistoryarray) === -1) oldhistoryarray.push(el);
				});
				

		       $('#lastResults').empty();
		       for(var i =0; i<oldhistoryarray.length; i++)
		       {
		           $('#lastResults').before('<a href="./apa.php?url=' + encodeURIComponent(oldhistoryarray[i]) + '">'+oldhistoryarray[i]+'</p>');
		       }
		   }
		   

} else {
    // Sorry! No Web Storage support..
}



</script>

