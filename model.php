<?php

$Devices = $Admin->fetch_all_objects("devices", "hostname");

# fetch all zone mappings
$firewallZoneMapping = $Zones->get_zone_mappings();

# reorder by device
if ($firewallZoneMapping!==false) {
        # devices
        $devices = array();
        # add
        foreach ($firewallZoneMapping as $m) {
                $devices[$m->deviceId][] = $m;
        }
}
$firewallZones = $Zones->get_zones();

?>
  <style type="text/css">
    #mynetwork {
      border: 1px solid lightgray;
    }
  </style>

  <script type="text/javascript" src="app/admin/architectures/dist/vis.js"></script>
  <link href="app/admin/architectures/dist/vis-network.min.css" rel="stylesheet" type="text/css" />
  <?php

  $subnets = array() ;
  foreach ($subnets_node as $item) {
      $subnets[] = $subnets_node["subnet"];
  }
  $devices_array=array();
  for($i=0;$i<count($Devices);$i++){
    $device_obj=new stdClass();
    $device_obj->id=$Devices[$i]->id;
    $device_obj->hostname=$Devices[$i]->hostname;
    $devices_array[]=$device_obj;
  }
  $zones_array=array();
  $fw_zones=$Zones->get_zones();
  for($i=0;$i<count($fw_zones);$i++){
    $zone_all_details=$Zones->get_zone($fw_zones[$i]->id);
    $zone_obj=new stdClass();
    $zone_obj->id=$fw_zones[$i]->id;
    $zone_obj->zone=$fw_zones[$i]->zone;
    $zone_obj->description=$fw_zones[$i]->description;
    foreach($zone_all_details->network as $j => $val2){
      $network_obj=new stdClass();
      $network_obj->id=$zone_all_details->network[$j]->subnetId;
      $network_obj->subnet=$Zones->Subnets->transform_to_dotted($zone_all_details->network[$j]->subnet);
      $network_obj->subnetMask=$zone_all_details->network[$j]->subnetMask;
      $network_obj->subnetDescription=$zone_all_details->network[$j]->subnetDescription;
      $network_obj->subnetIsFolder=$zone_all_details->network[$j]->subnetIsFolder;
      $zone_obj->networks[]=$network_obj;
    }
    $zones_array[]=$zone_obj;
  }

  function search_zone($id,$array){
    for($i=0;$i<count($array);$i++){
      if($array[$i]->id==$id)return $i;
    }
    return false;
  }

function fetch_subnet_parents($id,$subnets,$Subnets){
  $parents=[];
  foreach($subnets as $j => $subnet){
    if($subnet->id!=$id){
       $slaves=$Subnets->fetch_subnet_slaves($subnet->id);
       if(count($slaves)>0)
       foreach($slaves as $k => $slave){
         if($slave->id==$id)$parents[]=$subnet;
       }
    }
  }
  return $parents;
}

  ?>

  <script>

      var oReq = new XMLHttpRequest(); //New request object
      var nodes = null;
      var edges = null;
      var network = null;
      var DIR = 'app/admin/architectures/icons/';
      var EDGE_LENGTH_MAIN = 50;


      function draw () {

          oReq.onload = function () {

              // GET JSON DATA------------------------------------------------------------------------------
              var json = this.responseText;
              console.log(json);


              // GET an array of JSON DATA------------------------------------------------------------------
              var testJson = JSON.parse(json);
              console.log(testJson);

              nodes = [];
              edges = [];

              var devices = JSON.parse('<?php echo json_encode($devices_array);?>');
              console.log(devices);
              for (var i=0;i<devices.length;i++) {
                  nodes.push({id: 'd_'+devices[i].id, label: devices[i].hostname, image: DIR + 'firewall.png', shape: 'image'});
              }

              var zones = JSON.parse('<?php echo json_encode($zones_array);?>');
              console.log(zones);
              for (var i=0;i<zones.length;i++) {
                  nodes.push({id: 'z_'+zones[i].id, label: zones[i].zone, image: DIR + 'Network-Pipe-icon.png', shape: 'image'});
console.log(zones);console.log("ok");
                  if(zones[i].networks)
                  for(let j in zones[i].networks) {
console.log(zones[i].networks[j]);
                    nodes.push({id: 'n_'+zones[i].networks[j].id, label: zones[i].networks[j].subnet+"/"+zones[i].networks[j].subnetMask+"\n("+zones[i].networks[j].subnetDescription+")", image: DIR + 'cloud.png', shape: 'image'});
                  }
              }

<?php
  foreach ($zones_array as $k=>$firewallZoneMapping) {
    $zone_index=search_zone($firewallZoneMapping->id,$devices_array);
    $device_found=null;
    if($zone_index!==FALSE)$device_found=$devices_array[$zone_index];?>
              edges.push({from: 'd_<?php echo $device_found->id;?>', to: 'z_<?php echo $firewallZoneMapping->id;?>', length: EDGE_LENGTH_MAIN*<?php echo (count($zones_array)+1);?>});
<?php
      foreach($firewallZoneMapping->networks as $j => $firewallNetwork){
        $slaves=$Subnets->fetch_subnet_slaves($firewallNetwork->id);
        $parents=fetch_subnet_parents($firewallNetwork->id, $firewallZoneMapping->networks,$Subnets);
        $subnet_addresses=$Addresses->fetch_subnet_addresses($firewallNetwork->id);
        if(count($parents)>0){
          foreach($parents as $kk=>$parent){?>
              edges.push({from: 'n_<?php echo $parent->id;?>', to: 'n_<?php echo $firewallNetwork->id;?>', length: EDGE_LENGTH_MAIN});<?php
          }
        }else{
          $subnet_children_number=1;
          if(count($subnet_addresses)>0)$subnet_children_number=count($subnet_addresses);?>
              edges.push({from: 'z_<?php echo $firewallZoneMapping->id;?>', to: 'n_<?php echo $firewallNetwork->id;?>', length: EDGE_LENGTH_MAIN*<?php echo (($subnet_children_number/count($firewallZoneMapping->networks))+1)/2;?>});<?php
        }
        foreach($subnet_addresses as $kk=>$subnet_address){?>
          nodes.push({id: 'e_<?php echo $subnet_address->id;?>', label: "<?php echo $subnet_address->ip;?>\n(<?php echo $subnet_address->description;?>)", image: DIR + 'Hardware-Laptop-1-icon.png', shape: 'image'});
          edges.push({from: 'e_<?php echo $subnet_address->id;?>', to: 'n_<?php echo $firewallNetwork->id;?>', length: EDGE_LENGTH_MAIN});<?php
        }
      }
  }
?>

              // CREATE THE NETWORK----------------------------------------------------------------------------
              var container = document.getElementById('mynetwork');
              var data = {nodes: nodes, edges: edges};

                var options = {
                 width: '100%',
    		  height: Math.round($(window).height() * 1.85) + 'px',
                  nodes: {
                    borderWidth: 1,
                    borderWidthSelected: 1,
                    shape: "box",
                    color: {
                      border: 'lightgray',
                      background: 'white',
                      highlight: {
                        border: 'lightgray',
                        background: 'lightblue'
                      },
                      hover: {
                        border: 'lightgray',
                        background: 'lightblue'
                      }
                    }
                  },
                  edges: {
                    color: 'lightgray'
                  },
                  layout: {
//                    hierarchical: {
//                      nodeSpacing: 150
//                    }
                  },
                  interaction: {dragNodes :true},
                  physics:true
                  };

              network = new vis.Network(container, data, options);

          };

          oReq.open("get", "app/admin/architectures/get_Firewall_data.php", true);
          oReq.send();

      }
  </script>
<body onload="draw()">

<div id="mynetwork"></div>

</body>
</html>
