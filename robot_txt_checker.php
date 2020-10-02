<!DOCTYPE html>  
<html>  
<head> 
	<meta charset = "utf-8"/> 
	<title>robots.txt tester tool</title> 
	<style>
	body {
	font-family: 'Merriweather', serif;
	font-size:16px;
	}

	h1 {
	font-size:16px;
	}

	th {
	font-size:15px;
	}
	
	table, td, th {  
	border: 1px solid #ddd;
	text-align: left;
	}

	table {
	border-collapse: collapse;
	width: 100%;
	}

	th, td {
	padding: 15px;
	}
	</style>
</head> 
<body> 
	<div><h1>robots.txt tester tool</h1></div>
	<div> 
		<div> 
			<form action="robot_txt_checker.php" method="post">
			<input type="input" name="url" placeholder="https://www.agloberry.com" value=""/>
			<input type="submit" name="submit" value="Submit"/>
			</form>
		</div> 
		<div style="margin-top:10px;">
		<?php
		$userAgent = array();
		$allow = array();
		$disallow = array();
		$siteMap = array();
		
		if(isset($_POST["submit"]) && isset($_POST["url"])) {
			$url = trim($_POST["url"]);
			if(filter_var($url,FILTER_VALIDATE_URL) == true) {	//Check user input is URL or not
				$dump = parse_url($url);
				$robotFileLocation = $dump["scheme"]."://".$dump["host"]."/robots.txt";
				$robotFileObject = @fopen($robotFileLocation,"r"); // Character "@" is use to suppressed warning, if robots.txt not found in base URL
				if($robotFileObject != NULL) {
					$checkPoint = 0;
					$wordBuffer = "";
					$robotKeyword = "";
					while(!feof($robotFileObject)) {
						$character = fgetc($robotFileObject);
						if(ord($character) != 10 && ord($character) != 32)
						{	$wordBuffer .= $character;	}
						else
						{
							$wordBuffer = trim($wordBuffer);
							if($wordBuffer != "") {
								if(strcmp($wordBuffer,"User-agent:") == 0)
								{	$checkPoint = 1;	}
								else if($checkPoint == 1)
								{	
									$key = array_search($wordBuffer,$userAgent);
									if($key == NULL) {
										$userAgent[] = $wordBuffer;
										$key = count($userAgent)-1;
									}
									$checkPoint = 2;	
								}
								else if($checkPoint == 2 && (strcmp($wordBuffer,"Disallow:") == 0 || strcmp($wordBuffer,"Allow:") == 0 || strcmp($wordBuffer,"Sitemap:") == 0))
								{	$robotKeyword = $wordBuffer;	}
								else
								{
									update($robotKeyword, $key);
									$robotKeyword = "";
								}
								$wordBuffer = "";
							}
						}
					}
					$wordBuffer = trim($wordBuffer);
					if($wordBuffer != "") {
					update($robotKeyword, $key);
					}
					@fclose($robotFileObject);			
				}
				else
				{ echo "<div style='color:red;'>robots.txt file not found</div>"; }
			}
			else
			{ echo "<div style='color:red;'>Please enter valid URL</div>"; }
		}
				
		$userAgentCounter = 0;
		$userAgentTotal = count($userAgent);
		if($userAgentTotal >= 1) {
			?>
			<table width="100%">
				<tr>
					<th valign='top'>S.No</th>
					<th valign='top'>User Agent</th>
					<th valign='top'>Allow Path</th>
					<th valign='top'>Disallow Path</th>
					<th valign='top'>Sitemap</th>
				</tr>
				<?php
				while($userAgentCounter < $userAgentTotal) {
					echo "<tr>";
					echo "<td valign='top'>".($userAgentCounter+1)."</td>";
					echo "<td valign='top'>".$userAgent[$userAgentCounter]."</td>";
					$allowCounter = 0;
					$allowTotal = count($allow[$userAgentCounter]);
					echo "<td valign='top'>";
					while($allowCounter < $allowTotal) {
						echo $allow[$userAgentCounter][$allowCounter]."<br/>";
						$allowCounter++;
					}
					echo "</td>";
					$disallowCounter = 0;
					$disallowTotal = count($disallow[$userAgentCounter]);
					echo "<td valign='top'>";
					while($disallowCounter < $disallowTotal) {
						echo $disallow[$userAgentCounter][$disallowCounter]."<br/>";
						$disallowCounter++;
					}
					echo "</td>";
					$sitemapCounter = 0;
					$sitemapTotal = count($siteMap[$userAgentCounter]);
					echo "<td valign='top'>";
					while($sitemapCounter < $sitemapTotal) {
						echo $siteMap[$userAgentCounter][$sitemapCounter]."<br/>";
						$sitemapCounter++;
					}
					echo "</td>";
					echo "</tr>";
					$userAgentCounter++;
				}
				?>
			</table>
			<?php
		}
		?>
		</div>
	</div> 
	<div> 
	<p>Created by <a href = "https://www.algoberry.com" target="_blank">Algoberry</a></p> 
	</div>
</body> 
</html>

<?php
function update($robotKeyword, $key) {
	global $disallow, $allow, $siteMap,$wordBuffer;
	if($robotKeyword == "Disallow:") 
	{	$disallow[$key][] = $wordBuffer;	}
	else if($robotKeyword == "Allow:")
	{	$allow[$key][] = $wordBuffer;	}
	else if($robotKeyword == "Sitemap:")
	{	$siteMap[$key][] = $wordBuffer;	}
}
?>
