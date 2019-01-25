<?php

/**
 * Script to edit get a network visualisator
 *************************************************/

$Zones          = new FirewallZones($Database);

# verify that user is logged in
$User->check_user_session();

# fetch all Devices
$devices = $Admin->fetch_all_objects("devices", "hostname");
//var_dump($Subnets->fetch_subnet_slaves("13"));
//var_dump($Subnets->fetch_all_subnets());
?>


<h4><?php print _('Network Visualisation'); ?></h4>



<hr>
<br>

	<!-- TODO CALL AN API OR URL TO TRANSFORM DATA ON MODEL NETWORK -->

  <?php include('model.php'); ?>
