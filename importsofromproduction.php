<html>
<head>
<title>Import SO From Production</title>
</head>
<body>
<h1>Import SO From Production</h1>
<?php
if (isset($_POST['is_submit']) && $_POST['is_submit'] == "1")
{
	require('testimportso.php');

	$customer_id = 38403;
	$customer_address_id = 33631;
	$no_sos = $_POST['no_sos'];

	$testimportso = new TestImportSO();
	$testimportso->set_server('stage');
	$testimportso->set_customer_id($customer_id);
	$testimportso->set_customer_address_id($customer_address_id);
	$testimportso->set_no_sos($no_sos);

	$testimportso->run();

	echo "<br /><br />";
	echo '<a href="importsofromproduction.php">Back to Main Page</a>';
}
else
{
?>
	<form method="POST" action="importsofromproduction.php">
	Server : Stage Y
	<br /><br />
	Number of latest SOs :
	<input type="text" id="no_sos" name="no_sos" value="100" />
	<br /><br />
	Customer Set Will Be : dian.permata@bilna.com
	<br /><br />
	<input type="hidden" id="is_submit" name="is_submit" value="1" />
	<input type="submit" value="Get SO" />
	</form>
<?php
}
?>
</body>
</html>

<?php
function get_customer_id()
{
	$stagehost = 'bilnastage.csgheamo30fd.ap-southeast-1.rds.amazonaws.com';
	$stageuser = 'mybilnauser';
	$stagepassword = 'LawnqUiT79';
	$stagedb = 'bilna_live_stagey';

	$link = mysqli_connect($stagehost, $stageuser, $stagepassword, $stagedb);
	if (!$link) {
	    die('Could not connect to Stage : ' . mysqli_error($link));
	    exit;
	}

	$result = mysqli_query($link, "SELECT entity_id, email FROM customer_entity ORDER BY email LIMIT 0, 25");
	if (!$result) {
	    die('Invalid query: ' . mysqli_error($link));
	}

	$output = '';

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) { 
		$output .= '<option value="'.$row['entity_id'].'">'.$row['email'].'</option>';
	}

	return $output;
}
?>