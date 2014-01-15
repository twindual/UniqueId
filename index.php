<?php
/* index.php */
require_once('classes/UniqueId.class.php');

$oUniqueId = new UniqueId();


// REGIONAL IDS
$scope = 'REGIONAL';
$optional = array('last_name'=>'SAMPLE', 'day_of_birth'=>'2014-12-31', 'gender'=>'F');

$result = $oUniqueId->generate( $scope, 'CA', 'DRIVER', 'ON', $optional );
echo "DRIVER_ON = [" . $oUniqueId->format( $scope, 'CA', 'DRIVER', 'ON', $result ) . "]<br/>\n";
$result = $oUniqueId->generate( $scope, 'CA', 'DRIVER', 'PQ', $optional );
echo "DRIVER_PQ = [" . $oUniqueId->format( $scope, 'CA', 'DRIVER', 'PQ', $result ) . "]<br/>\n";
$result = $oUniqueId->generate( $scope, 'CA', 'DRIVER', 'AB', $optional );
echo "DRIVER_AB = [" . $oUniqueId->format( $scope, 'CA', 'DRIVER', 'AB', $result ) . "]<br/>\n";
$result = $oUniqueId->generate( $scope, 'CA', 'DRIVER', 'BC', $optional );
echo "DRIVER_BC = [" . $oUniqueId->format( $scope, 'CA', 'DRIVER', 'BC', $result ) . "]<br/>\n";
$result = $oUniqueId->generate( $scope, 'CA', 'DRIVER', 'NS', $optional );
echo "DRIVER_NS = [" . $oUniqueId->format( $scope, 'CA', 'DRIVER', 'NS', $result ) . "]<br/>\n";
$result = $oUniqueId->generate( $scope, 'CA', 'DRIVER', 'PE', $optional );
echo "DRIVER_PE = [" . $oUniqueId->format( $scope, 'CA', 'DRIVER', 'PE', $result ) . "]<br/>\n";

$result = $oUniqueId->generate( $scope, 'CA', 'HEALTH', 'ON' );
$result = $oUniqueId->validate( $scope, 'CA', 'HEALTH', 'ON', $result );
echo "OHIP = [" . $oUniqueId->format( $scope, 'CA', 'HEALTH', 'ON', $result["id_number"] ) . "]<br/>";
//var_dump( $result );


// NATIONAL IDS
$scope = 'NATIONAL';

$result = $oUniqueId->generate( $scope, 'CA', 'SIN', 'QC' );
$result = $oUniqueId->validate( $scope, 'CA', 'SIN', null, $result );
echo "SIN = [" . $oUniqueId->format( $scope, 'CA', 'SIN', null, $result["id_number"] ) . "]<br/>";
//var_dump( $result );

$result = $oUniqueId->generate( $scope, 'US', 'SSN', 'AZ' );
$result = $oUniqueId->validate( $scope, 'US', 'SSN', null, $result );
echo "SSN = [" . $oUniqueId->format( $scope, 'US', 'SSN', null, $result["id_number"] ) . "]<br/>";
//var_dump( $result );

$result = $oUniqueId->generate( $scope, 'US', 'SSN', 'PR' );
$result = $oUniqueId->validate( $scope, 'US', 'SSN', '', $result );
echo "SSN = [" . $oUniqueId->format( $scope, 'US', 'SSN', '', $result["id_number"] ) . "]<br/>";
//var_dump( $result );

unset( $oUniqueId );
?>
